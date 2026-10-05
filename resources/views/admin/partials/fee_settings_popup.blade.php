{{--
    Gear button + popup to set fees as Free or Paid with an amount.
    Expects: $title, $action (form URL), $fields = [['name' => 'member_signup_fee', 'label' => '...', 'value' => 1000, 'help' => '...'], ...]
--}}
<div x-data="{ open: {{ $errors->hasAny(collect($fields)->flatMap(fn ($f) => [$f['name'], $f['name'] . '_mode'])->all()) ? 'true' : 'false' }} }" class="inline-block">
    <button type="button" @click="open = true"
        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer whitespace-nowrap">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span>Fee Settings</span>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="open = false" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-2xl max-w-md w-full space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900">{{ $title }}</h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                </div>

                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @foreach($fields as $field)
                        @php
                            $value = (float) old($field['name'], $field['value']);
                            $mode = old($field['name'] . '_mode', $value > 0 ? 'paid' : 'free');
                        @endphp
                        <div x-data="{ mode: '{{ $mode }}' }" class="p-3 rounded-xl border border-slate-200 bg-slate-50/60 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-extrabold text-slate-800">{{ $field['label'] }}</span>
                                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-[11px] font-black">
                                    <label class="px-3 py-1 rounded-md cursor-pointer" :class="mode === 'free' ? 'bg-emerald-600 text-white' : 'text-slate-500'">
                                        <input type="radio" class="hidden" name="{{ $field['name'] }}_mode" value="free" x-model="mode"> Free
                                    </label>
                                    <label class="px-3 py-1 rounded-md cursor-pointer" :class="mode === 'paid' ? 'bg-primary-600 text-white' : 'text-slate-500'">
                                        <input type="radio" class="hidden" name="{{ $field['name'] }}_mode" value="paid" x-model="mode"> Paid
                                    </label>
                                </div>
                            </div>
                            <div x-show="mode === 'paid'" class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">₹</span>
                                <input type="number" name="{{ $field['name'] }}" min="1" step="1" value="{{ $value > 0 ? $value + 0 : '' }}"
                                    :required="mode === 'paid'" placeholder="Amount"
                                    class="h-10 w-full text-sm font-black pl-7 pr-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:border-primary-500">
                            </div>
                            @if(!empty($field['help']))
                                <p class="text-[10px] text-slate-500 font-medium">{{ $field['help'] }}</p>
                            @endif
                            @error($field['name']) <p class="text-xs text-rose-600 font-bold">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div class="pt-2 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold text-xs rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs">Save Fees</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
