@php
    $dlGu = (app()->getLocale() === 'gu');
    $dlToday = \App\Models\Event::localToday();
    $dlRows = [];
    if (!empty($event->form_end_date)) {
        $dlRows[] = [
            'label' => $dlGu ? 'ફોર્મ ભરવાની છેલ્લી તારીખ' : 'Form Fill-up Last Date',
            'date' => date('d M, Y', strtotime($event->form_end_date)),
            'closed' => $dlToday > \Carbon\Carbon::parse($event->form_end_date)->toDateString(),
        ];
    }
    if (!empty($event->registration_end_date)) {
        $dlRows[] = [
            'label' => $dlGu ? 'પાસ ખરીદીની છેલ્લી તારીખ' : 'Pass Purchase Last Date',
            'date' => $event->registration_end_date->format('d M, Y h:i A'),
            'closed' => $event->isPassPurchaseClosed(),
        ];
    }
@endphp
@if(count($dlRows))
    <div class="space-y-1 text-[11px] font-semibold">
        @foreach($dlRows as $row)
            <div class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg border {{ $row['closed'] ? 'bg-rose-50 border-rose-200/90 text-rose-700' : 'bg-amber-50 border-amber-200/90 text-amber-800' }}">
                <span class="truncate">{{ $row['label'] }}</span>
                <span class="shrink-0 inline-flex items-center gap-1 font-black">
                    {{ $row['date'] }}
                    @if($row['closed'])
                        <span class="text-[9px] bg-rose-600 text-white px-1.5 py-0.5 rounded">{{ $dlGu ? 'પૂર્ણ' : 'Closed' }}</span>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
@endif
