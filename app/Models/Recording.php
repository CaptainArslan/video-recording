<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recording extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'file', 'file_url', 'short_url', 'poster', 'poster_url', 'duration', 'status', 'size', 'type', 'make_it_private', 'share', 'embed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['title_dt', 'enc_id'];

    public function getTitleDtAttribute()
    {
        // Access the 'created_at' attribute of the model
        $title = $this->getAttribute('title');
        return formatTimestamp($title, 'M d, Y');
    }

    public function getEncIdAttribute()
    {
        // Access the 'created_at' attribute of the model
        $id = $this->getAttribute('id');
        return encrypt($id);
    }


    public function shareLogs()
    {
        return $this->hasMany(ShareLog::class);
    }
}
