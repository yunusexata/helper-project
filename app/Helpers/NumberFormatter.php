<?php

namespace App\Helpers;

class NumberFormatter
{
    const DECIMAL_POIN = 2;

    public static function format($number, $decimalPoin = self::DECIMAL_POIN)
    {
        $decimalPoin = fmod($number, 1) !== 0.00 ? $decimalPoin : 0;

        return number_format($number, $decimalPoin, ',', '.');
    }

    public static function imaskToValue($data)
    {

        if (blank($data)) {
            return null;
        }

        $value = trim($data);

        // Indonesian format: 90.000,99
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value); // remove thousand
            $value = str_replace(',', '.', $value); // decimal -> dot
        } else {
            // Only thousand separator
            $value = str_replace('.', '', $value);
        }

        return $value;
    }

    public static function valueToImask($data)
    {
        return str($data)->replace('.', ',')->toString();
    }

    public static function round($data)
    {
        return floor($data * pow(10, self::DECIMAL_POIN)) / pow(10, self::DECIMAL_POIN);
    }

    public static function denominator($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';
        if ($nilai < 12) {
            $temp = ' '.$huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = self::denominator($nilai - 10).' Belas';
        } elseif ($nilai < 100) {
            $temp = self::denominator($nilai / 10).' Puluh'.self::denominator($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus'.self::denominator($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::denominator($nilai / 100).' Ratus'.self::denominator($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu'.self::denominator($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::denominator($nilai / 1000).' Ribu'.self::denominator($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::denominator($nilai / 1000000).' Juta'.self::denominator($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::denominator($nilai / 1000000000).' Milyar'.self::denominator(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::denominator($nilai / 1000000000000).' Trilyun'.self::denominator(fmod($nilai, 1000000000000));
        }

        return $temp;
    }
}
