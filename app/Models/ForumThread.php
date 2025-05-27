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
    public function searchableAs(): string
    {
        return 'forum_threads_index'; // MeiliSearch index name for threads
    }

    /**
     * Get the indexable data array for the model.
     * This defines what data from the ForumThread will be sent to the search index.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['id'] = (int) $this->id;
        $array['title'] = $this->title;
        $array['body'] = $this->body;
        $array['created_at'] = $this->created_at->timestamp;
        $array['creator_name'] = $this->creator()->username ?? null; 
        $array['topic_name'] = $this->topic()->name ?? null;

        unset(
            $array['updated_at'],
            $array['creator_id'],
            $array['topic_id'],  
        );

        return $array;
    }
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
