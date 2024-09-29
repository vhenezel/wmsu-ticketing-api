<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingEvent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
  public function index()
  {
    return Booking::all();
  }

  public function store(Request $request)
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
        'appointmentNumber' => 'required|integer',
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
      'bookedTime' => $request->time,
      'college' => $request->college,
      'course' => $request->course,
      'proxyName' => $request->proxyName,
      'appointmentNumber' => $request->appointmentNumber
    ]);

    foreach ($request->formIds as $formId) {
      $booking->formBookings()->create([
        'bookingId' => $booking->id,
        'formId' => $formId,
      ]);
    }

    event(new BookingEvent($this->updateRequestNowServing()));

    return response()->json([
      'status' => true,
      'message' => 'Booked Successfully',
    ], 200);
  }

  public function bookedTimes(Request $request)
  {
    $date = trim($request->query('date'), '"\' ');

    $date = \Carbon\Carbon::parse($date)->format('Y-m-d');

    $bookings = Booking::whereDate('date', $date)
      ->whereIn('status', ['Booked', 'Waiting Room'])
      ->pluck('bookedTime');

    return response()->json([
      'booked_times' => $bookings,
    ], 200);
  }

  public function getLastAppointmentNum()
  {
    $latestAppointment = Booking::orderBy('appointmentNumber', 'desc')->first();

    return response()->json([
      'latestAppointmentNumber' => $latestAppointment ? $latestAppointment->appointmentNumber : null
    ], 200);
  }

  public function updateQueueOnline()
  {
    $currentDate = now()->toDateString();
    $currentTime = now();
    $fivePmToday = now()->setTime(17, 0);

    $upcomingBookings = Booking::where('date', $currentDate)
      ->where(function ($query) use ($currentTime) {
        $query->where('status', 'Booked')
          ->where('bookedTime', '>', $currentTime)
          ->orWhere('status', 'Waiting In Line');
      })
      ->with(['formBookings.form', 'client'])
      ->orderBy('bookedTime', 'asc')
      ->get()->fresh();

    $response = $upcomingBookings->map(function ($booking) {
      return [
        'id' => $booking->id,
        'date' => $booking->date,
        'time' => $booking->bookedTime,
        'college' => $booking->college,
        'appointmentNumber' => $booking->appointmentNumber,
        'status' => $booking->status,
        'proxyName' => $booking->proxyName,
        'client' => [
          'id' => $booking->clientId,
          'firstName' => $booking->client->userDetails->firstName,
          'lastName' => $booking->client->userDetails->lastName
        ],
        'formBookings' => $booking->formBookings,
      ];
    });

    return $response;
  }

  public function getCurrentRequestServing()
  {
    $currentDate = now()->toDateString();
    $currentTime = now()->startOfMinute();
    $nowServing = null;

    // Retrieve the booking that is currently "Now Serving"
    $currentNowServing = Booking::where('status', 'Now Serving')->with(['formBookings.form', 'client'])->first();
    $nextWaitingInLine = Booking::where('status', 'Waiting In Line')->with(['formBookings.form', 'client'])->orderBy('bookedTime', 'asc')->first();

    // Check if the current "Now Serving" booking has passed 5 minutes
    if ($currentNowServing) {
      $bookTime = $currentNowServing->getOriginal('nowServingAt');
      $nowServingTime = Carbon::parse($bookTime)->startOfMinute();

      if ($nowServingTime->diffInMinutes($currentTime) >= 5 && !$currentNowServing->serving) {
        // If it's been more than 5 minutes, change status to "Waiting Room"
        $currentNowServing->status = 'Waiting Room'; // Update only status
        $currentNowServing->saveQuietly();

        if ($nextWaitingInLine) {
          $nextWaitingInLine->update(['status' => 'Now Serving']);
          $nextWaitingInLine->update(['nowServingAt' => Carbon::now()]);

          $nowServing = [
            'id' => $nextWaitingInLine->id,
            'date' => $nextWaitingInLine->date,
            'time' => $nextWaitingInLine->bookedTime,
            'college' => $nextWaitingInLine->college,
            'appointmentNumber' => $nextWaitingInLine->appointmentNumber,
            'status' => $nextWaitingInLine->status,
            'proxyName' => $nextWaitingInLine->proxyName,
            'client' => [
              'id' => $nextWaitingInLine->clientId,
              'studentId' => $nextWaitingInLine->client->userDetails->schoolId,
              'firstName' => $nextWaitingInLine->client->userDetails->firstName,
              'lastName' => $nextWaitingInLine->client->userDetails->lastName,
            ],
            'formBookings' => $nextWaitingInLine->formBookings,
          ];
        } else {
          $nowServing = null;
        }
      } else {
        $nowServing = [
          'id' => $currentNowServing->id,
          'date' => $currentNowServing->date,
          'time' => $currentNowServing->bookedTime,
          'college' => $currentNowServing->college,
          'appointmentNumber' => $currentNowServing->appointmentNumber,
          'status' => $currentNowServing->status,
          'proxyName' => $currentNowServing->proxyName,
          'client' => [
            'id' => $currentNowServing->clientId,
            'studentId' => $currentNowServing->client->userDetails->schoolId,
            'firstName' => $currentNowServing->client->userDetails->firstName,
            'lastName' => $currentNowServing->client->userDetails->lastName,
          ],
          'formBookings' => $currentNowServing->formBookings,
        ];
      }
    }

    return $nowServing;
  }

  public function updateRequestNowServing()
  {
    $currentDate = now()->toDateString();
    $currentTime = now()->startOfMinute();
    $nowServing = null;

    $nowServing = $this->getCurrentRequestServing();

    $todaysBookings = Booking::where('date', $currentDate)
      ->whereIn('status', ['Booked', 'Waiting In Line'])
      ->with(['formBookings.form', 'client'])
      ->orderBy('bookedTime', 'asc')
      ->get()->fresh();

    $nowServingExists = Booking::where('date', $currentDate)
      ->where('status', 'Now Serving')
      ->where('serving', true)
      ->exists();


    // Loop through each booking and check if the booking time matches the current time
    foreach ($todaysBookings as $booking) {
      $origTime = $booking->getOriginal('bookedTime');
      $bookingTime = Carbon::parse($origTime)->startOfMinute(); // Compare without changing original time

      if ($booking->status == 'Waiting In Line' && ($bookingTime->isPast() || $bookingTime->eq($currentTime)) && !$nowServingExists) {
        $booking->update(['status' => 'Now Serving']);
        $booking->update(['nowServingAt' => Carbon::now()]);

        $nowServing = [
          'id' => $booking->id,
          'date' => $booking->date,
          'time' => $booking->bookedTime,
          'college' => $booking->college,
          'appointmentNumber' => $booking->appointmentNumber,
          'status' => $booking->status,
          'proxyName' => $booking->proxyName,
          'client' => [
            'id' => $booking->clientId,
            'studentId' => $booking->client->userDetails->schoolId,
            'firstName' => $booking->client->userDetails->firstName,
            'lastName' => $booking->client->userDetails->lastName,
          ],
          'formBookings' => $booking->formBookings,
        ];
        break;
      } else if ($bookingTime->eq($currentTime) && !$nowServingExists) {
        // Update booking status to "Now Serving"
        $booking->update(['status' => 'Now Serving']);
        $booking->update(['nowServingAt' => Carbon::now()]);
        $nowServing = [
          'id' => $booking->id,
          'date' => $booking->date,
          'time' => $booking->bookedTime,
          'college' => $booking->college,
          'appointmentNumber' => $booking->appointmentNumber,
          'status' => $booking->status,
          'proxyName' => $booking->proxyName,
          'client' => [
            'id' => $booking->clientId,
            'studentId' => $booking->client->userDetails->schoolId,
            'firstName' => $booking->client->userDetails->firstName,
            'lastName' => $booking->client->userDetails->lastName,
          ],
          'formBookings' => $booking->formBookings,
        ];
        break;
      } else if ($nowServingExists && $bookingTime->eq($currentTime)) {
        $booking->update(['status' => 'Waiting In Line']);
      }
    }


    // Return the updated queue
    $queue = $this->updateQueueOnline();

    return [
      'time' => $currentTime,
      'queue' => $queue,
      'nowServing' => $nowServing,
    ];
  }

  public function serveCurrent(Request $request)
  {
    $validate = Validator::make(
      $request->all(),
      [
        'bookingId' => 'required|integer',
      ]
    );

    if ($validate->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Validation Error',
        'errors' => $validate->errors()
      ], 401);
    }

    $booking = Booking::where('id', $request->bookingId)->first();

    if ($booking->status == "Now Serving") {
      $booking->update(['serving' => true]);

      return response()->json([
        'status' => true,
        'message' => 'Success',
      ], 200);
    } else {
      return response()->json([
        'status' => false,
        'message' => 'This booking is currently not in serving!',
      ], 401);
    }
  }

  public function updateBookingStatus(Request $request)
  {
    $validate = Validator::make($request->all(), [
      "bookingId" => 'required|integer',
      "remarks" => 'nullable|string',
      "returnDate" => 'nullable|date',
      "status" => 'required|string'
    ]);

    if ($validate->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Validation Error',
        'errors' => $validate->errors()
      ], 401);
    }

    $booking = Booking::where('id', $request->bookingId)->first();
    $booking->update([
      'status' => $request->status,
      'remarks' => $request->remarks,
      'returnDate' => $request->returnDate,
      'serving' => false,
    ]);

    Event::dispatch(new BookingEvent($this->updateRequestNowServing()));

    return response()->json([
      'status' => true,
      'message' => 'Success',
    ], 200);
  }

  public function getAllBooking()
  {
    $bookings = Booking::with(['formBookings.form', 'client'])->get()->fresh();

    return response()->json([
      'status' => true,
      'bookings' => $bookings->map(function ($booking) {
        return [
          'id' => $booking->id,
          'date' => $booking->date,
          'time' => $booking->bookedTime,
          'college' => $booking->college,
          'appointmentNumber' => $booking->appointmentNumber,
          'status' => $booking->status,
          'proxyName' => $booking->proxyName,
          'client' => [
            'id' => $booking->client->id,
            'studentId' => $booking->client->userDetails->schoolId,
            'firstName' => $booking->client->userDetails->firstName,
            'lastName' => $booking->client->userDetails->lastName,
          ],
          'formBookings' => $booking->formBookings,
        ];
      })
    ], 200);
  }
}