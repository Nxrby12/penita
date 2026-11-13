<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Limitarea cererilor: maximum 100 de cereri pe minut de la aceeasi adresa IP.
 * Peste limita, cererile sunt blocate (HTTP 429) pana se termina minutul.
 *
 * Numaratoarea se tine in tabelul `rate_limits` (o fereastra fixa de 60 de secunde pe IP),
 * cu o singura interogare atomica, deci functioneaza corect si la cereri simultane.
 */
class RateLimiter
{
    public function __construct(
        private PDO $db,
        private int $maxRequests = 100,
        private int $windowSeconds = 60,
    ) {}

    /** Inregistreaza cererea si intoarce cate cereri a facut IP-ul in fereastra curenta. */
    public function hit(string $ip): int
    {
        // Daca fereastra a expirat, porneste una noua cu 1 cerere; altfel adauga 1.
        // Ordinea conteaza: request_count se calculeaza inainte ca window_start sa fie schimbat.
        $stmt = $this->db->prepare(
            'INSERT INTO rate_limits (ip, window_start, request_count) VALUES (:ip, NOW(), 1)
             ON DUPLICATE KEY UPDATE
                request_count = IF(window_start <= NOW() - INTERVAL :w1 SECOND, 1, request_count + 1),
                window_start  = IF(window_start <= NOW() - INTERVAL :w2 SECOND, NOW(), window_start)'
        );
        $stmt->execute(['ip' => $ip, 'w1' => $this->windowSeconds, 'w2' => $this->windowSeconds]);

        return $this->count($ip);
    }

    public function maxRequests(): int
    {
        return $this->maxRequests;
    }

    /** Cate secunde mai sunt pana se deblocheaza IP-ul. */
    public function retryAfter(string $ip): int
    {
        $stmt = $this->db->prepare(
            'SELECT GREATEST(0, :w - TIMESTAMPDIFF(SECOND, window_start, NOW())) FROM rate_limits WHERE ip = :ip'
        );
        $stmt->execute(['ip' => $ip, 'w' => $this->windowSeconds]);
        return (int) $stmt->fetchColumn();
    }

    private function count(string $ip): int
    {
        $stmt = $this->db->prepare('SELECT request_count FROM rate_limits WHERE ip = :ip');
        $stmt->execute(['ip' => $ip]);
        return (int) $stmt->fetchColumn();
    }
}
