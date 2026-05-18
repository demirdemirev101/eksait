<?php

namespace App\Http\Controllers;

use App\Mail\AdminContactMessageMail;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => $user ? 'sometimes' : 'required|string|max:255',
            'phone' => $user ? 'sometimes' : 'nullable|string|max:20',
            'email' => $user ? 'sometimes' : 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($user) {
            $validated['name'] = $user->name;
            $validated['email'] = $user->email;
            $validated['phone'] = $user->phone;
        }

        $contact = Contact::create($validated);

        try {
            Mail::to(config('mail.admin_address'))->send(new AdminContactMessageMail($contact));
        } catch (\Throwable $e) {
            Log::error('Contact message email failed', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Съобщението е получено. Ще се свържем с вас възможно най-скоро.',
        ], 201);
    }
}
