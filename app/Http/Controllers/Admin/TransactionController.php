<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\EventRegistration;
use App\Models\EventSponsor;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Read-only ledger of every successful payment (permission: transactions_view).
 */
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request);

        $total = (clone $query)->sum('amount');
        $count = (clone $query)->count();
        $transactions = $query->with(['payable', 'recorder'])->orderByDesc('id')->paginate(25)->withQueryString();
        $purposes = Transaction::PURPOSES;

        return view('admin.transactions.index', compact('transactions', 'purposes', 'total', 'count'));
    }

    public function exportCsv(Request $request)
    {
        $transactions = $this->filtered($request)->with(['payable', 'recorder'])->orderBy('id')->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=transactions_' . date('Y-m-d') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Sr. No.', 'Transaction ID', 'Date', 'For', 'Payer', 'Contact', 'Amount (INR)', 'Paid Via', 'Razorpay Payment ID', 'Linked Record', 'Recorded By']);

            $sr = 0;
            foreach ($transactions as $t) {
                fputcsv($file, [
                    ++$sr,
                    $t->transaction_no,
                    $t->created_at?->format('d-M-Y h:i A'),
                    $t->purpose_label,
                    $t->payer_name,
                    $t->payer_contact,
                    number_format((float) $t->amount, 2, '.', ''),
                    $t->method === 'office' ? 'Office' : 'Online (Razorpay)',
                    $t->payment_id ?? '',
                    self::recordLabel($t),
                    $t->recorder->name ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * "Member: Karan Sathwara", "Business: Sathwara Shop", "Pass: Garba Night #003" ...
     */
    public static function recordLabel(Transaction $t): string
    {
        $p = self::payableWithTrashed($t);
        $deleted = $p && method_exists($p, 'trashed') && $p->trashed() ? ' (deleted)' : '';

        return self::baseLabel($p) . $deleted;
    }

    /**
     * The paid-for record, including soft-deleted members / businesses.
     */
    private static function payableWithTrashed(Transaction $t)
    {
        if ($t->payable || !$t->payable_type || !class_exists($t->payable_type)) {
            return $t->payable;
        }

        $uses = class_uses_recursive($t->payable_type);

        return isset($uses[\Illuminate\Database\Eloquent\SoftDeletes::class])
            ? $t->payable_type::withTrashed()->find($t->payable_id)
            : null;
    }

    private static function baseLabel($p): string
    {

        return match (true) {
            $p instanceof User => 'Member: ' . $p->name,
            $p instanceof Business => 'Business: ' . $p->business_name,
            $p instanceof EventRegistration => ($p->event->title ?? 'Event') . ($p->pass_number ? ' — Pass #' . sprintf('%03d', $p->pass_number) : ($p->yuva_melo_number ? ' — Yuva #' . sprintf('%03d', $p->yuva_melo_number) : '')),
            $p instanceof EventSponsor => 'Sponsor: ' . $p->name,
            default => '—',
        };
    }

    /**
     * Admin page for the record a transaction paid for, when the viewer can open it.
     */
    public static function recordUrl(Transaction $t): ?string
    {
        $p = self::payableWithTrashed($t);

        return match (true) {
            $p instanceof User => route('admin.members.show', $p->id),
            $p instanceof Business => route('admin.businesses.show', $p->id),
            $p instanceof EventRegistration => route('admin.events.registrations', $p->event_id),
            $p instanceof EventSponsor => route('admin.events.show', ['event' => $p->event_id, 'tab' => 'sponsorship', 'subtab' => 'sponsors']),
            default => null,
        };
    }

    private function filtered(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhere('payer_name', 'like', "%{$search}%")
                    ->orWhere('payer_contact', 'like', "%{$search}%");
            });
        }
        if ($request->filled('purpose') && array_key_exists($request->purpose, Transaction::PURPOSES)) {
            $query->where('purpose', $request->purpose);
        }
        if (in_array($request->method_filter, ['razorpay', 'office'], true)) {
            $query->where('method', $request->method_filter);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query;
    }
}
