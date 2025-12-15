<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'service_id', 'booking_date', 'status'];

    // relasi ke user
    public function user() {
        return $this->belongsTo(User::class);
    }

    // relasi ke service
    public function service() {
        return $this->belongsTo(Service::class);
    }
}
