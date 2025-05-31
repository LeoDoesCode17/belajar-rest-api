<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\AddressCreateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function create(AddressCreateRequest $request, $contactId): JsonResponse
    {
        // contactId must be found in authed user contacts
        $user = Auth::user();
        $contacts = $user->contacts->where('id', $contactId)->first();
        if (!$contacts) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contactId;
        $address->save();
        return (new AddressResource($address))->response()->setStatusCode(201);
    }
}
