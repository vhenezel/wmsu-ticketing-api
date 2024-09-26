<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
  public function index()
  {
    return Booking::all();
  }

  public function create(Request $request)
  {
    $validateBooking = Validator::make(
      $request->all(),
      [
        'clientId' => 'required|integer',
        'date' => 'required|date_format:Y-m-d',
        'time' => 'required|date_format:Y-m-d H:i:s',
        'college' => 'required|string|max:255',
        'course' => 'required|string|max:255',
        'proxyName' => 'nullable|string|max:255',
        'formIds' => 'required|array',
        'formIds.*' => 'integer',
      ]
    );

    if ($validateBooking->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Validation Error',
        'errors' => $validateBooking->errors()
      ], 401);
    }

    $booking = Booking::create([
      'clientId' => $request->clientId,
      'date' => $request->date,
      'time' => $request->time,
      'college' => $request->college,
      'course' => $request->course,
      'proxyName' => $request->proxyName,
    ]);

    foreach ($request->formIds as $formId) {
      $booking->formBookings()->create([
        'bookingId' => $booking->id,
        'formId' => $formId,
      ]);
    }

    return response()->json([
      'status' => true,
      'message' => 'Booked Successfully',
    ], 200);
  }

  public function bookedTimes(Request $request)
  {
    $date = trim($request->query('date'), '"\' ');

    $date = \Carbon\Carbon::parse($date)->format('Y-m-d');

    $bookings = Booking::whereDate('date', $date)->pluck('time');

    return response()->json([
      'booked_times' => $bookings,
    ], 200);
  }
}
