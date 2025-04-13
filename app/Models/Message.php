<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    public $table = 'messages';
    protected $fillable = ['id', 'sent_from', 'sent_to', 'message'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_from');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_to');
    }

    public function getTimeAttribute(): string {
        return date(
            "d M Y, H:i:s",
            strtotime($this->attributes['created_at'])
        );
    }
}
