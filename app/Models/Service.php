<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'price'];

    // relasi ke booking
    public function bookings() {
        return $this->hasMany(Booking::class);
    }
}
