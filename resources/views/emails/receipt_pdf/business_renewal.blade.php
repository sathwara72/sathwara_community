@extends('emails.receipt_pdf._base')

@section('type_label', 'Business Renewal Receipt')

@section('content')
    @php
        $bizName = \App\Support\GujaratiText::reorderMatra($business->business_name ?? '');
        $ownerName = \App\Support\GujaratiText::reorderMatra($business->owner_name ?? '');
        $bizAddress = !empty($business->address) ? $business->address : ($business->area?->name ?? '');
        $formattedAddress = !empty($bizAddress) ? \App\Support\GujaratiText::reorderMatra($bizAddress) : '';
        $bizPhone = $business->phone ?? '';
        $amountInWords = \App\Support\NumberToWords::convert($amount);
    @endphp

    <!-- Acknowledgement Box -->
    <div class="ack-box">
        Shree <strong>{{ $bizName }}</strong>
        @if(!empty($ownerName))
            (Contact Person: <strong>{{ $ownerName }}</strong>),
        @endif
        @if(!empty($formattedAddress))
            Address <strong>{{ $formattedAddress }}</strong>,
        @endif
        @if(!empty($bizPhone))
            Mobile <strong>{{ $bizPhone }}</strong>,
        @endif
        we have received a sum of Rupees <strong>{{ number_format($amount, 2) }} ({{ $amountInWords }} INR)</strong> as per the details below:
    </div>

    <!-- Business Details -->
    <div class="section-header">Business Listing Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Business Name:</td>
            <td class="value accent">{{ $business->business_name }}</td>
        </tr>
        <tr>
            <td class="label">Category:</td>
            <td class="value">{{ $business->category ? $business->category->name : 'General Business' }}</td>
        </tr>
        <tr>
            <td class="label">Owner / Contact Person:</td>
            <td class="value">{{ $business->owner_name }}</td>
        </tr>
        <tr>
            <td class="label">Phone / WhatsApp:</td>
            <td class="value">{{ $business->phone }} {{ !empty($business->whatsapp) ? '/ ' . $business->whatsapp : '' }}</td>
        </tr>
        @if(!empty($business->email))
        <tr>
            <td class="label">Business Email:</td>
            <td class="value">{{ $business->email }}</td>
        </tr>
        @endif
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
                    <strong>Annual Business Directory Listing Renewal Fee</strong>
                </td>
                <td style="text-align: center;">Razorpay Online</td>
                <td style="text-align: right; font-weight: bold;">
                    Rs. {{ number_format($amount, 2) }}
                </td>
            </tr>
            @include('emails.receipt_pdf._txn_status')
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Paid Amount:</td>
                <td style="text-align: right; color: #1e3a8a;">Rs. {{ number_format($amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
