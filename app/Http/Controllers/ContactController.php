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
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user= $request->user();

        $validated = $request->validate([
            'name' => $user ? 'nullable|string|max:255' : 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => $user ? 'nullable|email|max:255' : 'required|email|max:255',
            'message' => 'required|string',
        ]);

        if ($user) {
            $validated['name']  = $validated['name']  ?? $user->name;
            $validated['email'] = $validated['email'] ?? $user->email;
            $validated['phone'] = $validated['phone'] ?? $user->phone ?? null;
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
