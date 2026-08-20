<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'opportunity_number',
        'name',
        'customer_name',
        'company_name',
        'expected_revenue',
        'probability',
        'expected_closing_date',
        'stage_id',
        'assigned_to',
        'description',
        'status',
        'won_at',
        'lost_at',
        'lost_reason',
    ];

    protected $casts = [
        'expected_revenue' => 'decimal:2',
        'probability' => 'integer',
        'expected_closing_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public static function generateOpportunityNumber(): string
    {
        $latest = static::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return 'OPP-' . date('Ymd') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
    }
}
