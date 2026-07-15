<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Founding Member Access Unlocked</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>🎉 Congrats, {{ $referrer->name }}!</h2>
    <p>You’ve referred 3 people — that means you’ve just unlocked <strong>Founding Member access</strong> to Credit Remedi AI. 👑</p>

    <p>You can now choose your preferred plan and get started:</p>

    <ul>
        <li><strong>Pro Plan (Starter):</strong> $49/month</li>
        <li><strong>Turbo Plan (Premium):</strong> $69/month</li>
    </ul>

    <p style="margin-top: 20px;">
        <a href="{{ url('/plans') }}" style="background-color: #198754; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
            Choose My Plan
        </a>
    </p>

    <p>If you have any questions, feel free to reply to this email.</p>

    <p style="margin-top: 30px;">– The Credit Remedi Team</p>
</body>
</html>
