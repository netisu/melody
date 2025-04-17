<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserBan extends Model
{
    use HasFactory;

    protected $table = 'user_bans';

    protected $fillable = [
        'user_id',
        'banner_id',
        'type',
        'note',
        'length',
    ];

    protected $appends = ['DateHum','exactTime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    function getUserBanType(): string
    {
        switch ($this->type) {
            case 'self-deletion':
                return 'Requested Account Deletion';
            case 'warning':
                return 'Warning';
            case '7-day-ban':
                return '7 Day Ban';
            case '14-day-ban':
                return '14 Day Ban';
            case '30-day-ban':
                return '30 Day Ban';
            case '60-day-ban':
                return '60 Day Ban';
            case 'account-deletion':
                return 'Account Deleted';
            default:
                return $this->type; // Return the original type if not a known type
        }
    }
    public function getDateHumAttribute()
    {
        return Carbon::parse($this->length)->toFormattedDateString();
    }
    public function getexactTimeAttribute()
    {
        return Carbon::parse($this->length)->format('H:i');
    }
}
