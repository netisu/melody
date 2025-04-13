<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class AdminRoles extends Model
{
    use HasFactory;

    protected $table = 'admin_roles'; // Specify the actual table name
    protected $fillable = ['name', 'description', 'user_id']; // Allow mass assignment of points
}
