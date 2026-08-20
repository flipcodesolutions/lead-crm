<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_days',
        'is_paid',
        'description',
        'status',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'is_paid' => 'boolean',
        'status' => 'integer',
    ];

    public function allocations()
    {
        return $this->hasMany(LeaveAllocation::class);
    }

    public function requests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
