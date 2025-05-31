<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\AddressCreateRequest;
use App\Http\Requests\Address\AddressUpdateRequest;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index($contactId): AddressCollection
    {
        $user = Auth::user();
        $contact = $user->contacts->where('id', $contactId)->first();
        $addresses = $contact->addresses;
        if (!$contact || !$addresses) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        return new AddressCollection($addresses);
    }

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

    public function update(AddressUpdateRequest $request, $contactId, $addressId): AddressResource
    {
        $user = Auth::user();
        $contact = $user->contacts->where('id', $contactId)->first();
        $address = Address::where('id', $addressId)->where('contact_id', $contactId)->first();
        if (!$contact || !$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        $data = $request->validated();
        $address->update($data);
        return new AddressResource($address);
    }

    public function delete($contactId, $addressId): JsonResponse
    {
        $user = Auth::user();
        $contact = $user->contacts->where('id', $contactId)->first();
        $address = Address::where('id', $addressId)->where('contact_id', $contactId)->first();
        if (!$contact || !$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        $address->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function show($contactId, $addressId): AddressResource
    {
        $user = Auth::user();
        $contact = $user->contacts->where('id', $contactId)->first();
        $address = Address::where('id', $addressId)->where('contact_id', $contactId)->first();
        if (!$contact || !$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        return new AddressResource($address);
    }
}
