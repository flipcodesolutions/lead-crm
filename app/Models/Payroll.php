<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'hra',
        'allowances',
        'other_earnings',
        'gross_salary',
        'tax',
        'pf_deduction',
        'other_deduction',
        'net_salary',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'allowances' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getMonthNameAttribute(): string
    {
        return Carbon::create()->month($this->month)->format('F');
    }
}
