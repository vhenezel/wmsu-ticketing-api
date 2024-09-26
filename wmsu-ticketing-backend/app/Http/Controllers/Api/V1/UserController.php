<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Form;
use App\Models\User;

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
}
