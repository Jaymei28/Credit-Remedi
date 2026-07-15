<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Waitlist Confirmation</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.5; padding: 20px;">
    <p>Hi {{ $user->name }},</p>

    <p>Welcome to the <strong>Credit Remedi Founding Member Waitlist</strong>! 🎉</p>

    <p>We’re excited to have you onboard. You’re one step closer to unlocking early access to our powerful AI tools for repairing and protecting your credit.</p>

    <p><strong>Want to get in early?</strong> Invite <strong>3 friends</strong> using your unique referral link below. Once 3 people sign up through your link, you’ll unlock:</p>

    <ul>
        <li>✅ Founding Member pricing</li>
        <li>✅ Early access to Credit Remedi AI</li>
        <li>✅ Priority onboarding</li>
    </ul>

    <p><strong>Your referral link:</strong></p>
    <div style="background-color: #f1f1f1; padding: 12px; border-radius: 6px; text-align: center;">
        <strong>
            <a href="{{ route('waitlist.create', ['ref' => $user->referral_code]) }}">
                {{ route('waitlist.create', ['ref' => $user->referral_code]) }}
            </a>
        </strong>
    </div>

    <p style="margin-top: 24px;"><strong>Track your referrals:</strong></p>
    <div style="background-color: #e9f7ef; padding: 12px; border-radius: 6px; text-align: center;">
        <strong>
            <a href="{{ url('/referrals/' . $user->referral_code) }}">
                View My Referral Info
            </a>
        </strong>
    </div>

    <p style="margin-top: 24px;">Thanks for joining,<br>
    — The Credit Remedi Team</p>

    <hr style="margin-top: 30px;">
    <p style="font-size: 12px; color: #999;">
        You're receiving this email because you joined the Credit Remedi waitlist.
    </p>
</body>
</html>
