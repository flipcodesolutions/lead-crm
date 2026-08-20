<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'stage_id');
    }
}
