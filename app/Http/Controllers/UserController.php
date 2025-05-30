<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Pest\Laravel\json;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();
        $user = User::where('username', $data['username'])->first();
        $passwordCorrect = Hash::check($data['password'], $user->password);
        if (!$user || !$passwordCorrect) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Wrong credentials']
                ]
            ], 401));
        }
        $user->token = Str::uuid()->toString();
        $user->save();
        return new UserResource($user); // if the response is 200, we don't have to change to JSONResponse
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->update($data);
        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->token = null;
        $user->save();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }
}
