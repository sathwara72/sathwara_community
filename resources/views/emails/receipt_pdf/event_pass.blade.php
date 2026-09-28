@extends('emails.receipt_pdf._base')

@section('type_label', 'Event Pass Receipt')

@section('content')
    @php
        $attendeeName = $registration->form_data['full_name'] ?? ($user ? $user->name : 'Member');
        $finalAmount = $amount ?? (float)($registration->payment_amount ?? 0);
        $eventDate = $event->date ?? ($event->event_date ?? null);
        $eventTime = $event->time ?? ($event->start_time ?? null);
        $formattedTitle = \App\Support\GujaratiText::reorderMatra($event->title ?? '');
        $formattedVenue = \App\Support\GujaratiText::reorderMatra($event->venue ?? 'Community Hall');
        $formattedAttendee = \App\Support\GujaratiText::reorderMatra($attendeeName);
        $passUser = $registration->user ?? $user;
        $attendeePhone = $registration->form_data['mobile'] ?? ($passUser?->phone ?? ($passUser?->memberProfile?->phone ?? ''));
        $amountInWords = \App\Support\NumberToWords::convert($finalAmount);
    @endphp

    <!-- Acknowledgement Box -->
    <div class="ack-box">
        Shree <strong>{{ $formattedAttendee }}</strong>
        @if(!empty($attendeePhone))
            , Mobile <strong>{{ $attendeePhone }}</strong>
        @endif
        , we have received a sum of Rupees <strong>{{ number_format($finalAmount, 2) }} ({{ $amountInWords }} INR)</strong> as per the details below:
    </div>

    <!-- Event Details -->
    <div class="section-header">Event Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Event Title:</td>
            <td class="value accent">{{ $formattedTitle }}</td>
        </tr>
        <tr>
            <td class="label">Event Date &amp; Time:</td>
            <td class="value">
                {{ $eventDate ? date('d M Y', strtotime($eventDate)) : '-' }}
                @if($eventTime) | {{ date('h:i A', strtotime($eventTime)) }} @endif
            </td>
        </tr>
        <tr>
            <td class="label">Venue / Location:</td>
            <td class="value">{{ $formattedVenue }}</td>
        </tr>
        <tr>
            <td class="label">Primary Attendee:</td>
            <td class="value">{{ $formattedAttendee }}</td>
        </tr>
    </table>

    <!-- Payment Breakdown -->
    <div class="section-header">Payment Breakdown</div>
    <table class="payment-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 25%; text-align: center;">Payment Mode</th>
                <th style="width: 25%; text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $formattedTitle }} &mdash; Event Entry Pass Fee</strong><br>
                    <span style="font-size: 10px; color: #64748b;">For {{ $personCount }} Person(s)</span>
                </td>
                <td style="text-align: center;">
                    {{ !empty($paymentId) ? 'Razorpay Online' : 'Offline / Cash' }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    Rs. {{ number_format($finalAmount, 2) }}
                </td>
            </tr>
            @include('emails.receipt_pdf._txn_status', ['paymentStatus' => $paymentStatus ?? ($registration->payment_status ?? 'paid')])
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Paid Amount:</td>
                <td style="text-align: right; color: #1e3a8a;">Rs. {{ number_format($finalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
