<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Receives the Razorpay popup log from the browser (public/js/payment-log.js).
 * Each call carries the whole attempt, so calls arriving out of order still end up right.
 */
class PaymentAttemptController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{16,40}$/'],
            'status' => ['required', 'in:' . implode(',', PaymentAttempt::CLIENT_STATUSES)],
            'purpose' => ['nullable', 'in:' . implode(',', array_keys(Transaction::PURPOSES))],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:255'],
            'payment_id' => ['nullable', 'string', 'max:64'],
            'order_id' => ['nullable', 'string', 'max:64'],
            'error' => ['nullable', 'string', 'max:1000'],
        ]);

        $attempt = PaymentAttempt::firstOrNew(['attempt_key' => $data['key']]);

        if (!$attempt->applyClientStatus($data['status'])) {
            return response()->json(['success' => true]);
        }

        if (!$attempt->exists) {
            $attempt->fill([
                'purpose' => $data['purpose'] ?? null,
                'description' => $data['description'] ?? null,
                'amount' => (float) ($data['amount'] ?? 0),
                'payer_name' => $data['name'] ?? null,
                'payer_contact' => $data['contact'] ?? null,
                'payer_email' => $data['email'] ?? null,
                'user_id' => auth('web')->id(),
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ]);
        }

        $attempt->status = $data['status'];
        $attempt->razorpay_payment_id = $data['payment_id'] ?? $attempt->razorpay_payment_id;
        $attempt->razorpay_order_id = $data['order_id'] ?? $attempt->razorpay_order_id;
        if ($data['status'] === 'failed') {
            $attempt->error = $data['error'] ?? null;
        } elseif ($data['status'] === 'paid') {
            $attempt->error = null;
        }
        $attempt->save();

        return response()->json(['success' => true]);
    }
}
