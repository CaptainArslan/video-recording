<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'exp' => Carbon::now()->addWeek(1)->timestamp, // Set token expiration to 30 days from now
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'email',
        'phone',
        'role',
        'location_id',
        'ghl_api_key',
        'email_verified_at',
        'password',
        'added_by',
        'image',
        'status',
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
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)->withPivot('start_date', 'end_date', 'status')->withTimestamps();
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class);
    }



    /**
     * Interact with the user's first name.
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }

    /**
     * Interact with the user's last name.
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = strtolower($value);
    }

    public function getStatus()
    {
        switch ($this->status) {
            case 1:
                return '<span class="badge bg-primary rounded-3 fw-semibold">Active</span>';
            default:
                return '<span class="badge bg-danger rounded-3 fw-semibold">Inactive</span>';
        }
    }

    public function getRole()
    {
        switch ($this->role) {
            case 0:
                return 'Admin';
            case 1:
                return 'Company';
            default:
                return 'User';
        }
    }

    public function getCompleteName()
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name;
    }
    //     public function getPlanName()
    //     {
    //         return ucfirst(trim($this->plan->name));
    //     }
}
