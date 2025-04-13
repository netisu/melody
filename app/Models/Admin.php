<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as AeoAuthenticatable;

use Illuminate\Support\Facades\{
    Cache,
    Redis,
    Auth
};

class Admin extends AeoAuthenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins'; // Specify the actual table name
    protected $fillable = ['adminPoints', 'user_id', 'role_id']; // Allow mass assignment of points
    protected $guard = 'admin';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rolePermissions($key)
    {
        $roles = Cache::remember('admin_role_for_' . Auth::id(), now()->addMinutes(30), function () {
            return AdminRoles::where('id',  $this->role_id)->first(); // Change to use the find() method instead of where() + first()
        });

        if (!$roles) {
            return null; // or return a default value or throw an exception
        }

        // Use optional chaining to access the property safely
        return optional($roles)->$key;
    }
}
