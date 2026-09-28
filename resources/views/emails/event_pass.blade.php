<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="utf-8">
    <title>Event Pass & Payment Receipt - {{ $event->title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .receipt-badge {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 20px;
            margin-top: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .content {
            padding: 24px 20px;
        }
        .intro-text {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 20px;
            color: #334155;
        }
        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 20px;
            margin-bottom: 14px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .data-table tr td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table tr td.label {
            color: #64748b;
            font-weight: 600;
            width: 38%;
        }
        .data-table tr td.value {
            color: #0f172a;
            font-weight: 700;
        }
        .payment-box {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
        }
        .amount-row .amount {
            color: #2563eb;
            font-size: 18px;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-paid {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .status-unpaid {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .passes-list {
            margin: 20px 0;
        }
        /* Pass Card */
        .pass-card {
            border: 2px solid #0f172a;
            border-radius: 12px;
            background: #ffffff;
            margin-bottom: 16px;
            overflow: hidden;
            position: relative;
        }
        .pass-top-strip {
            background: #0f172a;
            color: #ffffff;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pass-main {
            padding: 14px;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }
        .pass-left-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: center;
            padding-right: 12px;
        }
        .pass-logo-circle {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            background: #f8fafc;
            display: inline-block;
            overflow: hidden;
            line-height: 65px;
            text-align: center;
        }
        .pass-logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .pass-middle-content {
            display: table-cell;
            vertical-align: top;
            padding-right: 10px;
        }
        .pass-mandal-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .pass-event-title {
            font-size: 14px;
            font-weight: 900;
            color: #e11d48;
            margin: 0 0 5px 0;
        }
        .pass-meta {
            font-size: 11px;
            color: #334155;
            margin: 2px 0;
            font-weight: 600;
        }
        .pass-right-box {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            text-align: right;
        }
        .pass-no-box {
            border: 2px solid #0f172a;
            border-radius: 8px;
            background: #f8fafc;
            padding: 6px 8px;
            text-align: center;
        }
        .pass-no-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .pass-no-value {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            display: block;
            margin-top: 2px;
            letter-spacing: 2px;
        }
        .pass-footer-location {
            border-top: 1.5px dashed #cbd5e1;
            padding: 7px 14px;
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }
        .footer-note {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    @php
        $attendeeName = $registration->form_data['full_name'] ?? ($user ? $user->name : 'Member');
        $memberId = $user ? sprintf('#%05d', $user->id) : ($registration->form_data['member_id'] ?? '-');
        $logoUrl = App\Models\Setting::get('website_logo') ? asset('storage/' . App\Models\Setting::get('website_logo')) : asset('logo.png');
        $receiptNoDisplay = $receiptNo ?? \App\Services\ReceiptNumberService::assign($registration, 'receipt_no');
        $finalAmount = $amount ?? (float)($registration->payment_amount ?? 0);
        $finalPaymentStatus = $paymentStatus ?? ($registration->payment_status ?? 'paid');
    @endphp

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}</h1>
            <p>ઇવેન્ટ પ્રવેશ પાસ અને પેમેન્ટ રસીદ</p>
            <div class="receipt-badge">Pass & Payment Receipt</div>
        </div>

        <div class="content">
            <!-- Receipt Meta Info -->
            <table style="width: 100%; background: #f1f5f9; border-radius: 10px; padding: 10px 14px; margin-bottom: 18px; font-size: 12px;">
                <tr>
                    <td style="width: 50%;">
                        <span style="font-size: 10px; color: #64748b; font-weight: 800; text-transform: uppercase; display: block;">Receipt No</span>
                        <strong style="color: #0f172a; font-size: 13px;">{{ $receiptNoDisplay }}</strong>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <span style="font-size: 10px; color: #64748b; font-weight: 800; text-transform: uppercase; display: block;">Date & Time</span>
                        <strong style="color: #0f172a; font-size: 12px;">{{ date('d M Y, h:i A') }}</strong>
                    </td>
                </tr>
            </table>

            <p class="intro-text">
                નમસ્તે <strong>{{ $attendeeName }}</strong>,<br>
                તમે <strong>{{ $event->title }}</strong> કાર્યક્રમ માટે સફળતાપૂર્વક <strong>{{ $personCount }} પ્રવેશ પાસ</strong> ખરીદેલ છે. આપની પેમેન્ટ રસીદ અને ડિજિટલ પ્રવેશ પાસ નીચે મુજબ છે:
            </p>

            <!-- Payment Summary -->
            <div class="section-title">Payment Summary (ચુકવણી સારાંશ)</div>
            <div class="payment-box">
                <table style="width: 100%; font-size: 13px;">
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Event Title</td>
                        <td style="font-weight: 700; text-align: right; color: #0f172a;">{{ $event->title }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Booked Passes / Persons</td>
                        <td style="font-weight: 700; text-align: right; color: #0f172a;">{{ $personCount }} Person(s)</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Payment Method</td>
                        <td style="font-weight: 700; text-align: right; color: #0f172a;">
                            {{ !empty($paymentId) ? 'Online (Razorpay)' : 'Offline / Cash' }}
                        </td>
                    </tr>
                    @if(!empty($paymentId))
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Payment ID</td>
                        <td style="font-weight: 700; text-align: right; font-family: monospace; color: #2563eb;">{{ $paymentId }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Payment Status</td>
                        <td style="text-align: right;">
                            <span class="status-pill {{ $finalPaymentStatus === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                                {{ strtoupper($finalPaymentStatus) }}
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="amount-row">
                    <span>Total Amount Paid:</span>
                    <span class="amount">₹ {{ number_format($finalAmount, 2) }}</span>
                </div>
            </div>

            <!-- Passes List Rendering (1 card per purchased pass) -->
            <div class="section-title">Digital Entry Passes (ડિજિટલ પ્રવેશ પાસ)</div>
            <div class="passes-list">
                @foreach($passes as $index => $passNo)
                    <div class="pass-card">
                        <!-- Top Strip -->
                        <div class="pass-top-strip">
                            <span>ENTRY PASS #{{ $index + 1 }}</span>
                            @php
                                $eventDate = $event->date ?? ($event->event_date ?? null);
                                $eventTime = $event->time ?? ($event->start_time ?? null);
                            @endphp
                            <span>{{ $eventDate ? date('d M Y', strtotime($eventDate)) : '' }}</span>
                        </div>

                        <!-- Main Pass Body -->
                        <div class="pass-main">
                            <!-- Left: Circular Logo -->
                            <div class="pass-left-logo">
                                <div class="pass-logo-circle">
                                    <img src="{{ $logoUrl }}" alt="Logo" onerror="this.style.display='none'">
                                </div>
                            </div>

                            <!-- Middle: Mandal Name, Event Name, Date -->
                            <div class="pass-middle-content">
                                <div class="pass-mandal-title">
                                    {{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}
                                </div>
                                <div class="pass-event-title">
                                    {{ $event->title }}
                                </div>
                                <div class="pass-meta">
                                    📅 <strong>Date:</strong> {{ $eventDate ? date('d-M-Y', strtotime($eventDate)) : '-' }} 
                                    @if($eventTime) | ⏰ {{ date('h:i A', strtotime($eventTime)) }} @endif
                                </div>
                                <div class="pass-meta">
                                    👤 <strong>Attendee:</strong> {{ $attendeeName }}
                                </div>
                            </div>

                            <!-- Right Box: Dedicated Pass No. -->
                            <div class="pass-right-box">
                                <div class="pass-no-box">
                                    <span class="pass-no-label">Pass No.</span>
                                    <span class="pass-no-value">{{ $passNo }}</span>
                                </div>
                                @if(!empty($qrUrls[$index]))
                                    <img src="{{ $qrUrls[$index] }}" alt="Entry QR" width="90" height="90" style="display: block; margin: 8px auto 0;">
                                @endif
                            </div>
                        </div>

                        <!-- Bottom Location Strip -->
                        <div class="pass-footer-location">
                            📍 <strong>Venue:</strong> {{ $event->venue ?? 'Community Hall' }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="footer-note">
                <p style="margin: 0 0 5px 0;">આ રસીદ અને પાસ તમારા મોબાઇલમાં સાચવી રાખો અથવા પ્રિન્ટ કરીને ઇવેન્ટ સ્થળે સાથે લાવો.</p>
                <p style="margin: 0; font-size: 11px; color: #94a3b8;">&copy; {{ date('Y') }} {{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
