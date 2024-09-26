<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookingId',
        'formId',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
