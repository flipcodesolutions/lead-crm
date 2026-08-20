<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'hra',
        'allowances',
        'other_earnings',
        'gross_salary',
        'pf_deduction',
        'other_deduction',
        'annual_salary',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'allowances' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'annual_salary' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'status' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
