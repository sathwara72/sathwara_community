<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('type_label') - {{ $receiptNo }}</title>
    <style>
        @page {
            margin: 18px 25px;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'NotoSansGujarati', sans-serif;
            color: #1e293b;
            font-size: 11.5px;
            line-height: 1.35;
        }
        .receipt-box {
            position: relative;
            border: 2px solid #1e3a8a;
            border-radius: 8px;
            padding: 14px 18px;
        }
        .watermark-bg {
            position: absolute;
            top: 20%;
            left: 12%;
            width: 76%;
            text-align: center;
            opacity: 0.03;
            z-index: -1;
        }
        .watermark-bg img {
            width: 380px;
            max-width: 85%;
            height: auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .meta-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            padding: 5px 12px 6px 12px;
            vertical-align: middle;
            line-height: 1.0;
        }
        .ack-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #b45309;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 11.5px;
            line-height: 1.5;
            color: #1e293b;
        }
        .ack-box strong {
            color: #991b1b;
        }
        .section-header {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4.5px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .info-table td.label {
            color: #64748b;
            font-weight: bold;
            width: 35%;
        }
        .info-table td.value {
            color: #0f172a;
            font-weight: bold;
        }
        .info-table td.value.accent {
            color: #1e3a8a;
            font-size: 13px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .payment-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 6px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            vertical-align: middle;
            line-height: 1.0;
        }
        .payment-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .total-row td {
            background-color: #f1f5f9;
            font-size: 12.5px;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            padding: 7px 10px;
            vertical-align: middle;
        }
        .txn-row td {
            font-size: 10px;
            color: #475569;
            padding: 6px 10px;
            vertical-align: middle;
        }
        .status-badge {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            font-size: 10px;
        }
        .status-badge.status-good {
            background-color: #dcfce7;
            color: #15803d;
        }
        .status-badge.status-pending {
            background-color: #fef9c3;
            color: #92400e;
        }
        .status-badge.status-bad {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    @php
        $logoSetting = \App\Models\Setting::get('website_logo');
        $logoPath = $logoSetting && file_exists(storage_path('app/public/' . $logoSetting))
            ? storage_path('app/public/' . $logoSetting)
            : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null);
        $logoBase64 = $logoPath ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $orgName = \App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad');
    @endphp

    <div class="receipt-box">
        @if($logoBase64)
        <div class="watermark-bg">
            <img src="{{ $logoBase64 }}" alt="Watermark">
        </div>
        @endif

        @php
            $orgAddress = \App\Models\Setting::get('contact_address', '1, Satwara Samaj Bhavan, Opp. Siddheswar Shopping, Viratnagar-Manmohan Road, Odhav, Ahmedabad - 382415');
            $orgPhone = \App\Models\Setting::get('contact_phone', '+91-6353785519');
            $cleanPhone = preg_replace('/[^0-9+]/', '', $orgPhone);
            $mapsUrl = 'https://maps.google.com/?q=' . urlencode($orgAddress);
        @endphp

        <!-- Header Table: Logo (Left) | Title 2-Line (Left) | Address & Mobile (Right) -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
            <tr>
                @if($logoBase64)
                <td style="width: 52px; vertical-align: middle; text-align: left;">
                    <img src="{{ $logoBase64 }}" style="max-height: 48px; max-width: 52px; object-fit: contain;" alt="Logo">
                </td>
                @endif
                <td style="vertical-align: middle; text-align: left; padding-left: 8px;">
                    <div style="font-size: 16px; font-weight: 900; color: #1e3a8a; text-transform: uppercase; line-height: 1.2; letter-spacing: 0.5px;">
                        SHREE SATWARA GNATI MANDAL,<br>AHMEDABAD
                    </div>
                </td>
                <td style="vertical-align: middle; text-align: right; font-size: 10.5px; color: #334155; line-height: 1.3; width: 45%;">
                    @if(!empty($orgAddress))
                        <div style="font-weight: bold; font-size: 10.5px;">
                            <a href="{{ $mapsUrl }}" target="_blank" style="color: #334155; text-decoration: none;">
                                {{ \App\Support\GujaratiText::reorderMatra($orgAddress) }}
                            </a>
                        </div>
                    @endif
                    @if(!empty($orgPhone))
                        <div style="font-weight: bold; color: #1e3a8a; font-size: 11px; margin-top: 2px;">
                            <a href="tel:{{ $cleanPhone }}" style="color: #1e3a8a; text-decoration: none;">
                                Mobile No: {{ $orgPhone }}
                            </a>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Centered Clean Receipt Title -->
        <div style="width: 100%; margin-top: 6px; margin-bottom: 8px; border-bottom: 2px solid #1e3a8a; padding-bottom: 4px; text-align: center;">
            <span style="font-size: 15px; font-weight: 900; color: #1e3a8a; text-transform: uppercase; letter-spacing: 1.5px;">
                RECEIPT
            </span>
        </div>

        <!-- Receipt Meta Details (Equal Top/Bottom Visual Padding) -->
        <table class="meta-table" style="line-height: 1.0;">
            <tr>
                <td style="width: 50%; text-align: left; padding: 5px 12px 6px 12px; vertical-align: middle; line-height: 1.0; font-size: 12px;">
                    <strong style="color: #475569;">Receipt Number:</strong> <span style="font-size: 12.5px; font-weight: bold; color: #0f172a;">{{ $receiptNo }}</span>
                </td>
                <td style="width: 50%; text-align: right; padding: 5px 12px 6px 12px; vertical-align: middle; line-height: 1.0; font-size: 12px;">
                    <strong style="color: #475569;">Date &amp; Time:</strong> <span style="font-size: 12.5px; font-weight: bold; color: #0f172a;">{{ date('d M Y, h:i A') }}</span>
                </td>
            </tr>
        </table>

        @yield('content')
    </div>
</body>
</html>
