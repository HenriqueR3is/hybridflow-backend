<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
        public function reservations()
        {
            return $this->hasMany(\App\Models\Reservation::class);
        }

    protected $fillable = ['name', 'capacity'];
}
