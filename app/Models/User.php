<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\TeamMember;
use App\Models\AiProviderKey;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company',
        'verification_code',
        'verification_expires_at',
        'verification_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_expires_at' => 'datetime',
        'verification_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function teamInvitations()
    {
        return $this->hasMany(TeamMember::class, 'owner_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function aiProviderKeys()
    {
        return $this->hasMany(AiProviderKey::class);
    }
}
