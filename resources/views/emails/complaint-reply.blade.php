{{--
    resources/views/emails/complaint-reply.blade.php

    Sent to a Help Center visitor when an administrator replies.

    Styling is inline and table-free-ish on purpose: email clients strip
    <style> blocks (Gmail keeps them, Outlook does not), so anything that
    must survive is written as a style attribute.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $originalTitle }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#13176b;">

    <div style="max-width:600px;margin:0 auto;padding:24px 16px 40px;">

        {{-- Header --}}
        <div style="background:#0a1f6e;border-radius:14px 14px 0 0;padding:22px 26px;">
            <div style="color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:2px;">
                UPSKILL
            </div>
            <div style="color:#c9d2f5;font-size:12px;margin-top:4px;">
                Pangasinan State University &middot; Help Center
            </div>
        </div>

        {{-- Body --}}
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-top:none;
                    border-radius:0 0 14px 14px;padding:26px;">

            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Hi {{ $recipientName }},
            </p>

            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#4b5563;">
                Thanks for contacting the UPSKILL Help Center. Here is our reply to your
                message:
            </p>

            {{-- The admin's reply --}}
            <div style="background:#f7f9ff;border-left:4px solid #0a1f6e;border-radius:8px;
                        padding:16px 18px;margin-bottom:24px;">
                <div style="font-size:15px;line-height:1.65;color:#13176b;white-space:pre-wrap;">{{ $reply }}</div>
            </div>

            {{-- What they originally sent, for context --}}
            <div style="border-top:1px solid #e5e7eb;padding-top:18px;">
                <div style="font-size:11px;font-weight:bold;letter-spacing:0.5px;
                            text-transform:uppercase;color:#6b7280;margin-bottom:8px;">
                    Your original message
                </div>
                <div style="font-size:14px;font-weight:bold;margin-bottom:6px;">
                    {{ $originalTitle }}
                </div>
                <div style="font-size:13.5px;line-height:1.6;color:#6b7280;white-space:pre-wrap;">{{ $originalBody }}</div>
            </div>

            <p style="margin:24px 0 0;font-size:14px;line-height:1.6;color:#4b5563;">
                If you need anything else, just reply to this email and it will reach us.
            </p>

            <p style="margin:18px 0 0;font-size:14px;line-height:1.6;">
                &mdash; {{ $adminName }}<br>
                <span style="color:#6b7280;font-size:13px;">UPSKILL Administrator</span>
            </p>
        </div>

        {{-- Footer --}}
        <div style="text-align:center;padding:18px 10px 0;">
            <div style="font-size:11.5px;color:#8a8fa8;line-height:1.6;">
                Sent {{ $sentAt->format('M j, Y g:i A') }} &middot;
                &copy; {{ date('Y') }} Pangasinan State University
            </div>
            <div style="font-size:11.5px;color:#8a8fa8;margin-top:6px;">
                <a href="{{ url('/') }}" style="color:#0a1f6e;text-decoration:underline;">
                    Visit UPSKILL
                </a>
            </div>
        </div>
    </div>
</body>
</html>
