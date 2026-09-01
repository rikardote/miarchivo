<?php

namespace App\Services;

class RfcHelper
{
    protected static array $inconvenientWords = [
        'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO', 'COGE', 'COJA',
        'COJE', 'COJI', 'COJO', 'CULO', 'FETO', 'GUEY', 'JOTO', 'KACA', 'KACO', 'KAGA',
        'KAGO', 'KOGE', 'KOJO', 'KULO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MION',
        'MOCO', 'MULA', 'PEDA', 'PEDO', 'PENE', 'PUTA', 'PUTO', 'QULO', 'RATA', 'RUIN'
    ];

    /**
     * Calcula la base de 10 caracteres del RFC de una persona física.
     */
    public static function calculate10(string $name, string $paternal, ?string $maternal, string $birthDate): string
    {
        $name = self::cleanString($name);
        $paternal = self::cleanString($paternal);
        $maternal = $maternal ? self::cleanString($maternal) : '';

        // 1. Filtrar nombres comunes 'JOSE' o 'MARIA' si hay segundo nombre
        $nameParts = array_filter(explode(' ', $name));
        $firstName = reset($nameParts) ?: 'X';
        if (count($nameParts) > 1 && in_array(strtoupper($firstName), ['JOSE', 'MARIA', 'MA', 'MA.', 'J', 'J.'])) {
            $firstName = $nameParts[1] ?? $firstName;
        }

        // 2. Primera letra del paterno
        $letra1 = mb_substr($paternal, 0, 1);

        // 3. Primera vocal interna del paterno
        $paternalRest = mb_substr($paternal, 1);
        preg_match('/[AEIOU]/u', $paternalRest, $matches);
        $letra2 = $matches[0] ?? (mb_substr($paternal, 1, 1) ?: 'X');

        // 4. Primera letra del materno (o 'X' si no tiene)
        $letra3 = !empty($maternal) ? mb_substr($maternal, 0, 1) : 'X';

        // 5. Primera letra del nombre
        $letra4 = mb_substr($firstName, 0, 1);

        $initials = strtoupper($letra1 . $letra2 . $letra3 . $letra4);

        // 6. Validar palabras inconvenientes
        if (in_array($initials, self::$inconvenientWords)) {
            $initials = substr($initials, 0, 3) . 'X';
        }

        // 7. Formatear Fecha (YYMMDD)
        $timestamp = strtotime($birthDate);
        $datePart = $timestamp ? date('ymd', $timestamp) : '000101';

        return $initials . $datePart;
    }

    protected static function cleanString(string $text): string
    {
        $text = mb_strtoupper(trim($text), 'UTF-8');
        
        // Quitar acentos
        $search  = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
        $replace = ['A', 'E', 'I', 'O', 'U', 'U'];
        $text = str_replace($search, $replace, $text);

        // Remover caracteres no alfabéticos
        return preg_replace('/[^A-ZÑ& ]/u', '', $text);
    }
}
