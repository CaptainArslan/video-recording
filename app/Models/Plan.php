<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'name', 'price', 'recording_limit', 'description', 'status',
    ];

    public function userS(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
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
}
