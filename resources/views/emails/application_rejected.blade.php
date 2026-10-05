<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Not Approved</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="570" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #0f172a; padding: 32px 40px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Shree Satwara Gnati Mandal, Ahmedabad</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 14px; color: #1e293b;">Dear {{ $recipientName }},</p>
                            <p style="margin: 0 0 16px 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                @if($kind === 'business')
                                    We have reviewed your business directory application for <strong>{{ $applicationName }}</strong> and are unable to approve it at this time.
                                @else
                                    We have reviewed your membership application and are unable to approve it at this time.
                                @endif
                            </p>
                            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 16px; margin: 0 0 16px 0;">
                                <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #be123c;">Reason / કારણ</p>
                                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #881337;">{!! nl2br(e($reason)) !!}</p>
                            </div>
                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                If you believe this is a mistake or you can provide the missing details, please contact the Mandal office
                                @if($contactEmail) at <a href="mailto:{{ $contactEmail }}" style="color: #2563eb;">{{ $contactEmail }}</a>@endif
                                @if($contactPhone) or call {{ $contactPhone }}@endif.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px 40px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 11px; font-weight: 600;">
                            &copy; {{ date('Y') }} Shree Satwara Gnati Mandal, Ahmedabad. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
