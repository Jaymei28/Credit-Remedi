<?php

namespace App\Http\Controllers;

use App\Models\WaitlistUser;
use Illuminate\Http\Request;
use App\Mail\WaitlistJoinedMail;
use App\Mail\ReferralMilestoneMail;
use Illuminate\Support\Facades\Mail;

class WaitlistController extends Controller
{

    public function create(Request $request)
    {
        return view('waitlist.create', [
            'ref' => $request->query('ref')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:waitlist_users,email',
            'challenge' => 'required',
            'usage' => 'required',
            'timeline' => 'required',
        ]);

        // 🔍 Find referrer by referral code from form input
        $referrer = WaitlistUser::where('referral_code', $request->referrer_code)->first();

        // 📝 Create new waitlist user
        $user = WaitlistUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'challenge' => $request->challenge,
            'usage' => $request->usage,
            'timeline' => $request->timeline,
            'referrer_id' => optional($referrer)->id,
        ]);

        Mail::to($user->email)->send(new WaitlistJoinedMail($user));


        // 🔁 Increment referrer's count
        if ($referrer) {
            $referrer->increment('referral_count');
            $referrer->refresh();
            
            if ($referrer->referral_count >= 3 && $referrer->referral_count <= 6) {
                Mail::to($referrer->email)->send(new ReferralMilestoneMail($referrer));
            }
        }

        return redirect()->back()->with('status', 'Thank you! You’ve joined the waitlist.');
    }

    public function report()
    {
        $waitlistUsers = \App\Models\WaitlistUser::with('referrer')->paginate(20);

        return view('admin.waitlist-report', compact('waitlistUsers'));
    }

    public function publicReferrals($code)
    {
        $user = WaitlistUser::where('referral_code', $code)->firstOrFail();

        $referrals = WaitlistUser::where('referrer_id', $user->id)
        ->latest()
        ->get();
        
        
        return view('guest.public-referrals', compact('user', 'referrals'));
    }

}
