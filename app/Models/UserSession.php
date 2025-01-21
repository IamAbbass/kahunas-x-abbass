<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = ['client_id', 'scheduled_at', 'completed'];

    public function client() {
        return $this->belongsTo(Client::class);
    }
}
