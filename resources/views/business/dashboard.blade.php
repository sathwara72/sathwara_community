@extends('layouts.business')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
    .stat-card {
        background: var(--card-bg); border-radius: 14px;
        border: 1px solid var(--border);
        padding: 22px 24px;
        display: flex; align-items: center; gap: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .stat-icon {
        width: 52px; height: 52px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .stat-icon.primary { background: color-mix(in srgb, var(--primary, #ef4444) 12%, transparent); color: var(--primary, #ef4444); }
    .stat-icon.green   { background: rgba(16,185,129,.12); color: #10b981; }
    .stat-icon.orange  { background: rgba(245,158,11,.12); color: #f59e0b; }
    .stat-icon.red     { background: rgba(239,68,68,.12); color: #ef4444; }
    .stat-meta h4 { font-size: 22px; font-weight: 800; color: var(--text); }
    .stat-meta p  { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-top: 2px; }

    .status-banner {
        border-radius: 14px; padding: 22px 28px;
        margin-bottom: 28px; display: flex; align-items: center; gap: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
    }
    .status-banner.approved { background: linear-gradient(135deg, #d1fae5, #a7f3d0); border: 1px solid #6ee7b7; }
    .status-banner.pending  { background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #fcd34d; }
    .status-banner.rejected { background: linear-gradient(135deg, #fee2e2, #fecaca); border: 1px solid #fca5a5; }
    .status-banner.inactive { background: linear-gradient(135deg, #f1f5f9, #e2e8f0); border: 1px solid #cbd5e1; }

    .status-banner .banner-icon { font-size: 32px; }
    .status-banner.approved .banner-icon { color: #059669; }
    .status-banner.pending  .banner-icon { color: #d97706; }
    .status-banner.rejected .banner-icon { color: #dc2626; }
    .status-banner.inactive .banner-icon { color: #64748b; }
    .status-banner .banner-text h3 { font-size: 17px; font-weight: 700; margin-bottom: 4px; }
    .status-banner.approved .banner-text h3 { color: #065f46; }
    .status-banner.pending  .banner-text h3 { color: #92400e; }
    .status-banner.rejected .banner-text h3 { color: #7f1d1d; }
    .status-banner.inactive .banner-text h3 { color: #1e293b; }
    .status-banner .banner-text p { font-size: 13.5px; }
    .status-banner.approved .banner-text p { color: #065f46; }
    .status-banner.pending  .banner-text p { color: #78350f; }
    .status-banner.rejected .banner-text p { color: #7f1d1d; }
    .status-banner.inactive .banner-text p { color: #475569; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media(max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

    .quick-action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; }
    .quick-action {
        display: flex; flex-direction: column; align-items: center; gap: 10px;
        padding: 20px 14px; border-radius: 12px;
        background: #f8fafc; border: 1px solid var(--border);
        text-decoration: none; color: var(--text);
        transition: all .2s; font-size: 13px; font-weight: 700; text-align: center;
    }
    .quick-action i { font-size: 22px; color: var(--primary, #ef4444); }
    .quick-action:hover { background: color-mix(in srgb, var(--primary, #ef4444) 6%, transparent); border-color: var(--primary, #ef4444); transform: translateY(-2px); box-shadow: 0 4px 12px color-mix(in srgb, var(--primary, #ef4444) 15%, transparent); }

    .info-row { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .info-row:last-child { border-bottom: none; }
    .info-row .info-label { font-size: 13px; color: var(--text-muted); min-width: 120px; flex-shrink: 0; }
    .info-row .info-val { font-size: 13.5px; font-weight: 500; flex: 1; }

    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .badge-secondary { background: #f1f5f9; color: #475569; }
</style>
@endpush

@section('content')
@php
    $status = $business->status;
    $membershipStatus = $business->membership_status;
    $isActive = ($status === 'approved' && $membershipStatus === 'active');
    $renewalDue = $business->isRenewalDue();
@endphp

{{-- Status Banner --}}
@if($isActive)
    <div class="status-banner approved">
        <i class="fas fa-check-circle banner-icon"></i>
        <div class="banner-text">
            <h3>Your listing is Live & Active</h3>
            <p>Your business is visible in the public directory.
                @if($business->approved_at)
                    Approved on {{ $business->approved_at->format('d M, Y') }}.
                    Expires on {{ $business->approved_at->addYear()->format('d M, Y') }}.
                @endif
            </p>
        </div>
    </div>
@elseif($status === 'pending')
    <div class="status-banner pending">
        <i class="fas fa-hourglass-half banner-icon"></i>
        <div class="banner-text">
            <h3>Pending Admin Approval</h3>
            <p>Your business registration is under review. You will be notified once approved.</p>
        </div>
    </div>
@elseif($status === 'rejected')
    <div class="status-banner rejected">
        <i class="fas fa-times-circle banner-icon"></i>
        <div class="banner-text">
            <h3>Listing Rejected</h3>
            <p>Your business listing has been rejected. Please contact the admin for more details.</p>
        </div>
    </div>
@else
    <div class="status-banner inactive">
        <i class="fas fa-ban banner-icon"></i>
        <div class="banner-text">
            <h3>Membership Inactive / Renewal Due</h3>
            <p>Your annual membership may have expired. Please renew to make your listing active again.</p>
        </div>
    </div>
@endif

@if($renewalDue)
    <div class="alert alert-warning" style="margin-bottom:24px;">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Renewal Required!</strong>
            Your business membership has expired. Please <a href="{{ route('business.renewal') }}" style="font-weight:600;">renew now</a> to restore your public listing.
        </div>
    </div>
@endif

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-store"></i></div>
        <div class="stat-meta">
            <h4>{{ ucfirst($status) }}</h4>
            <p>Listing Status</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $isActive ? 'green' : 'orange' }}">
            <i class="fas fa-{{ $isActive ? 'check-circle' : 'clock' }}"></i>
        </div>
        <div class="stat-meta">
            <h4>{{ ucfirst($membershipStatus ?? 'N/A') }}</h4>
            <p>Membership</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-receipt"></i></div>
        <div class="stat-meta">
            <h4>₹{{ number_format($business->payment_amount ?? 0) }}</h4>
            <p>Registration Fee Paid</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $renewalDue ? 'red' : 'green' }}"><i class="fas fa-sync-alt"></i></div>
        <div class="stat-meta">
            <h4>{{ $renewalDue ? 'Overdue' : 'Up to Date' }}</h4>
            <p>Renewal Status</p>
        </div>
    </div>
</div>

{{-- Quick Actions + Business Info --}}
<div class="two-col">
    {{-- Quick Actions --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bolt" style="color:var(--primary, #ef4444);"></i>
            <h5>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="quick-action-grid">
                <a href="{{ route('business.profile.edit') }}" class="quick-action">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                <a href="{{ route('business.renewal') }}" class="quick-action">
                    <i class="fas fa-sync-alt"></i> Renewals
                </a>
                <a href="{{ route('business.directory') }}" target="_blank" class="quick-action">
                    <i class="fas fa-globe"></i> Directory
                </a>
            </div>
        </div>
    </div>

    {{-- Business Info --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-info-circle" style="color:var(--primary, #ef4444);"></i>
            <h5>Business Information</h5>
        </div>
        <div class="card-body" style="padding:16px 24px;">
            <div class="info-row">
                <span class="info-label">Business Name</span>
                <span class="info-val">{{ $business->business_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Owner</span>
                <span class="info-val">{{ $business->owner_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Category</span>
                <span class="info-val">{{ $business->category->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone</span>
                <span class="info-val">{{ $business->phone }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-val">
                    {{ $business->email }}
                    @if($business->email_verified_at)
                        <span class="badge badge-success" style="margin-left:6px;"><i class="fas fa-check-circle"></i> Verified</span>
                    @endif
                </span>
            </div>
            @if($business->approved_at)
            <div class="info-row">
                <span class="info-label">Expiry Date</span>
                <span class="info-val">
                    {{ $business->approved_at->addYear()->format('d M, Y') }}
                    @if($renewalDue)
                        <span class="badge badge-danger" style="margin-left:6px;">Expired</span>
                    @endif
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
