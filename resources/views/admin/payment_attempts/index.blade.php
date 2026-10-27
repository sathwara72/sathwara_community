@extends('layouts.admin')

@section('page_title', 'Payment Attempts')

@section('content')
    @php
        $tz = config('app.local_timezone');
        $statusColors = [
            'opened' => 'bg-slate-100 text-slate-600 border-slate-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
            'paid' => 'bg-amber-50 text-amber-700 border-amber-200',
            'verified' => 'bg-amber-50 text-amber-700 border-amber-200',
            'verification_failed' => 'bg-rose-50 text-rose-700 border-rose-200',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ];
        $rzpColors = [
            'captured' => 'text-emerald-700',
            'authorized' => 'text-amber-700',
            'refunded' => 'text-blue-700',
            'failed' => 'text-rose-700',
        ];
    @endphp
    <div class="space-y-4">
        <!-- Check a payment on Razorpay -->
        <form method="GET" action="{{ route('admin.payment_attempts.index') }}" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm space-y-2">
            <div>
                <h3 class="text-xs font-black text-slate-900">Check a payment on Razorpay</h3>
                <p class="text-[11px] text-slate-500 font-medium">Paste a Razorpay payment ID (pay_…), order ID (order_…) or our transaction number (TXN-…) to see whether the money was received and whether it is saved in our records.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <input type="text" name="lookup" value="{{ request('lookup') }}" placeholder="pay_XXXXXXXX / order_XXXXXXXX / TXN-000123"
                    class="flex-grow min-w-[220px] text-xs font-mono font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-bold text-xs rounded-xl">Check on Razorpay</button>
            </div>

            @if($lookup)
                @if($lookup['error'])
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">{{ $lookup['error'] }}</div>
                @endif
                @foreach($lookup['payments'] as $p)
                    @php
                        $captured = $p['status'] === 'captured';
                        $box = $captured
                            ? ($p['transaction'] ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-300')
                            : 'bg-slate-50 border-slate-200';
                    @endphp
                    <div class="p-3 rounded-xl border {{ $box }} space-y-2">
                        <div class="text-xs font-black">
                            @if($captured && $p['transaction'])
                                <span class="text-emerald-800">✓ Payment received and saved as {{ $p['transaction']->transaction_no }} ({{ $p['transaction']->purpose_label }}).</span>
                            @elseif($captured)
                                <span class="text-amber-800">⚠ Payment received by Razorpay, but NOT saved in our records. Contact the payer and add the entry manually.</span>
                            @elseif($p['status'] === 'authorized')
                                <span class="text-amber-800">Payment authorized but not captured. Razorpay refunds it to the payer automatically unless it is captured.</span>
                            @elseif($p['status'] === 'refunded')
                                <span class="text-blue-800">Payment was refunded to the payer.</span>
                            @else
                                <span class="text-rose-800">No money received: Razorpay status is "{{ $p['status'] }}".</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-[11px] font-semibold text-slate-700">
                            <div><span class="text-slate-400 block text-[10px] uppercase font-black">Payment ID</span><span class="font-mono break-all">{{ $p['id'] }}</span></div>
                            <div><span class="text-slate-400 block text-[10px] uppercase font-black">Razorpay status</span><span class="font-black {{ $rzpColors[$p['status']] ?? '' }}">{{ ucfirst($p['status']) }}</span></div>
                            <div><span class="text-slate-400 block text-[10px] uppercase font-black">Amount</span>₹{{ number_format($p['amount'], 2) }} · {{ $p['method'] }}</div>
                            <div><span class="text-slate-400 block text-[10px] uppercase font-black">Paid at (IST)</span>{{ $p['created_at'] }}</div>
                            <div><span class="text-slate-400 block text-[10px] uppercase font-black">Payer</span>{{ $p['contact'] ?? '—' }}<span class="block break-all">{{ $p['email'] }}</span></div>
                            @if($p['order_id'])
                                <div><span class="text-slate-400 block text-[10px] uppercase font-black">Order ID</span><span class="font-mono break-all">{{ $p['order_id'] }}</span></div>
                            @endif
                            @if($p['description'])
                                <div class="col-span-2"><span class="text-slate-400 block text-[10px] uppercase font-black">For</span>{{ $p['description'] }}</div>
                            @endif
                            @if($p['error'])
                                <div class="col-span-2 text-rose-700"><span class="text-slate-400 block text-[10px] uppercase font-black">Error</span>{{ $p['error'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </form>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.payment_attempts.index') }}" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-wrap items-end gap-2">
            <div class="flex-grow min-w-[200px]">
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Razorpay ID, TXN ID, name, mobile or email"
                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500">
            </div>
            <div>
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Status</label>
                <select name="status" class="block text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="">All</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">For</label>
                <select name="purpose" class="block text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="">All</option>
                    @foreach($purposes as $key => $label)
                        <option value="{{ $key }}" @selected(request('purpose') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="block text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <div>
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="block text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl">Filter</button>
            @if(request()->hasAny(['search', 'status', 'purpose', 'from', 'to']))
                <a href="{{ route('admin.payment_attempts.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-rose-600">Clear</a>
            @endif
        </form>

        <p class="text-[11px] text-slate-500 font-medium px-1">
            Rows marked <span class="font-black text-amber-700">Paid, not verified yet</span>, <span class="font-black text-amber-700">Verified, not saved</span> or
            <span class="font-black text-rose-700">Verification failed</span> may have taken money without saving anything; use <span class="font-black">Check</span> to confirm with Razorpay.
        </p>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-black uppercase text-slate-600 tracking-wider border-b border-slate-200 whitespace-nowrap">
                        <th class="py-3 px-4">Date (IST)</th>
                        <th class="py-3 px-4">For</th>
                        <th class="py-3 px-4">Payer</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Razorpay</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                    @forelse($attempts as $a)
                        <tr class="hover:bg-slate-50/70 align-top">
                            <td class="py-3 px-4 whitespace-nowrap">
                                {{ $a->created_at?->copy()->setTimezone($tz)->format('d-M-Y h:i A') }}
                                @if($a->updated_at && $a->updated_at->ne($a->created_at))
                                    <span class="block text-[10px] text-slate-400">updated {{ $a->updated_at->copy()->setTimezone($tz)->format('h:i A') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                {{ $a->purpose_label }}
                                @if($a->description)<span class="block text-[10px] text-slate-400 max-w-[220px]">{{ $a->description }}</span>@endif
                            </td>
                            <td class="py-3 px-4">
                                {{ $a->payer_name ?: '—' }}
                                @if($a->payer_contact)<span class="block text-[10px] text-slate-400">{{ $a->payer_contact }}</span>@endif
                                @if($a->payer_email)<span class="block text-[10px] text-slate-400 break-all">{{ $a->payer_email }}</span>@endif
                            </td>
                            <td class="py-3 px-4 text-right font-black whitespace-nowrap">₹{{ number_format((float) $a->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-md border font-extrabold whitespace-nowrap {{ $statusColors[$a->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ $a->status_label }}</span>
                                @if($a->transaction)
                                    <span class="block text-[10px] font-mono font-black text-emerald-700 mt-0.5">{{ $a->transaction->transaction_no }}</span>
                                @endif
                                @if($a->error)
                                    <span class="block text-[10px] text-rose-600 mt-0.5 max-w-[220px]">{{ $a->error }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($a->razorpay_payment_id)<span class="block font-mono text-[10px] break-all">{{ $a->razorpay_payment_id }}</span>@endif
                                @if($a->razorpay_order_id)<span class="block font-mono text-[10px] text-slate-400 break-all">{{ $a->razorpay_order_id }}</span>@endif
                                @if($a->razorpay_status)
                                    <span class="block text-[10px] font-black {{ $rzpColors[$a->razorpay_status] ?? '' }}">
                                        Razorpay: {{ ucfirst($a->razorpay_status) }}
                                        <span class="font-semibold text-slate-400">({{ $a->razorpay_checked_at?->copy()->setTimezone($tz)->format('d M, h:i A') }})</span>
                                    </span>
                                @endif
                                @if(!$a->razorpay_payment_id && !$a->razorpay_order_id)
                                    <span class="text-[10px] text-slate-400">No payment made</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($a->razorpay_payment_id || $a->razorpay_order_id)
                                    <a href="{{ route('admin.payment_attempts.index', array_merge(request()->except('lookup'), ['lookup' => $a->razorpay_payment_id ?: $a->razorpay_order_id])) }}"
                                       class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] rounded-lg whitespace-nowrap">Check</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-400 font-semibold">No payment attempts logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attempts->links() }}
    </div>
@endsection
