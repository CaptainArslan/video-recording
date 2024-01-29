<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GhlAuth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ghl_access_token', 'ghl_refresh_token', 'user_type', 'location_id',
    ];
}
