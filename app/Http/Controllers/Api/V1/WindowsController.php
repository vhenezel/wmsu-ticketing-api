<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Window;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WindowsController extends Controller
{
  public function index()
  {
    return Window::all();
  }

  public function create(Request $request)
  {
    $validateWindow = Validator::make(
      $request->all(),
      [
        'windowNumber' => 'required|integer',
        'type' => 'required|string',
      ]
    );

    if ($validateWindow->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Validation Error',
        'errors' => $validateWindow->errors()
      ], 401);
    }

    $window = Window::create([
      'window_number' => $request->windowNumber,
      'type' => $request->type
    ]);

    return response()->json([
      'status' => true,
      'message' => 'Added Successfully',
    ], 200);
  }

  public function all()
  {
    return Window::with('teller')->get()->map(function ($window) {
      return [
        'id' => $window->id,
        'window_number' => $window->window_number,
        'assignedTeller' => $window->teller ? $window->teller->userDetails->firstName . ' ' . $window->teller->userDetails->lastName : null,
        'tellerId' => $window->assigned_id,
        'type' => $window->type,
        'method' => $window->method,
        'status' => $window->status,
        'created_at' => $window->created_at,
        'updated_at' => $window->updated_at,
      ];
    });
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
