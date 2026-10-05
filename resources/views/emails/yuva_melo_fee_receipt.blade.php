@php $fd = $registration->form_data ?? []; @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yuva Melo Form Fee Receipt</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="570" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #0f172a; padding: 28px 40px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 19px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Shree Satwara Gnati Mandal, Ahmedabad</h1>
                            <p style="margin: 6px 0 0 0; font-size: 12px; color: #94a3b8; font-weight: 600;">YUVA MELO FORM FEE RECEIPT</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                Thank you. We have received your Yuva Melo form fee for <strong>{{ $event->title }}</strong>.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #334155;">
                                @foreach([
                                    'Receipt No.' => $receiptNo,
                                    'Yuva Melo No.' => $registration->yuva_melo_number ? sprintf('%03d', $registration->yuva_melo_number) : null,
                                    'Candidate' => $fd['full_name'] ?? null,
                                    'Mobile' => $fd['mobile_no'] ?? null,
                                    'Amount Paid' => '₹' . number_format($amount, 2),
                                    'Payment ID' => $paymentId,
                                    'Date' => now()->format('d M, Y h:i A'),
                                ] as $label => $value)
                                    <tr>
                                        <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-weight: 700; width: 40%;">{{ $label }}</td>
                                        <td style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9;">{{ $value ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <p style="margin: 20px 0 0 0; font-size: 11px; line-height: 1.6; color: #64748b;">Please keep this email as proof of payment.</p>
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
