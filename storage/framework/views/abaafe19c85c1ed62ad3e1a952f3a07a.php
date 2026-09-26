<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your UPSKILL verification code</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5fb;">
    
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5fb; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 18px rgba(20,25,70,.10);">

                    
                    <tr>
                        <td align="center" style="background-color:#1b2350; padding:26px 24px;">
                            <span style="font-family:Arial,Helvetica,sans-serif; font-size:24px; font-weight:bold; letter-spacing:4px; color:#f0b429;">UPSKILL</span>
                            <div style="font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#aab1d6; margin-top:4px; letter-spacing:1px;">PANGASINAN STATE UNIVERSITY</div>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:32px 32px 8px; font-family:Arial,Helvetica,sans-serif; color:#2a2f4a;">
                            <h1 style="font-size:19px; margin:0 0 12px; color:#1b2350;">Password reset verification</h1>
                            <p style="font-size:14px; line-height:1.6; margin:0 0 20px;">
                                Hi <?php echo e($toName); ?>, we received a request to reset the password on your
                                UPSKILL account. Enter this code on the verification page:
                            </p>

                            
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="background-color:#f4f5fb; border:2px dashed #1b2350; border-radius:10px; padding:20px;">
                                        <span style="font-family:'Courier New',Courier,monospace; font-size:34px; font-weight:bold; letter-spacing:10px; color:#1b2350;"><?php echo e($code); ?></span>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; line-height:1.6; margin:20px 0 0; color:#6a7194;">
                                This code expires in <strong><?php echo e($ttlMinutes); ?> minutes</strong> and can be
                                used once. If you did not request a password reset, you can ignore this
                                email — your password stays the same.
                            </p>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:24px 32px 28px; font-family:Arial,Helvetica,sans-serif;">
                            <hr style="border:none; border-top:1px solid #e4e7f2; margin:0 0 16px;">
                            <p style="font-size:11px; color:#9aa0bf; line-height:1.6; margin:0;">
                                This is an automated message from UPSKILL — please do not reply.
                                Never share this code with anyone, including people claiming to be
                                staff. UPSKILL will never ask for it.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\Users\Kurt Palavino\Herd\UpSkill-Microcredential_Platform\resources\views/emails/verification-code.blade.php ENDPATH**/ ?>