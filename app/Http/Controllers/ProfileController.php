<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [];

        // If password is being updated
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        // If any profile fields are being updated
        if (
            $request->filled('name') || 
            $request->filled('address') || 
            $request->filled('contact_number') ||
            $request->filled('city') ||
            $request->filled('state') ||
            $request->filled('zipcode') || 
            $request->filled('ssn_last4')
            
        ) {
            $rules['name'] = 'required|string|max:255';
            $rules['address'] = 'nullable|string|max:255';
            $rules['contact_number'] = 'nullable|string|max:20';
            $rules['city'] = 'nullable|string|max:100';
            $rules['state'] = 'nullable|string|max:50';
            $rules['zipcode'] = 'nullable|string|max:10';
            $rules['ssn_last4'] = 'nullable|digits:4';
        }

        $validated = $request->validate($rules);

        // Update profile fields if present
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
            $user->address = $validated['address'] ?? null;
            $user->contact_number = $validated['contact_number'] ?? null;
            $user->city = $validated['city'] ?? null;
            $user->state = $validated['state'] ?? null;
            $user->zipcode = $validated['zipcode'] ?? null;
            $user->ssn_last4 = $validated['ssn_last4'] ?? null;
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
