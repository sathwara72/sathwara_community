<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventSponsor;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingMembers = User::onlyMembers()->where('status', 'pending')->count();
        $pendingBusinesses = Business::where('status', 'pending')->count();
        $pendingSponsors = EventSponsor::where('status', 'pending')->count();

        $membershipRevenue = (float) User::where('payment_status', 'paid')->sum('payment_amount');
        $businessRevenue = (float) Business::where('payment_status', 'paid')->sum('payment_amount');
        $eventRevenue = (float) EventRegistration::where('payment_status', 'paid')->sum('payment_amount');
        $sponsorshipRevenue = (float) EventSponsor::where('payment_status', 'received')->sum('amount');

        $totalPassesSold = (int) EventRegistration::passes()
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'approved');
            })
            ->sum(DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.person_count')), ''), 1)"));

        $stats = [
            'total_members' => User::onlyMembers()->where('status', 'approved')->count(),
            'pending_members' => $pendingMembers,
            'total_businesses' => Business::count(),
            'pending_businesses' => $pendingBusinesses,
            'total_events' => Event::count(),
            'passes_sold' => $totalPassesSold,
            'total_revenue' => $membershipRevenue + $businessRevenue + $eventRevenue + $sponsorshipRevenue,
            'sponsorship_revenue' => $sponsorshipRevenue,
            'pending_approvals' => $pendingMembers + $pendingBusinesses + $pendingSponsors,
        ];

        $latestMembers = User::onlyMembers()
            ->with('memberProfile')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $latestBusinesses = Business::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Last 6 months, oldest to newest
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $monthLabels = $months->map(fn ($m) => $m->format('M Y'))->values();

        $memberTrend = $months->map(function ($month) {
            return User::onlyMembers()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        })->values();

        $businessTrend = $months->map(function ($month) {
            return Business::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        })->values();

        $passTrend = $months->map(function ($month) {
            return (int) EventRegistration::passes()
                ->where(function ($q) {
                    $q->where('payment_status', 'paid')
                      ->orWhere('status', 'approved');
                })
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum(DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.person_count')), ''), 1)"));
        })->values();

        // Passes sold by event (top 6 events)
        $passesByEvent = EventRegistration::passes()
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhere('status', 'approved');
            })
            ->selectRaw("event_id, CAST(SUM(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.person_count')), ''), 1)) AS UNSIGNED) as total")
            ->whereNotNull('event_id')
            ->groupBy('event_id')
            ->orderByDesc('total')
            ->with('event:id,title')
            ->take(6)
            ->get();

        $eventLabels = $passesByEvent->map(fn ($r) => $r->event->title ?? '—')->values();
        $eventValues = $passesByEvent->pluck('total')->values();

        $revenueBreakdown = [
            'membership' => $membershipRevenue,
            'business' => $businessRevenue,
            'events' => $eventRevenue,
            'sponsorship' => $sponsorshipRevenue,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'latestMembers',
            'latestBusinesses',
            'monthLabels',
            'memberTrend',
            'businessTrend',
            'passTrend',
            'eventLabels',
            'eventValues',
            'revenueBreakdown'
        ));
    }
}
