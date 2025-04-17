<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status_log';

    protected $fillable = [
        'creator_id',
        'message',
        'created_at',
        'updated_at'
    ];
    protected $appends = ['DateHum'];
  
    public function getDateHumAttribute()
    {
        return $this->created_at->diffForHumans();
    }
  
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }
}
