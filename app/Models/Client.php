<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['coach_id', 'name', 'email'];

    public function coach() {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function userSessions() {
        return $this->hasMany(UserSession::class);
    }
}
