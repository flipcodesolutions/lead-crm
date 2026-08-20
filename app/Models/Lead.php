<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_number',
        'name',
        'company_name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'country',
        'source_id',
        'status_id',
        'stage_id',
        'assigned_to',
        'created_by',
        'expected_value',
        'description',
        'next_follow_up_at',
        'converted_at',
        'lost_reason',
        'status',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',
        'status' => 'integer',
    ];

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class)->latest('assigned_at');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class)->latest('follow_up_date');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function opportunity()
    {
        return $this->hasOne(Opportunity::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function emails()
    {
        return $this->hasMany(Email::class)->latest();
    }

    // Helper to generate next unique lead number
    public static function generateLeadNumber(): string
    {
        $latest = static::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return 'LD-' . date('Ymd') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
    }
}
