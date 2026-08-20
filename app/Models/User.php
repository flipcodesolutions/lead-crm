<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'assigned_to');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function leadNotes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }

    public function customNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }

    // Role helper methods
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        $roleName = strtolower(trim($this->role->name));

        if (is_array($roles)) {
            $roles = array_map(fn($r) => strtolower(trim($r)), $roles);
            return in_array($roleName, $roles);
        }

        return $roleName === strtolower(trim($roles));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isTelecaller(): bool
    {
        return $this->hasRole('telecaller');
    }

    public function isSalesperson(): bool
    {
        return $this->hasRole(['sales', 'salesperson']);
    }
}
