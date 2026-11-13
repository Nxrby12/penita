<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\PaymentException;
use App\Models\Book;
use App\Payments\PaymentGatewayFactory;
use App\Payments\SimulatedGateway;
use App\Repositories\BookRepository;
use App\Repositories\OrderRepository;

/**
 * Cumpararea unei carti:
 *  1. buy()      creeaza comanda si trimite cumparatorul la pagina de plata (Stripe)
 *  2. success()  Stripe il trimite inapoi; verificam DIRECT la Stripe ca plata a reusit
 *  3. cancel()   cumparatorul a renuntat
 */
class OrderController extends Controller
{
    private OrderRepository $orders;

    public function __construct()
    {
        $this->orders = new OrderRepository();
    }

    public function buy(string $id): void
    {
        $this->requireLogin();
        $user = Auth::user();

        $book = (new BookRepository())->findById((int) $id);
        if ($book === null || !$book->isApproved()) {
            throw new NotFoundException('Carte indisponibila');
        }
        $back = $book->isBlindDate ? '/blind-date' : '/carte/' . $book->slug;

        if ($book->authorId === $user->id || $this->orders->userOwnsBook($user->id, $book->id)) {
            Session::flash('success', 'Cartea e deja în biblioteca ta.');
            $this->redirect('/cont');
        }

        $orderId = $this->orders->createPending($user->id, $book);

        // Cartile gratuite nu trec prin Stripe
        if ($book->price <= 0) {
            $this->orders->markPaid($orderId);
            Session::flash('success', 'Cartea a fost adăugată în biblioteca ta.');
            $this->redirect('/cont');
        }

        try {
            $gateway = PaymentGatewayFactory::make();
            $checkout = $gateway->createCheckout(
                $orderId,
                $this->productName($book),
                (int) round($this->orders->total($orderId) * 100),   // Stripe lucreaza in bani
                absolute_url('/comanda/succes?comanda=' . $orderId),
                absolute_url('/comanda/anulata?comanda=' . $orderId),
            );
        } catch (PaymentException $e) {
            $this->orders->markFailed($orderId);
            error_log($e->getMessage());
            Session::flash('error', 'Plata nu a putut fi pornită. Încearcă din nou mai târziu.');
            $this->redirect($back);
        }

        $this->orders->attachSession($orderId, $checkout->id);
        $this->redirectTo($checkout->url);
    }

    public function success(): void
    {
        $this->requireLogin();
        $order = $this->orderFromQuery();
        $sessionId = (string) ($_GET['session_id'] ?? '');

        // Sesiunea din adresa trebuie sa fie exact cea salvata la comanda
        if ($order->sessionId === null || !hash_equals($order->sessionId, $sessionId)) {
            throw new ForbiddenException('Sesiune de plata necunoscuta');
        }

        if ($order->status !== 'paid') {
            try {
                $paid = PaymentGatewayFactory::make()->isPaid($sessionId, $order->id);
            } catch (PaymentException $e) {
                error_log($e->getMessage());
                $paid = false;
            }
            if (!$paid) {
                Session::flash('error', 'Plata nu a fost confirmată. Dacă ți s-au luat banii, scrie-ne.');
                $this->redirect('/cont');
            }
            $this->orders->markPaid($order->id);
        }

        Session::flash('success', 'Plata a reușit! Cartea te așteaptă în biblioteca ta.');
        $this->redirect('/cont');
    }

    public function cancel(): void
    {
        $this->requireLogin();
        $order = $this->orderFromQuery();
        $this->orders->markFailed($order->id);
        Session::flash('error', 'Ai anulat plata. Nu ți s-a luat niciun ban.');
        $this->redirect('/carti');
    }

    /** Pagina de plata SIMULATA (doar cand payments.driver = simulare). */
    public function simulate(): void
    {
        $this->requireLogin();
        $session = $this->simulatedSession();
        $this->view('orders/simulate', [
            'title'     => 'Plată simulată',
            'sessionId' => $_GET['sesiune'],
            'session'   => $session,
        ]);
    }

    public function simulateConfirm(): void
    {
        $this->requireLogin();
        $session = $this->simulatedSession();
        if (($_POST['action'] ?? '') === 'pay') {
            SimulatedGateway::markPaid((string) $_GET['sesiune']);
            $this->redirectTo($session['success']);
        }
        $this->redirectTo($session['cancel']);
    }

    private function simulatedSession(): array
    {
        if (!PaymentGatewayFactory::isSimulated()) {
            throw new NotFoundException('Plata simulata este dezactivata');
        }
        return SimulatedGateway::find((string) ($_GET['sesiune'] ?? ''))
            ?? throw new NotFoundException('Sesiune simulata inexistenta');
    }

    private function orderFromQuery(): object
    {
        $id = filter_var($_GET['comanda'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        return $this->orders->findForUser($id, Auth::user()->id)
            ?? throw new NotFoundException('Comanda inexistenta');
    }

    /** Pe pagina Stripe, cartile Blind Date nu isi dezvaluie titlul. */
    private function productName(Book $book): string
    {
        return $book->isBlindDate ? "Blind Date with a Book #{$book->id}" : "{$book->title} ({$book->authorName})";
    }
}
