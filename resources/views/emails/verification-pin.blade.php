<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your GradeGenius verification code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 24px; color: #18181b;">
    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e4e4e7; padding: 28px;">
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;">GradeGenius Reports</div>
        <h1 style="margin: 8px 0 16px 0; font-size: 20px;">Verify your email</h1>
        <p style="font-size: 14px; line-height: 1.6; margin: 0 0 16px 0;">Hi {{ $user->name }}, enter this code on the GradeGenius page you already have open:</p>
        <div style="font-size: 34px; font-weight: 800; letter-spacing: 0.35em; text-align: center; background-color: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 16px 0 16px 0.35em; color: #0f766e;">{{ $pin }}</div>
        <p style="font-size: 13px; line-height: 1.6; color: #52525b; margin: 16px 0 0 0;">The code works for {{ $validMinutes }} minutes. Don't open a new browser; go back to the tab where you signed up. If you didn't create a GradeGenius account, you can ignore this email.</p>
    </div>
</body>
</html>
