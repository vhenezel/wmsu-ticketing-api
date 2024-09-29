<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                "email" => "required|email|unique:users,email",
                "password" => "required",
                "firstName" => "required",
                "lastName" => "required",
                "middleName" => "nullable",
                "schoolId" => "required",
                "schoolStatus" => "nullable",
                "course" => "nullable",
                "collegeName" => "nullable",
                "gender" => "required",
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $user = User::create([
            "email" => $request->email,
            "password" => $request->password,
        ]);

        $user->userDetails()->create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'middleName' => $request->middleName,
            'schoolId' => $request->schoolId,
            'schoolStatus' => $request->schoolStatus,
            'course' => $request->course,
            'collegeName' => $request->collegeName,
            'gender' => $request->gender,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registered Successfully',
            'token' => $user->createToken('API TOKEN', ['*'], now()->addDay())->plainTextToken,
        ], 200);
    }

    public function login(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                "email" => "required|email",
                "password" => "required",
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials!',
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $userDetails = $user->userDetails;
        $newToken = $user->createToken('API TOKEN', ['*'], now()->addDay());

        session([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Logged In Successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'token' => $newToken->plainTextToken,
                'tokenExpiry' => $newToken->accessToken->expires_at,
                'userDetails' => $userDetails,
            ],
        ], 200);
    }
}