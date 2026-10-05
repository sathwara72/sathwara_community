<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Received — Receipt Not Delivered</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="570" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <tr>
                        <td style="background-color: #be123c; padding: 24px 32px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 17px; font-weight: 800;">Payment received — receipt not delivered</h1>
                            <p style="margin: 6px 0 0 0; font-size: 12px; color: #fecdd3;">{{ $purpose }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 32px;">
                            <p style="margin: 0 0 16px 0; font-size: 13px; line-height: 1.6; color: #334155;">
                                A payment was received, but its receipt / pass email could not be sent to
                                <strong>{{ $intendedEmail ?: '(no email given)' }}</strong>. Please contact the person and share the receipt from the admin panel.
                            </p>
                            <p style="margin: 0 0 16px 0; font-size: 12px; color: #9f1239;"><strong>Problem:</strong> {{ $problem }}</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; font-size: 12px; color: #334155;">
                                @foreach($details as $label => $value)
                                    <tr>
                                        <td style="padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-weight: 700; width: 40%;">{{ $label }}</td>
                                        <td style="padding: 8px 12px; border-bottom: 1px solid #f1f5f9;">{{ $value ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
