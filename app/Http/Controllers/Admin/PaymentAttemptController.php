<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAttempt;
use App\Models\Transaction;
use App\Services\RazorpayVerifier;
use Illuminate\Http\Request;

/**
 * Log of every Razorpay popup opened on the site, with a live Razorpay lookup to settle
 * "was this payment actually received?" (permission: transactions_view).
 */
class PaymentAttemptController extends Controller
{
    public function index(Request $request, RazorpayVerifier $razorpay)
    {
        $query = PaymentAttempt::query()->with('transaction');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('payer_name', 'like', "%{$search}%")
                    ->orWhere('payer_contact', 'like', "%{$search}%")
                    ->orWhere('payer_email', 'like', "%{$search}%")
                    ->orWhereHas('transaction', fn ($t) => $t->where('transaction_no', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status') && array_key_exists($request->status, PaymentAttempt::STATUSES)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('purpose') && array_key_exists($request->purpose, Transaction::PURPOSES)) {
            $query->where('purpose', $request->purpose);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $attempts = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $statuses = PaymentAttempt::STATUSES;
        $purposes = Transaction::PURPOSES;
        $lookup = $request->filled('lookup') ? $this->lookup(trim($request->lookup), $razorpay) : null;

        return view('admin.payment_attempts.index', compact('attempts', 'statuses', 'purposes', 'lookup'));
    }

    /**
     * Ask Razorpay about a payment id (pay_…), an order id (order_…) or one of our TXN numbers.
     *
     * @return array{query: string, error: ?string, payments: array<int, array<string, mixed>>}
     */
    private function lookup(string $value, RazorpayVerifier $razorpay): array
    {
        $result = ['query' => $value, 'error' => null, 'payments' => []];

        if (!$razorpay->configured()) {
            $result['error'] = 'Razorpay keys are not configured, so payments cannot be checked.';
            return $result;
        }

        if (stripos($value, 'TXN-') === 0) {
            $txn = Transaction::where('transaction_no', strtoupper($value))->first();
            if (!$txn) {
                $result['error'] = "No transaction {$value} in our records.";
                return $result;
            }
            if (!$txn->payment_id) {
                $result['error'] = "{$txn->transaction_no} was paid at the office, there is nothing to check on Razorpay.";
                return $result;
            }
            $value = $txn->payment_id;
        }

        if (str_starts_with($value, 'order_')) {
            $payments = $razorpay->fetchOrderPayments($value);
            if ($payments === null) {
                $result['error'] = "Razorpay could not find order {$value}.";
                return $result;
            }
            if ($payments === []) {
                $result['error'] = "No payment was made against order {$value}.";
                return $result;
            }
        } else {
            $payment = $razorpay->fetchPayment($value);
            if ($payment === null) {
                $result['error'] = "Razorpay could not find payment {$value}. Payment IDs start with \"pay_\".";
                return $result;
            }
            $payments = [$payment];
        }

        foreach ($payments as $p) {
            $id = $p['id'] ?? '';
            $txn = $id !== '' ? Transaction::where('payment_id', $id)->first() : null;

            // Remember what Razorpay said on the logged attempts for this payment
            PaymentAttempt::where('razorpay_payment_id', $id)->update([
                'razorpay_status' => $p['status'] ?? null,
                'razorpay_checked_at' => now(),
            ]);

            $result['payments'][] = [
                'id' => $id,
                'status' => $p['status'] ?? '-',
                'amount' => ((int) ($p['amount'] ?? 0)) / 100,
                'method' => $p['method'] ?? '-',
                'email' => $p['email'] ?? null,
                'contact' => $p['contact'] ?? null,
                'order_id' => $p['order_id'] ?? null,
                'description' => $p['description'] ?? null,
                'error' => $p['error_description'] ?? null,
                'created_at' => !empty($p['created_at'])
                    ? \Carbon\Carbon::createFromTimestamp($p['created_at'], config('app.local_timezone'))->format('d M Y, h:i A')
                    : '-',
                'transaction' => $txn,
            ];
        }

        return $result;
    }
}
