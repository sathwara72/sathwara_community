<?php

namespace App\Support;

class NumberToWords
{
    private static array $units = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
    ];

    private static array $tens = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    ];

    /**
     * Convert amount into Indian numbering currency words (e.g. Twenty-Five Thousand)
     */
    public static function convert(float|int $amount): string
    {
        $number = round((float)$amount, 2);
        $integerPart = (int) floor($number);
        $fractionPart = (int) round(($number - $integerPart) * 100);

        if ($integerPart === 0 && $fractionPart === 0) {
            return 'Zero';
        }

        $words = '';
        if ($integerPart > 0) {
            $words = self::convertIntegerToIndianWords($integerPart);
        }

        if ($fractionPart > 0) {
            $paiseWords = self::convertBelowThousand($fractionPart);
            $words = empty($words) ? $paiseWords . ' Paise' : $words . ' and ' . $paiseWords . ' Paise';
        }

        return trim($words);
    }

    private static function convertIntegerToIndianWords(int $num): string
    {
        if ($num === 0) {
            return '';
        }

        $crores = (int) floor($num / 10000000);
        $remainder = $num % 10000000;

        $lakhs = (int) floor($remainder / 100000);
        $remainder = $remainder % 100000;

        $thousands = (int) floor($remainder / 1000);
        $remainder = $remainder % 1000;

        $result = '';

        if ($crores > 0) {
            $result .= self::convertIntegerToIndianWords($crores) . ' Crore ';
        }

        if ($lakhs > 0) {
            $result .= self::convertBelowThousand($lakhs) . ' Lakh ';
        }

        if ($thousands > 0) {
            $result .= self::convertBelowThousand($thousands) . ' Thousand ';
        }

        if ($remainder > 0) {
            $result .= self::convertBelowThousand($remainder);
        }

        return trim($result);
    }

    private static function convertBelowThousand(int $num): string
    {
        $result = '';

        if ($num >= 100) {
            $hundreds = (int) floor($num / 100);
            $num = $num % 100;
            $result .= self::$units[$hundreds] . ' Hundred ';
        }

        if ($num >= 20) {
            $tensDigit = (int) floor($num / 10);
            $unitsDigit = $num % 10;
            if ($unitsDigit > 0) {
                $result .= self::$tens[$tensDigit] . '-' . self::$units[$unitsDigit];
            } else {
                $result .= self::$tens[$tensDigit];
            }
        } elseif ($num > 0) {
            $result .= self::$units[$num];
        }

        return trim($result);
    }
}
