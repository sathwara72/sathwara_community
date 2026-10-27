{{-- Review status of a submission (only shown when an admin has flagged it) --}}
@switch($reg->status)
    @case(\App\Models\EventRegistration::STATUS_CORRECTION)
        <span class="inline-block px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-300 text-[9px] font-black uppercase tracking-wider">⚠ Correction asked</span>
        @break
    @case(\App\Models\EventRegistration::STATUS_RESUBMITTED)
        <span class="inline-block px-1.5 py-0.5 rounded bg-blue-50 text-blue-800 border border-blue-300 text-[9px] font-black uppercase tracking-wider">↻ Updated, check again</span>
        @break
    @case(\App\Models\EventRegistration::STATUS_REJECTED)
        <span class="inline-block px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-300 text-[9px] font-black uppercase tracking-wider">✕ Rejected</span>
        @break
@endswitch
