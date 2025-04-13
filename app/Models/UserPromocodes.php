<?php

namespace App\Models;
use App\Models\Promocode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPromocodes extends Model
{
    use HasFactory;

    protected $table = 'user_promocodes';

    protected $fillable = [
        'user_id',
        'promocode_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promocode()
    {
        return $this->belongsTo(Promocode::class);
    }
}
