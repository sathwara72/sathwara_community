@extends('emails.receipt_pdf._base')

@section('type_label', 'Sponsorship Receipt')

@section('content')
    @php
        $eventDate = $event->date ?? ($event->event_date ?? null);
        $eventTime = $event->time ?? ($event->start_time ?? null);
        $packageTitle = $sponsorshipType ? $sponsorshipType->title : 'General Event Sponsorship';
        $formattedEventTitle = \App\Support\GujaratiText::reorderMatra($event->title ?? '');
        $formattedPackageTitle = \App\Support\GujaratiText::reorderMatra($packageTitle);
        $formattedSponsorName = \App\Support\GujaratiText::reorderMatra($sponsor->name ?? '');
        $formattedContactPerson = !empty($sponsor->contact_person) ? \App\Support\GujaratiText::reorderMatra($sponsor->contact_person) : '';
        $sponsorAddress = !empty($sponsor->address) ? $sponsor->address : ($sponsor->city ?? '');
        $formattedAddress = !empty($sponsorAddress) ? \App\Support\GujaratiText::reorderMatra($sponsorAddress) : '';
        $formattedVenue = \App\Support\GujaratiText::reorderMatra($event->venue ?? '');
        $amountInWords = \App\Support\NumberToWords::convert($amount);
    @endphp

    <!-- Acknowledgement Box -->
    <div class="ack-box">
        Shree <strong>{{ $formattedSponsorName }}</strong>
        @if(!empty($formattedContactPerson))
            (Contact Person: <strong>{{ $formattedContactPerson }}</strong>),
        @endif
        @if(!empty($formattedAddress))
            Address <strong>{{ $formattedAddress }}</strong>,
        @endif
        @if(!empty($sponsor->mobile))
            Mobile <strong>{{ $sponsor->mobile }}</strong>,
        @endif
        we have received a sum of Rupees <strong>{{ number_format($amount, 2) }} ({{ $amountInWords }} INR)</strong> as per the details below:
    </div>

    <!-- Event Details -->
    <div class="section-header">Event Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Event Title:</td>
            <td class="value accent">{{ $formattedEventTitle }}</td>
        </tr>
        <tr>
            <td class="label">Event Date &amp; Time:</td>
            <td class="value">
                {{ $eventDate ? date('d M Y', strtotime($eventDate)) : '-' }}
                @if($eventTime) | {{ date('h:i A', strtotime($eventTime)) }} @endif
            </td>
        </tr>
        @if(!empty($event->venue))
        <tr>
            <td class="label">Venue:</td>
            <td class="value">{{ $formattedVenue }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Sponsorship Category:</td>
            <td class="value">{{ $formattedPackageTitle }}</td>
        </tr>
    </table>

    <!-- Sponsor Details -->
    <div class="section-header">Sponsor Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Organization / Sponsor:</td>
            <td class="value">{{ $formattedSponsorName }}</td>
        </tr>
        @if(!empty($sponsor->contact_person))
        <tr>
            <td class="label">Contact Person:</td>
            <td class="value">{{ \App\Support\GujaratiText::reorderMatra($sponsor->contact_person) }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Phone / Mobile:</td>
            <td class="value">{{ $sponsor->mobile }}</td>
        </tr>
        @if(!empty($sponsor->email))
        <tr>
            <td class="label">Email Address:</td>
            <td class="value">{{ $sponsor->email }}</td>
        </tr>
        @endif
        @if(!empty($sponsor->city))
        <tr>
            <td class="label">City / Location:</td>
            <td class="value">{{ \App\Support\GujaratiText::reorderMatra($sponsor->city) }}</td>
        </tr>
        @endif
    </table>

    <!-- Payment Breakdown -->
    <div class="section-header">Contribution Summary</div>
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
                    <strong>{{ $formattedEventTitle }} &mdash; Event Sponsorship Contribution</strong><br>
                    <span style="font-size: 10px; color: #64748b;">Package: {{ $formattedPackageTitle }}</span>
                </td>
                <td style="text-align: center;">
                    {{ !empty($paymentId) ? 'Razorpay Online' : 'Offline / Cash' }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    Rs. {{ number_format($amount, 2) }}
                </td>
            </tr>
            @include('emails.receipt_pdf._txn_status')
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Contribution:</td>
                <td style="text-align: right; color: #1e3a8a;">Rs. {{ number_format($amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
