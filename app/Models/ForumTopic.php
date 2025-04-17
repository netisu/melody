<?php

namespace App\Models;

use App\Models\ForumThread;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class ForumTopic extends Model
{
    use HasFactory;

    protected $table = 'forum_topics';

    protected $fillable = [
        'name',
        'description',
        'home_page_priority',
        'is_staff_only_viewing',
        'role_required_to_post'
    ];

    public function threads($hasPagination = true)
    {
        if (Auth::check() && Auth::user()->staff == 1)
            $threads = ForumThread::where('in_topic_id', '=', $this->id)->orderBy('is_pinned', 'DESC')->orderBy('updated_at', 'DESC');
        else
            $threads = ForumThread::where([
                ['in_topic_id', '=', $this->id],
                ['is_deleted', '=', false]
            ])->orderBy('is_pinned', 'DESC')->orderBy('updated_at', 'DESC');

        return ($hasPagination) ? $threads->paginate(8) : $threads->get();
    }

    public function slug()
    {
        $name = str_replace('-', ' ', $this->name);

        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
}
