{{-- Payments received for one member / business (from the transactions ledger). Expects $transactions. --}}
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4 mt-6">
    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-sm border border-emerald-100">🧾</div>
        <div>
            <h4 class="text-base font-black text-slate-900 leading-tight">Payments</h4>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Every payment received, with its Transaction ID.</p>
        </div>
    </div>

    @if($transactions->isEmpty())
        <p class="text-xs text-slate-500 font-medium">No payments recorded.</p>
    @else
        <div class="overflow-x-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left border-collapse min-w-[620px]">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-black uppercase text-slate-600 tracking-wider border-b border-slate-200 whitespace-nowrap">
                        <th class="py-3 px-4">Transaction ID</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">For</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4">Paid via</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                    @foreach($transactions as $t)
                        <tr>
                            <td class="py-3 px-4 font-mono font-black text-slate-900 whitespace-nowrap">{{ $t->transaction_no }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">{{ $t->created_at?->format('d-M-Y h:i A') }}</td>
                            <td class="py-3 px-4">{{ $t->purpose_label }}</td>
                            <td class="py-3 px-4 text-right font-black whitespace-nowrap">₹{{ number_format((float) $t->amount, 2) }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($t->method === 'office')
                                    Office{{ $t->recorder ? ' (' . $t->recorder->name . ')' : '' }}
                                @else
                                    Online <span class="block text-[10px] text-slate-400 font-mono">{{ $t->payment_id }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
