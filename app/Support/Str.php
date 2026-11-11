<?php
declare(strict_types=1);

namespace App\Support;

/** Functii ajutatoare pentru texte. */
class Str
{
    private const RO = ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
                        'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't'];

    /**
     * Transforma un titlu intr-o adresa prietenoasa (SEO):
     * "Umbra Dragonului: Partea a II-a" -> "umbra-dragonului-partea-a-ii-a"
     */
    public static function slug(string $text): string
    {
        $text = strtr($text, self::RO);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim((string) $text, '-');
        return $text !== '' ? mb_substr($text, 0, 200) : 'carte';
    }
}
