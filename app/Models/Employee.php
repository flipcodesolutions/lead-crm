<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'user_id',
        'department_id',
        'designation_id',
        'manager_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'joining_date',
        'address',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function currentSalary()
    {
        return $this->hasOne(EmployeeSalary::class)->where('status', 1)->latestOfMany();
    }

    public function leaveAllocations()
    {
        return $this->hasMany(LeaveAllocation::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function taxes()
    {
        return $this->hasMany(EmployeeTax::class);
    }
}
