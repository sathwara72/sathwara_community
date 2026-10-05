<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Business Membership Expired</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="570" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #0f172a; padding: 28px 40px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 19px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Shree Satwara Gnati Mandal, Ahmedabad</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 14px 0; font-size: 14px; color: #1e293b;">Dear {{ $business->owner_name ?: $business->business_name }},</p>
                            <p style="margin: 0 0 14px 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                The 1-year business directory membership of <strong>{{ $business->business_name }}</strong> ended on
                                <strong>{{ $business->approved_at->copy()->addYear()->format('d M, Y') }}</strong>. Your listing is now hidden from the public directory.
                            </p>
                            <p style="margin: 0 0 22px 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                Renew for <strong>₹{{ number_format($fee, 2) }}</strong> to make it visible again for the next year.
                            </p>
                            <table border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" style="border-radius: 10px; background-color: #dc2626;">
                                        <a href="{{ route('business.renewal') }}" style="display: inline-block; padding: 12px 28px; font-size: 14px; font-weight: 800; color: #ffffff; text-decoration: none;">Renew Membership</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 22px 0 0 0; font-size: 11px; line-height: 1.6; color: #64748b; text-align: center;">
                                Log in to your Business Panel with {{ $business->email }} to renew.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px 40px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 11px; font-weight: 600;">
                            &copy; {{ date('Y') }} Shree Satwara Gnati Mandal, Ahmedabad. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
