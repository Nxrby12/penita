<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CAPTCHA proprie, desenata pe server cu biblioteca GD.
 *
 *  - codul are 5 caractere, fara cele usor de confundat (0/O, 1/I/l)
 *  - in sesiune se pastreaza doar un hash al codului, cu termen de 5 minute
 *  - fiecare cod se poate folosi O SINGURA DATA (dupa verificare e sters)
 *  - imaginea are caractere rotite, linii si puncte de zgomot, ca sa fie greu de citit automat
 */
class Captcha
{
    private const KEY = '_captcha';
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const LENGTH = 5;
    private const TTL = 300;

    /** Genereaza un cod nou si il salveaza (ca hash) in sesiune. */
    public static function generate(): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }
        Session::set(self::KEY, ['hash' => hash('sha256', $code), 'expires' => time() + self::TTL]);
        return $code;
    }

    /** Verifica raspunsul (fara diferenta intre litere mari si mici) si invalideaza codul. */
    public static function verify(?string $answer): bool
    {
        $stored = Session::get(self::KEY);
        Session::forget(self::KEY);

        if (!is_array($stored) || $stored['expires'] < time() || !is_string($answer)) {
            return false;
        }
        $answer = strtoupper(trim($answer));
        return hash_equals($stored['hash'], hash('sha256', $answer));
    }

    /** Imaginea PNG cu codul dat. */
    public static function render(string $code): string
    {
        $w = 200;
        $h = 64;
        $img = imagecreatetruecolor($w, $h);
        $paper = [247, 242, 232];
        imagefill($img, 0, 0, imagecolorallocate($img, ...$paper));

        // 1. fiecare caracter: desenat mic, rotit, marit si lipit la o pozitie aleatorie
        $x = 12;
        foreach (str_split($code) as $char) {
            $tile = imagecreatetruecolor(12, 18);
            $bg = imagecolorallocate($tile, ...$paper);
            imagefill($tile, 0, 0, $bg);
            $ink = imagecolorallocate($tile, random_int(20, 90), random_int(20, 60), random_int(40, 90));
            imagestring($tile, 5, 1, 1, $char, $ink);

            $rotated = imagerotate($tile, random_int(-25, 25), $bg);
            $rw = imagesx($rotated);
            $rh = imagesy($rotated);
            $scale = random_int(26, 30) / 10;
            imagecopyresampled($img, $rotated, $x, random_int(0, 10), 0, 0,
                               (int) ($rw * $scale), (int) ($rh * $scale), $rw, $rh);
            imagedestroy($tile);
            imagedestroy($rotated);
            $x += random_int(34, 37);
        }

        // 2. zgomot peste litere: puncte si linii, ca sa incurce citirea automata
        for ($i = 0; $i < 450; $i++) {
            $c = imagecolorallocate($img, random_int(120, 210), random_int(110, 200), random_int(100, 180));
            imagesetpixel($img, random_int(0, $w - 1), random_int(0, $h - 1), $c);
        }
        for ($i = 0; $i < 5; $i++) {
            $c = imagecolorallocate($img, random_int(60, 160), random_int(60, 130), random_int(60, 130));
            imagesetthickness($img, random_int(1, 2));
            imageline($img, random_int(0, 40), random_int(0, $h), random_int($w - 40, $w), random_int(0, $h), $c);
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        return (string) ob_get_clean();
    }
}
