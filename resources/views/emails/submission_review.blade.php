<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $outcome === 'rejected' ? 'Submission Rejected' : 'Correction Required' }}</title>
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
                                @if($outcome === 'rejected')
                                    The submission for <strong>{{ $studentName }}</strong> in <strong>{{ $eventTitle }}</strong> has been rejected.
                                    <br>{{ $eventTitle }} માટે <strong>{{ $studentName }}</strong> ની એન્ટ્રી નામંજૂર કરવામાં આવી છે.
                                @else
                                    Please correct the submission for <strong>{{ $studentName }}</strong> in <strong>{{ $eventTitle }}</strong> and submit it again.
                                    <br>{{ $eventTitle }} માટે <strong>{{ $studentName }}</strong> ની એન્ટ્રીમાં સુધારો કરીને ફરીથી સબમિટ કરો.
                                @endif
                            </p>
                            <div style="background-color: {{ $outcome === 'rejected' ? '#fff1f2' : '#fffbeb' }}; border: 1px solid {{ $outcome === 'rejected' ? '#fecdd3' : '#fde68a' }}; border-radius: 12px; padding: 16px; margin: 0 0 16px 0;">
                                <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 800; text-transform: uppercase; color: {{ $outcome === 'rejected' ? '#be123c' : '#b45309' }};">Note from the office / કારણ</p>
                                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #1e293b;">{!! nl2br(e($note)) !!}</p>
                            </div>
                            @if($outcome !== 'rejected')
                                <p style="margin: 0 0 8px 0;">
                                    <a href="{{ $editUrl }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 10px 18px; border-radius: 10px;">Correct the submission / સુધારો કરો</a>
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #64748b;">Open <em>Submitted Registrations</em>, then tap <strong>Edit</strong> on this student.</p>
                            @endif
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
