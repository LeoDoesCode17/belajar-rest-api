<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Pest\Laravel\json;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

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
}
