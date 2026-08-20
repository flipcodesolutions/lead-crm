<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_income',
        'max_income',
        'tax_rate',
        'fixed_tax',
        'status',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'fixed_tax' => 'decimal:2',
        'status' => 'integer',
    ];

    /**
     * Calculate total annual tax for a given taxable income based on active slabs.
     */
    public static function calculateAnnualTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 0) {
            return 0.00;
        }

        $slabs = self::where('status', 1)->orderBy('min_income', 'asc')->get();
        if ($slabs->isEmpty()) {
            return 0.00;
        }

        $totalTax = 0.00;

        foreach ($slabs as $slab) {
            if ($taxableIncome > $slab->min_income) {
                $upperLimit = $slab->max_income !== null ? min($taxableIncome, $slab->max_income) : $taxableIncome;
                $taxableAmount = $upperLimit - $slab->min_income;

                if ($taxableAmount > 0) {
                    $totalTax += ($taxableAmount * ($slab->tax_rate / 100)) + $slab->fixed_tax;
                }
            }
        }

        return round($totalTax, 2);
    }
}
