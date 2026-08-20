<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'financial_year',
        'annual_income',
        'taxable_income',
        'calculated_tax',
        'paid_tax',
        'remaining_tax',
    ];

    protected $casts = [
        'annual_income' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'calculated_tax' => 'decimal:2',
        'paid_tax' => 'decimal:2',
        'remaining_tax' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
