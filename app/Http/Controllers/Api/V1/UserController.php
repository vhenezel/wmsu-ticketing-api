<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
  public function index()
  {
    return User::all();
  }

  public function all()
  {
    return UserResource::collection(User::all());
  }

  public function show(int $user_id)
  {
    $user = User::with('userDetails')->findOrFail($user_id);
    return UserResource::make($user)->toArraySingle();
  }

  public function allForms()
  {
    return Form::all();
  }

  public function getUserBookings(Request $request)
  {
    $userId = trim($request->query('userId'), '"\' ');

    if (!is_numeric($userId)) {
      return response()->json(['message' => 'Invalid userId.'], 400);
    }

    $user = User::with('bookings.formBookings.form')->find($userId);

    if (!$user) {
      return response()->json(['message' => 'User not found.'], 404);
    }

    $bookingsWithFormNames = $user->bookings->map(function ($booking) {
      $booking->form_bookings = $booking->formBookings->map(function ($formBooking) {
        return [
          'id' => $formBooking->id,
          'bookingId' => $formBooking->bookingId,
          'created_at' => $formBooking->created_at,
          'updated_at' => $formBooking->updated_at,
          'form' => [
            'id' => $formBooking->form->id,
            'name' => $formBooking->form->name,
            'description' => $formBooking->form->description,
          ],
        ];
      });

      return $booking;
    });

    return response()->json(['bookings' => $bookingsWithFormNames], 200);
  }
}
