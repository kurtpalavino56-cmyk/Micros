<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your UPSKILL password was changed</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5fb;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5fb; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 18px rgba(20,25,70,.10);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background-color:#1b2350; padding:26px 24px;">
                            <span style="font-family:Arial,Helvetica,sans-serif; font-size:24px; font-weight:bold; letter-spacing:4px; color:#f0b429;">UPSKILL</span>
                            <div style="font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#aab1d6; margin-top:4px; letter-spacing:1px;">PANGASINAN STATE UNIVERSITY</div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px 32px 8px; font-family:Arial,Helvetica,sans-serif; color:#2a2f4a;">
                            <h1 style="font-size:19px; margin:0 0 12px; color:#1b2350;">Your password was changed</h1>
                            <p style="font-size:14px; line-height:1.6; margin:0 0 16px;">
                                Hi {{ $toName }}, this is a confirmation that the password on your
                                UPSKILL account was changed on <strong>{{ $resetAt }}</strong>.
                            </p>
                            <p style="font-size:14px; line-height:1.6; margin:0;">
                                If you made this change, no further action is needed. If you did
                                <strong>not</strong>, contact your administrator immediately — someone
                                else may have access to your account.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 32px 28px; font-family:Arial,Helvetica,sans-serif;">
                            <hr style="border:none; border-top:1px solid #e4e7f2; margin:0 0 16px;">
                            <p style="font-size:11px; color:#9aa0bf; line-height:1.6; margin:0;">
                                This is an automated message from UPSKILL — please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
