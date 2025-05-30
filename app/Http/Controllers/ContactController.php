<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\ContactCreateRequest;
use App\Http\Requests\Contact\ContactUpdateRequest;
use App\Http\Resources\ContactResource;
use Illuminate\Http\JsonResponse;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $contact = new Contact($data);
        $contact->user_id = $request->user()->id;;
        $contact->save();
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(ContactUpdateRequest $request, $id): ContactResource
    {
        $data = $request->validated();
        $contact = Contact::findOrFail($id);
        $contact->update($data);
        return new ContactResource($contact);
    }

    public function get($id): ContactResource
    {
        // can only get contact that belongs to authed user
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        return new ContactResource($contact);
    }

    public function delete($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }
}
