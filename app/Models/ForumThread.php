<?php

namespace App\Models;

use App\Models\ForumReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class ForumThread extends Model
{
    use HasFactory;

    protected $table = 'forum_threads';

    protected $fillable = [
        'topic_id',
        'creator_id',
        'title',
        'body'
    ];

    protected $appends = ['DateHum'];

    public function getDateHumAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class, 'thread_id');
    }

    public function slug(): string
    {
        $text = $this->title;

        // Convert to lowercase and replace spaces with a separator
        $text = strtolower(trim(preg_replace('/\s+/', '-', $text)));

        $allowedChars = 'a-z0-9-'; // Adjust this to include desired characters
        $text = preg_replace('/[^' . $allowedChars . ']/', '', $text);
        $text = preg_replace('/-{2,}/', '-', $text);

        // Handle trailing separators
        $text = trim($text, '-');

        return $text;
    }
}
