<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Format number according to Indian Numbering System (Lakhs, Crores).
     * e.g. 150000 -> 1,50,000.00
     * e.g. 10000000 -> 1,00,00,000.00
     */
    public static function formatInr(mixed $amount, int $decimals = 2): string
    {
        if (!is_numeric($amount)) {
            return '0.00';
        }

        $amount = (float)$amount;
        $isNegative = $amount < 0;
        $amount = abs($amount);

        // Format to fixed decimals
        $formatted = number_format($amount, $decimals, '.', '');
        $parts = explode('.', $formatted);
        $num = $parts[0];
        $dec = isset($parts[1]) ? '.' . $parts[1] : '';

        if (strlen($num) > 3) {
            $lastThree = substr($num, -3);
            $remaining = substr($num, 0, -3);
            $remaining = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $remaining);
            $num = $remaining . ',' . $lastThree;
        }

        return ($isNegative ? '-' : '') . $num . $dec;
    }

    /**
     * Format with Currency Symbol (Default: ₹)
     */
    public static function format(mixed $amount, int $decimals = 2): string
    {
        $symbol = Setting::get('currency_symbol', '₹');
        return $symbol . self::formatInr($amount, $decimals);
    }

    /**
     * Format Indian Phone Number (+91 XXXXX XXXXX or standard 10 digit)
     */
    public static function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Clean non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If 10 digits (Standard Indian Mobile) -> +91 XXXXX XXXXX
        if (strlen($digits) === 10 && in_array(substr($digits, 0, 1), ['6', '7', '8', '9'])) {
            return '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5, 5);
        }

        // If 12 digits starting with 91 -> +91 XXXXX XXXXX
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $tenDigits = substr($digits, 2);
            return '+91 ' . substr($tenDigits, 0, 5) . ' ' . substr($tenDigits, 5, 5);
        }

        // If 11 digits starting with 0 -> +91 XXXXX XXXXX
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $tenDigits = substr($digits, 1);
            return '+91 ' . substr($tenDigits, 0, 5) . ' ' . substr($tenDigits, 5, 5);
        }

        return $phone;
    }
}
