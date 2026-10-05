@extends('layouts.admin')

@section('page_title', __('messages.transactions'))

@section('content')
    @php $tc = \App\Http\Controllers\Admin\TransactionController::class; @endphp
    <div class="space-y-4">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-wrap items-end gap-2">
            <div class="flex-grow min-w-[200px]">
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="TXN ID, Razorpay ID, name or mobile"
                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500">
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
                <label class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Paid via</label>
                <select name="method_filter" class="block text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="">All</option>
                    <option value="razorpay" @selected(request('method_filter') === 'razorpay')>Online (Razorpay)</option>
                    <option value="office" @selected(request('method_filter') === 'office')>Office</option>
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
            @if(request()->hasAny(['search', 'purpose', 'method_filter', 'from', 'to']))
                <a href="{{ route('admin.transactions.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-rose-600">Clear</a>
            @endif
            <a href="{{ route('admin.transactions.export', request()->all()) }}"
                class="ml-auto px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200/60 whitespace-nowrap">
                {{ __('messages.export_csv') }}
            </a>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-black uppercase text-slate-600 tracking-wider border-b border-slate-200 whitespace-nowrap">
                        <th class="py-3 px-4">Transaction ID</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">For</th>
                        <th class="py-3 px-4">Payer</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4">Paid via</th>
                        <th class="py-3 px-4">Linked record</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-mono font-black text-slate-900 whitespace-nowrap">{{ $t->transaction_no }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">{{ $t->created_at?->format('d-M-Y h:i A') }}</td>
                            <td class="py-3 px-4">{{ $t->purpose_label }}</td>
                            <td class="py-3 px-4">
                                {{ $t->payer_name ?? '—' }}
                                @if($t->payer_contact)<span class="block text-[10px] text-slate-400">{{ $t->payer_contact }}</span>@endif
                            </td>
                            <td class="py-3 px-4 text-right font-black whitespace-nowrap">₹{{ number_format((float) $t->amount, 2) }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($t->method === 'office')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 font-extrabold">Office</span>
                                    @if($t->recorder)<span class="block text-[10px] text-slate-400 mt-0.5">by {{ $t->recorder->name }}</span>@endif
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-extrabold">Online</span>
                                    <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $t->payment_id }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @php $url = $tc::recordUrl($t); @endphp
                                @if($url)
                                    <a href="{{ $url }}" class="text-primary-600 hover:underline">{{ $tc::recordLabel($t) }}</a>
                                @else
                                    {{ $tc::recordLabel($t) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-400 font-semibold">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
@endsection
