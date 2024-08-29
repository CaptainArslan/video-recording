<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guard = [];

    protected $fillable = [
        'user_id', 'recording_id', 'contact_id', 'contact_name', 'type', 'subject', 'body', 'all_tags', 'status', 'conversation_id', 'message', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    /**
     * Get the status of the share log.
     *
     * @return void
     */
    public function getStatus()
    {
        $status = $this->status;
        if ($status == 0) {
            return '<span class="badge badge-danger">Failed</span>';
        } else if ($status == 1) {
            return '<span class="badge badge-primary">Sent</span>';
        }
    }
}
