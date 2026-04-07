<?php
/**
 * Password-reset email template.
 * Expected variables (set by the caller before ob_start):
 *   string $resetUrl – the verify-reset URL (already HTML-escaped)
 */
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Reset your Nexo password</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f3f4f6;padding:40px 0;">
    <tr>
      <td align="center" style="padding:0 16px;">

        <!-- Card -->
        <table width="600" cellpadding="0" cellspacing="0" border="0"
               style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

          <!-- ── Header ── -->
          <tr>
            <td style="background:#7431e8;padding:28px 40px;text-align:center;">
              <span style="color:#ffffff;font-size:28px;font-weight:700;letter-spacing:-0.5px;line-height:1;">Nexo</span>
            </td>
          </tr>

          <!-- ── Body ── -->
          <tr>
            <td style="padding:36px 40px 32px;">

              <h2 style="margin:0 0 16px;color:#111827;font-size:20px;font-weight:600;line-height:1.3;">
                Password Reset Request
              </h2>

              <p style="margin:0 0 12px;color:#374151;font-size:15px;line-height:1.6;">Hello,</p>

              <p style="margin:0 0 12px;color:#374151;font-size:15px;line-height:1.6;">
                We received a request to reset the password for your Nexo account.
                Click the button below to verify it&#8217;s you, then continue to create a new password.
              </p>

              <p style="margin:0 0 28px;color:#374151;font-size:15px;line-height:1.6;">
                This link is valid for <strong style="color:#111827;">1 hour</strong>.
              </p>

              <!-- CTA Button -->
              <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                <tr>
                  <td style="background:#7431e8;border-radius:8px;text-align:center;">
                    <a href="<?= $resetUrl ?>"
                       style="display:inline-block;padding:13px 30px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;border-radius:8px;line-height:1.4;">
                      Verify &amp; Reset Password
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Fallback URL -->
              <p style="margin:0 0 6px;color:#6b7280;font-size:13px;line-height:1.5;">
                If the button above doesn&#8217;t work, copy and paste this link into your browser:
              </p>
              <p style="margin:0 0 28px;word-break:break-all;">
                <a href="<?= $resetUrl ?>" style="color:#7431e8;font-size:13px;text-decoration:underline;"><?= $resetUrl ?></a>
              </p>

              <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">
                If you did not request a password reset, you can safely ignore this email.
                Your password will not be changed.
              </p>

            </td>
          </tr>

          <!-- ── Footer ── -->
          <tr>
            <td style="padding:20px 40px;border-top:1px solid #e5e7eb;background:#f9fafb;text-align:center;">
              <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.5;">
                &copy; <?= $year ?> Nexo &middot; The Nexo Team
              </p>
            </td>
          </tr>

        </table>
        <!-- /Card -->

      </td>
    </tr>
  </table>

</body>
</html>
