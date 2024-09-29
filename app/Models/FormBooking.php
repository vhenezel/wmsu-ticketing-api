<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function form()
    {
        return $this->belongsTo(Form::class, 'formId', 'id');
    }
}
