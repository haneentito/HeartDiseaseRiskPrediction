<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Save to the contacts table
        Contact::create($request->all());

        // Log the data for verification
        \Log::info('Contact Form Submission:', $request->all());

        return response()->json(['message' => 'Contact message received successfully'], 200);
    }
}