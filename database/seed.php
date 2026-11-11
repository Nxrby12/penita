<?php
/**
 * Date demonstrative: 3 autori si 8 carti aprobate, cu coperti.
 * Folositor ca site-ul sa nu fie gol la prezentare si pe hosting.
 *
 * Rulare, din folderul proiectului:   php database/seed.php
 * Se poate rula de mai multe ori: cartile care exista deja sunt sarite.
 */
declare(strict_types=1);

use App\Core\Database;
use App\Services\BookStorage;
use App\Support\Str;

if (PHP_SAPI !== 'cli') {
    exit('Acest script se ruleaza doar din terminal.');
}

define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

$db = Database::connection();

$authors = [
    'ana_pop'    => 'Ana Pop',
    'mihai_radu' => 'Mihai Radu',
    'ioana_dima' => 'Ioana Dima',
];

$books = [
    ['Umbra dragonului de sticlă', 'ana_pop', 29.99, ['Fantasy'],
     "Ultimul dragon din munții Apuseni e făcut din sticlă și nu-și mai amintește cine l-a creat.\n\nMira, ucenica unui sticlar din Brad, are trei nopți să-i redea memoria înainte ca prima rază de soare să-l spargă."],
    ['Ultimul semnal de pe Europa', 'mihai_radu', 34.50, ['Science Fiction', 'Mister'],
     "Stația de cercetare de sub gheața lui Europa a tăcut acum unsprezece zile. Apoi, un singur mesaj: o melodie de leagăn românească.\n\nInginera Dana Vlad coboară să afle cine o cântă, la 600 de milioane de kilometri de casă."],
    ['Casa de pe strada Lipscani', 'ioana_dima', 24.99, ['Mister'],
     "O casă veche din centrul Bucureștiului își schimbă camerele în fiecare noapte. Noul ei proprietar găsește în pod jurnalul unei croitorese din 1928.\n\nFiecare pagină pare să descrie ce se va întâmpla a doua zi."],
    ['Scrisori neexpediate', 'ana_pop', 19.99, ['Romance'],
     "Douăzeci de ani de scrisori scrise și niciodată trimise, găsite într-o cutie de pantofi la o vânzare de garaj din Cluj.\n\nIrina pornește să afle dacă destinatarul mai trăiește și dacă mai are rost să afle tot adevărul."],
    ['Ce ascunde ceața', 'mihai_radu', 27.00, ['Thriller'],
     "Un sat din Delta Dunării rămâne izolat trei zile din cauza ceții. Când ceața se ridică, lipsește un om, iar toți ceilalți au aceeași poveste, repetată cuvânt cu cuvânt.\n\nComisarul Toma nu crede nicio silabă."],
    ['Rădăcini de oraș', 'ioana_dima', 15.00, ['Poezie'],
     "Patruzeci de poeme despre blocuri, tramvaie de noapte și bunici care țin roșii pe balcon.\n\nO carte despre cum crești rădăcini într-un oraș care nu stă niciodată pe loc."],
    ['Liniștea dinaintea zorilor', 'mihai_radu', 22.50, ['Horror'],
     "În satul Valea Neagră nimeni nu vorbește între ora trei și ora patru dimineața. Nimeni nu mai știe de ce.\n\nUn student la etnografie vine să afle și face greșeala să întrebe cu voce tare."],
    ['Mic manual de început', 'ioana_dima', 18.00, ['Dezvoltare personala'],
     "Un ghid scurt și practic pentru cei care amână de prea mult timp primul pas: primul roman, prima alergare, prima afacere.\n\nȘapte capitole, fiecare cu un exercițiu de zece minute."],
];

$genreIds = $db->query('SELECT name, id FROM genres')->fetchAll(PDO::FETCH_KEY_PAIR);
$authorRole = (int) $db->query("SELECT id FROM roles WHERE name = 'autor'")->fetchColumn();

// 1. Autorii (cu parola aleatorie: sunt conturi doar demonstrative)
$authorIds = [];
foreach ($authors as $username => $penName) {
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :u');
    $stmt->execute(['u' => $username]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $db->prepare('INSERT INTO users (role_id, username, email, password_hash) VALUES (:r, :u, :e, :h)')
           ->execute(['r' => $authorRole, 'u' => $username, 'e' => "{$username}@demo.penita.ro",
                      'h' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
        $id = $db->lastInsertId();
        $db->prepare('INSERT INTO author_profiles (user_id, pen_name) VALUES (:id, :pen)')
           ->execute(['id' => $id, 'pen' => $penName]);
        echo "Autor creat: {$penName}\n";
    }
    $authorIds[$username] = (int) $id;
}

// 2. Cartile
@mkdir(BookStorage::coverDir(), 0755, true);
@mkdir(BookStorage::pdfDir(), 0755, true);

foreach ($books as $i => [$title, $author, $price, $genres, $description]) {
    $slug = Str::slug($title);
    $exists = $db->prepare('SELECT 1 FROM books WHERE slug = :s');
    $exists->execute(['s' => $slug]);
    if ($exists->fetchColumn()) {
        echo "Există deja: {$title}\n";
        continue;
    }

    $cover = bin2hex(random_bytes(16)) . '.jpg';
    copy(__DIR__ . "/demo/{$slug}.jpg", BookStorage::coverDir() . '/' . $cover);

    $pdf = bin2hex(random_bytes(16)) . '.pdf';
    file_put_contents(BookStorage::pdfDir() . '/' . $pdf, demoPdf($title, $authors[$author]));

    $db->prepare(
        "INSERT INTO books (author_id, title, slug, description, price, cover_path, pdf_path, status, reviewed_at, created_at)
         VALUES (:a, :t, :s, :d, :p, :c, :f, 'approved', NOW(), NOW() - INTERVAL :days DAY)"
    )->execute(['a' => $authorIds[$author], 't' => $title, 's' => $slug, 'd' => $description,
                'p' => $price, 'c' => $cover, 'f' => $pdf, 'days' => $i]);
    $bookId = (int) $db->lastInsertId();

    foreach ($genres as $genre) {
        $db->prepare('INSERT INTO book_genres (book_id, genre_id) VALUES (:b, :g)')
           ->execute(['b' => $bookId, 'g' => $genreIds[$genre]]);
    }
    echo "Carte adăugată: {$title}\n";
}

echo "Gata.\n";

/** Un PDF minimal de o pagina, cu titlul si autorul. */
function demoPdf(string $title, string $author): string
{
    $ascii = fn(string $s) => strtr($s, ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't',
                                         'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ț' => 'T', '(' => '', ')' => '']);
    $text = sprintf("BT /F1 24 Tf 60 700 Td (%s) Tj 0 -40 Td /F1 14 Tf (de %s) Tj 0 -40 Td (Exemplar demonstrativ - Penita) Tj ET",
                    $ascii($title), $ascii($author));
    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
        "<< /Length " . strlen($text) . " >>\nstream\n{$text}\nendstream",
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];
    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $n => $obj) {
        $offsets[] = strlen($pdf);
        $pdf .= ($n + 1) . " 0 obj\n{$obj}\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $o) {
        $pdf .= sprintf("%010d 00000 n \n", $o);
    }
    return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
}
