<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRfc implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rfc = strtoupper(trim((string) $value));

        // 1. Longitud exacta de 10 caracteres (sin homoclave)
        if (strlen($rfc) !== 10) {
            $fail("El RFC debe tener exactamente 10 caracteres.");
            return;
        }

        // 2. Formato: 4 letras alfabéticas + 6 dígitos numéricos (AAAA YY MM DD)
        if (!preg_match('/^[A-ZÑ&]{4}[0-9]{6}$/', $rfc)) {
            $fail("El formato del RFC no es válido (debe componerse de 4 letras iniciales y 6 dígitos de fecha YYMMDD).");
            return;
        }

        // 3. Validar consistencia de la fecha (YYMMDD)
        $year = (int) substr($rfc, 4, 2);
        $month = (int) substr($rfc, 6, 2);
        $day = (int) substr($rfc, 8, 2);

        // Mes debe ser entre 1 y 12
        if ($month < 1 || $month > 12) {
            $fail("El mes en el RFC no es válido (debe ser entre 01 y 12).");
            return;
        }

        // Resolver siglo para validación de bisiestos
        $currentYearShort = (int) date('y');
        $fullYear = ($year > $currentYearShort) ? 1900 + $year : 2000 + $year;

        // Validar fecha exacta en el calendario (evita 30 de febrero, 31 de abril, etc.)
        if (!checkdate($month, $day, $fullYear)) {
            $fail("La fecha de nacimiento en el RFC ({$day}/{$month}/{$year}) no corresponde a un día real del calendario.");
            return;
        }
    }
}
