<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'date',
        'time',
        'college',
        'course',
        'proxyName',
    ];

    public function client()
    {
        return $this->belongsTo(User::class);
    }

    public function formBookings(): HasMany
    {
        return $this->hasMany(related: FormBooking::class, foreignKey: 'bookingId');
    }
}
