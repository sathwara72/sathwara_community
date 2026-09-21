@extends('layouts.business')

@section('title', __('Renewal & Invoices'))
@section('page-title', __('Renewal & Invoices'))

@push('styles')
    <style>
        .renewal-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .renewal-stat {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 20px 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .renewal-stat .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .renewal-stat .value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
        }

        .renewal-stat .sub {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table th,
        .payment-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
        }

        .payment-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .payment-table tr:hover td {
            background: #f8fafc;
        }

        .payment-table td:last-child {
            text-align: right;
        }

        .renewal-cta {
            background: linear-gradient(135deg, var(--primary, #ef4444), #991b1b);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--primary, #ef4444) 30%, transparent);
        }

        .renewal-cta h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .renewal-cta p {
            font-size: 13.5px;
            opacity: .9;
        }

        .renewal-cta .cta-btn {
            padding: 12px 28px;
            background: rgba(255, 255, 255, .2);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, .4);
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            backdrop-filter: blur(8px);
            transition: all .2s;
            white-space: nowrap;
        }

        .renewal-cta .cta-btn:hover {
            background: #fff;
            color: var(--primary, #ef4444);
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 14px;
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 14px;
        }
    </style>
@endpush

@section('content')
    @php
        $isActive = $business->status === 'approved' && $business->membership_status === 'active';
        $renewalDue = $business->isRenewalDue();
        $approvedAt = $business->approved_at;
        $expiresAt = $approvedAt ? $approvedAt->copy()->addYear() : null;
    @endphp

    {{-- Renewal Stats --}}
    <div class="renewal-summary">
        <div class="renewal-stat">
            <div class="label">{{ __('Listing Status') }}</div>
            <div class="value">
                @if($isActive)
                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> {{ __('Active') }}</span>
                @elseif($business->status === 'pending')
                    <span class="badge badge-warning"><i class="fas fa-clock"></i> {{ __('Pending') }}</span>
                @else
                    <span class="badge badge-danger"><i class="fas fa-ban"></i> {{ __('Inactive') }}</span>
                @endif
            </div>
        </div>
        <div class="renewal-stat">
            <div class="label">{{ __('Approved On') }}</div>
            <div class="value">{{ $approvedAt ? $approvedAt->format('d M, Y') : '—' }}</div>
        </div>
        <div class="renewal-stat">
            <div class="label">{{ __('Expiry Date') }}</div>
            <div class="value">
                {{ $expiresAt ? $expiresAt->format('d M, Y') : '—' }}
                @if($renewalDue)
                    <span class="badge badge-danger" style="margin-left:6px; font-size:11px;">{{ __('Expired') }}</span>
                @endif
            </div>
        </div>
        <div class="renewal-stat">
            <div class="label">{{ __('Renewal Status') }}</div>
            <div class="value">
                @if($renewalDue)
                    <span class="badge badge-danger">{{ __('Renewal Required') }}</span>
                @elseif($isActive)
                    <span class="badge badge-success">{{ __('Up to Date') }}</span>
                @else
                    <span class="badge badge-secondary">{{ __('N/A') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- CTA if renewal due --}}
    @if($renewalDue)
        <div class="renewal-cta" style="background: linear-gradient(135deg, var(--primary-hex, #ef4444), #7f1d1d); border-radius: 16px; padding: 28px 32px; color: #fff; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--primary-hex, #ef4444) 30%, transparent);">
            <div style="max-width: 560px;">
                <div style="display:inline-flex; align-items:center; gap:8px; padding:4px 12px; background:rgba(255,255,255,0.2); border-radius:999px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px;">
                    <i class="fas fa-exclamation-triangle"></i> {{ __('Renewal Required') }}
                </div>
                <h3 style="font-size:22px; font-weight:800; margin-bottom:8px; color:#fff;">{{ __('Renew Business Membership') }}</h3>
                <p style="font-size:13.5px; opacity:0.95; line-height:1.6; margin-bottom:14px;">
                    {{ __('Your business membership has expired. Renew now to keep your business profile active and visible in the public directory.') }}
                </p>
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="font-size:28px; font-weight:800; color:#fff;">₹{{ number_format($renewalFee, 2) }}</span>
                    <span style="font-size:13px; opacity:0.85; font-weight:600;">/ {{ __('Valid for 1 Year') }}</span>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px; align-items:stretch; min-width: 220px;">
                {{-- Online Pay & Renew Button --}}
                <button type="button" id="payOnlineBtn" onclick="initiateRazorpayRenewal()" class="cta-btn" style="background:#fff; color:var(--primary-hex, #ef4444); display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 14px rgba(0,0,0,0.18); font-size:14px; font-weight:800; padding:13px 24px; border:none;">
                    <i class="fas fa-bolt"></i> {{ __('Pay & Renew Now') }}
                </button>

                {{-- Generate Payment Link --}}
                <form method="POST" action="{{ route('business.renewal.generateLink') }}">
                    @csrf
                    <button type="submit" class="cta-btn" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px; font-size:12px; padding:10px 18px; border: 1.5px solid rgba(255,255,255,0.4); background: rgba(255,255,255,0.15);">
                        <i class="fas fa-link"></i> {{ __('Generate Payment Link') }}
                    </button>
                </form>

                {{-- Contact Admin --}}
                <a href="mailto:{{ config('mail.from.address', 'admin@sathwaracommunity.com') }}" class="cta-btn" style="border:none; font-size:11px; padding:6px 12px; text-align:center; opacity:0.85; text-decoration:underline;">
                    <i class="fas fa-envelope"></i> {{ __('Contact Admin') }}
                </a>
            </div>
        </div>

        {{-- Hidden Form for Razorpay Callback Submission --}}
        <form id="razorpayRenewalForm" method="POST" action="{{ route('business.renewal.pay') }}" style="display:none;">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        </form>
    @else
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:16px; padding:18px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:40px; height:40px; border-radius:12px; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:18px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h5 style="font-size:14px; font-weight:800; color:#14532d; margin-bottom:2px;">{{ __('Membership Active') }}</h5>
                    <p style="font-size:12px; color:#166534; margin:0;">
                        {{ __('Your business listing is active until') }} <strong>{{ $expiresAt ? $expiresAt->format('d M, Y') : '—' }}</strong>.
                    </p>
                </div>
            </div>
            <span class="badge badge-success" style="padding:6px 14px; font-size:12px; font-weight:700;">
                <i class="fas fa-check-circle"></i> {{ __('Active (No renewal required)') }}
            </span>
        </div>
    @endif

    {{-- Payment Links / Renewal History --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-file-invoice-dollar" style="color:var(--primary, #ef4444);"></i>
            <h5>{{ __('Renewal Payment Links & History') }}</h5>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($business->paymentLinks && $business->paymentLinks->count())
                <div style="overflow-x:auto;">
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Sent On') }}</th>
                                <th>{{ __('Paid On') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($business->paymentLinks as $i => $link)
                                <tr>
                                    <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                                    <td><strong>₹{{ number_format($link->amount, 2) }}</strong></td>
                                    <td>
                                        @if($link->status === 'paid')
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> {{ __('Paid') }}</span>
                                        @elseif($link->isExpired())
                                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> {{ __('Expired') }}</span>
                                        @elseif($link->status === 'created')
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> {{ __('Pending') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __($link->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $link->created_at->format('d M, Y') }}</td>
                                    <td>{{ $link->paid_at ? $link->paid_at->format('d M, Y') : '—' }}</td>
                                    <td>
                                        @if($link->status === 'paid')
                                            <span style="color:#10b981; font-size:13px; font-weight:700;">
                                                <i class="fas fa-check-circle"></i> {{ __('Completed') }}
                                            </span>
                                        @elseif($link->status === 'created' && $link->razorpay_link_url && !$link->isExpired())
                                            <a href="{{ $link->razorpay_link_url }}" target="_blank"
                                                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--primary, #ef4444);color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;box-shadow:0 2px 6px color-mix(in srgb, var(--primary, #ef4444) 25%, transparent);">
                                                <i class="fas fa-credit-card"></i> {{ __('Pay Now') }} ↗
                                            </a>
                                        @elseif($link->isExpired())
                                            <span style="color:#ef4444; font-size:12px; font-weight:600;">{{ __('Expired') }}</span>
                                        @else
                                            <span style="color:#cbd5e1; font-size:13px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <p>{{ __('No renewal payment links found.') }}<br>
                        {{ __('Renewal links are sent by the admin when your membership is due for renewal.') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Registration Payment Summary --}}
    <div class="card" style="margin-top:12px;">
        <div class="card-header">
            <i class="fas fa-receipt" style="color:var(--primary, #ef4444);"></i>
            <h5>{{ __('Registration Payment') }}</h5>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="payment-table">
                <tbody>
                    <tr>
                        <td>{{ __('Registration Fee') }}</td>
                        <td style="text-align:right; font-weight:600;">
                            ₹{{ number_format($business->payment_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Payment Status') }}</td>
                        <td style="text-align:right;">
                            @if($business->payment_status === 'paid')
                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> {{ __('Paid') }}</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> {{ __('Unpaid') }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($business->payment_id)
                        <tr>
                            <td>{{ __('Payment ID') }}</td>
                            <td style="text-align:right; font-size:12px; color:#64748b;">{{ $business->payment_id }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    function initiateRazorpayRenewal() {
        const btn = document.getElementById('payOnlineBtn');
        const originalText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Opening payment...") }}';
        }

        const razorpayKey = "{{ $razorpayKeyId ?? '' }}";
        const feeAmountPaise = Math.round({{ (float) $renewalFee }} * 100);
        const businessName = "{{ addslashes($business->business_name) }}";
        const ownerName = "{{ addslashes($business->owner_name ?? '') }}";
        const email = "{{ addslashes($business->email ?? '') }}";
        const phone = "{{ addslashes($business->phone ?? '') }}";

        const options = {
            "key": razorpayKey || "rzp_test_key",
            "amount": feeAmountPaise,
            "currency": "INR",
            "name": "{{ config('app.name', 'Sathwara Community') }}",
            "description": "Business Renewal - " + businessName,
            "handler": function (response) {
                document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpayRenewalForm').submit();
            },
            "modal": {
                "ondismiss": function () {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                }
            },
            "prefill": {
                "name": ownerName,
                "email": email,
                "contact": phone
            },
            "theme": { "color": "{{ App\Models\Setting::get('primary_color', '#ef4444') }}" }
        };

        if (window.Razorpay) {
            const rzp = new Razorpay(options);
            rzp.open();
        } else {
            alert('Razorpay gateway could not be loaded. Please refresh the page or use "Generate Payment Link".');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }
</script>
@endpush