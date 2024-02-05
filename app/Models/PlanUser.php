<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanUser extends Model
{
    use HasFactory;
    protected $table = 'plan_user';

    protected $fillable = [
        'user_id',
        'plan_id',

        'status',
    ];
}
