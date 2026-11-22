<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 24px; color: #18181b;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 2px solid #09090b; overflow: hidden; box-shadow: 4px 4px 0px 0px #09090b;">
        {{-- Header --}}
        <div style="background-color: #fef08a; padding: 24px; border-bottom: 2px solid #09090b;">
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;">
                GradeGenius Reports · Official Class Notice
            </div>
            <h1 style="margin: 8px 0 0 0; font-size: 22px; font-weight: 800; color: #09090b;">
                {{ $announcement->title }}
            </h1>
            <p style="margin: 4px 0 0 0; font-size: 13px; font-weight: 600; color: #27272a;">
                {{ $announcement->schoolClass->name }} · {{ $announcement->schoolClass->subject }} · Grade {{ $announcement->schoolClass->grade }}
            </p>
        </div>

        {{-- Content --}}
        <div style="padding: 24px;">
            <p style="font-size: 15px; line-height: 1.6; margin-top: 0;">
                Dear {{ $parent->name }},
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #52525b;">
                Your learner's educator, <strong>{{ $announcement->teacher->name }}</strong>, has published an important announcement for <strong>{{ $announcement->schoolClass->name }} ({{ $announcement->schoolClass->subject }})</strong>:
            </p>

            <div style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 20px 0; font-size: 14px; line-height: 1.6; color: #0f172a; white-space: pre-line;">
{{ $announcement->body }}
            </div>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e4e4e7;">
                <a href="{{ route('parent.announcements.show', $announcement->id) }}"
                   style="display: inline-block; background-color: #0284c7; color: #ffffff; text-decoration: none; padding: 12px 24px; font-weight: 700; font-size: 13px; border-radius: 10px; border: 2px solid #09090b;">
                    View in Parent Portal →
                </a>
            </div>

            <p style="margin-top: 24px; font-size: 13px; color: #71717a;">
                Kind regards,<br>
                <strong>{{ $announcement->teacher->name }}</strong><br>
                GradeGenius Academic Intelligence
            </p>
        </div>

        {{-- Footer --}}
        <div style="background-color: #fafafa; border-top: 1px solid #e4e4e7; padding: 16px 24px; font-size: 11px; color: #a1a1aa; text-align: center;">
            This email was sent to {{ $parent->email }} as a verified guardian on GradeGenius.
        </div>
    </div>
</body>
</html>
