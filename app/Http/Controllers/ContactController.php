<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\ContactCreateRequest;
use App\Http\Requests\Contact\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
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
        $data['user_id'] = Auth::user()->id;
        $contact = Contact::create($data);
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(ContactUpdateRequest $request, $id): ContactResource
    {
        $data = $request->validated();
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
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
        $user = Auth::user();
        $contact = $user->contacts->where('id', $id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['not found']
                ]
            ])->setStatusCode(404));
        }
        $contact->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1); // if doesn't exist then set to 1 (when i passed this variable after perpage in paginate(), the results are null)
        $size = $request->input('size', 10); // if doesn't exist then set to 10
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $contacts = Contact::where('user_id', $user->id)
            ->when($name, function ($query, $name) {
                return $query->where(function ($query) use ($name) {
                    $query->where('first_name', 'like', "%{$name}%")
                        ->orWhere('last_name', 'like', "%{$name}%");
                });
            })
            ->when($email, function ($query, $email) {
                return $query->where('email', 'like', "%{$email}%");
            })
            ->when($phone, function ($query, $phone) {
                return $query->where('phone', 'like', "%{$phone}%");
            })->paginate($size, ['*'], 'page', $page);
        return new ContactCollection($contacts);
    }
}
