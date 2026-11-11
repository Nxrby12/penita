<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;

/**
 * Valideaza datele din formulare dupa reguli simple:
 *   required, email, min:8, max:50, same:camp, in:a,b, alpha_dash
 *
 * Exemplu:
 *   $data = Validator::validate($_POST, ['email' => 'required|email']);
 */
class Validator
{
    private const MESSAGES = [
        'required'   => 'Câmpul este obligatoriu.',
        'email'      => 'Adresa de email nu este validă.',
        'min'        => 'Trebuie să aibă cel puțin %s caractere.',
        'max'        => 'Poate avea cel mult %s caractere.',
        'same'       => 'Valorile nu coincid.',
        'in'         => 'Valoarea aleasă nu este permisă.',
        'alpha_dash' => 'Sunt permise doar litere, cifre, - și _.',
    ];

    /**
     * @param array<string, mixed>  $input
     * @param array<string, string> $rules
     * @return array<string, string> datele validate (curatate de spatii)
     * @throws ValidationException
     */
    public static function validate(array $input, array $rules): array
    {
        $clean = [];
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = trim((string) ($input[$field] ?? ''));
            $clean[$field] = $value;

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                if ($value === '' && $name !== 'required') {
                    continue; // campurile optionale goale nu se mai verifica
                }
                if (!self::passes($name, $value, $param, $input)) {
                    $errors[$field] = sprintf(self::MESSAGES[$name], $param);
                    break; // un singur mesaj pe camp
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
        return $clean;
    }

    private static function passes(string $rule, string $value, ?string $param, array $input): bool
    {
        return match ($rule) {
            'required'   => $value !== '',
            'email'      => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min'        => mb_strlen($value) >= (int) $param,
            'max'        => mb_strlen($value) <= (int) $param,
            'same'       => $value === (string) ($input[$param] ?? ''),
            'in'         => in_array($value, explode(',', (string) $param), true),
            'alpha_dash' => preg_match('/^[\pL\pN_-]+$/u', $value) === 1,
            default      => throw new \InvalidArgumentException("Regula necunoscuta: {$rule}"),
        };
    }
}
