@extends('layouts.admin')

@section('page_title', __('messages.website_text_translations'))

@section('content')
    <style>
        /* Layout for the editor rows (kept here so it works without a CSS rebuild) */
        .tr-row { display: grid; grid-template-columns: 1fr; gap: 12px; }
        @media (min-width: 1024px) { .tr-row { grid-template-columns: 210px minmax(0, 1fr) minmax(0, 1fr); gap: 16px; } }
        .tr-area { min-height: 38px; max-height: 320px; overflow-y: auto; resize: none; line-height: 1.55; }
        .tr-sticky { position: sticky; top: 0; z-index: 20; }
    </style>

    <div class="space-y-4">

        <!-- Summary -->
        <div class="grid grid-cols-3 gap-3">
            @foreach([
                ['label' => __('messages.translations_stat_total'), 'value' => $stats['total'], 'filter' => '', 'tone' => 'text-slate-900 bg-white border-slate-100'],
                ['label' => __('messages.translations_filter_edited'), 'value' => $stats['edited'], 'filter' => 'edited', 'tone' => 'text-amber-700 bg-amber-50 border-amber-100'],
                ['label' => __('messages.translations_filter_missing_gu'), 'value' => $stats['missing_gu'], 'filter' => 'missing_gu', 'tone' => 'text-rose-700 bg-rose-50 border-rose-100'],
            ] as $stat)
                <a href="{{ route('admin.translations.index', ['group' => $group, 'filter' => $stat['filter'] ?: null]) }}"
                   class="block rounded-xl border p-3 shadow-xs transition hover:-translate-y-0.5 {{ $stat['tone'] }} {{ request('filter', '') === $stat['filter'] ? 'ring-2 ring-primary-500/40' : '' }}">
                    <div class="text-xl sm:text-2xl font-black leading-none">{{ number_format($stat['value']) }}</div>
                    <div class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70 mt-1.5">{{ $stat['label'] }}</div>
                </a>
            @endforeach
        </div>

        <!-- Sticky Toolbar -->
        <div class="tr-sticky bg-white p-3 rounded-xl border border-slate-100 shadow-sm space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <!-- Group Tabs -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl w-full lg:w-auto">
                    @foreach(['messages' => __('messages.translations_group_main'), 'json' => __('messages.translations_group_other')] as $tab => $label)
                        <a href="{{ route('admin.translations.index', ['group' => $tab]) }}"
                           class="flex-1 lg:flex-none text-center px-3.5 py-1.5 text-xs font-bold rounded-lg transition-colors {{ $group === $tab ? 'bg-white text-primary-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <!-- Search & Filter -->
                <form method="GET" action="{{ route('admin.translations.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                    <input type="hidden" name="group" value="{{ $group }}">
                    <div class="relative w-full sm:w-72">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('messages.translations_search_placeholder') }}"
                            class="h-9 w-full text-xs font-semibold pl-8 pr-8 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 transition-colors">
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.translations.index', request()->except('search', 'page')) }}"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-sm">&times;</a>
                        @endif
                    </div>
                    <select name="filter" onchange="this.form.submit()"
                            class="h-9 text-xs font-bold px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500">
                        <option value="">{{ __('messages.translations_filter_all') }}</option>
                        <option value="edited" @selected(request('filter') === 'edited')>{{ __('messages.translations_filter_edited') }}</option>
                        <option value="missing_gu" @selected(request('filter') === 'missing_gu')>{{ __('messages.translations_filter_missing_gu') }}</option>
                    </select>
                    <button type="submit"
                        class="h-9 px-4 bg-slate-900 hover:bg-slate-800 font-bold text-xs text-white rounded-xl transition-colors shrink-0">
                        {{ __('messages.search') }}
                    </button>
                </form>
            </div>

            <p class="text-[11px] font-medium text-slate-500 leading-relaxed">
                {{ __('messages.translations_help') }}
                <span class="hidden sm:inline text-slate-400">· {{ __('messages.translations_ctrl_enter') }}</span>
            </p>
        </div>

        <!-- Column headings (desktop) -->
        @if($translations->count())
            <div class="tr-row hidden lg:grid px-4 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                <div>{{ __('messages.translations_col_key') }}</div>
                <div>English</div>
                <div>ગુજરાતી</div>
            </div>
        @endif

        <!-- Editor Rows -->
        <div class="space-y-2.5">
            @forelse($translations as $row)
                <form method="POST" action="{{ route('admin.translations.update') }}"
                      x-data="translationRow(@js([
                          'en' => $row['en'], 'gu' => $row['gu'],
                          'en_default' => $row['en_default'], 'gu_default' => $row['gu_default'],
                          'en_edited' => $row['en_override'] !== null, 'gu_edited' => $row['gu_override'] !== null,
                      ]))"
                      @submit.prevent="save()"
                      @keydown.ctrl.enter.prevent="save()" @keydown.meta.enter.prevent="save()"
                      class="bg-white rounded-xl border shadow-xs p-3.5 lg:p-4 transition-colors"
                      :class="dirty ? 'border-primary-300 ring-2 ring-primary-500/10' : ((state.en_edited || state.gu_edited) ? 'border-amber-200' : 'border-slate-100')">
                    @csrf
                    <input type="hidden" name="group" value="{{ $group }}">
                    <input type="hidden" name="key" value="{{ $row['key'] }}">

                    <div class="tr-row">
                        <!-- Key & status -->
                        <div class="space-y-2 min-w-0">
                            <code class="block text-[11px] font-bold text-slate-600 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md break-all leading-snug" title="{{ $row['key'] }}">{{ $row['key'] }}</code>
                            <div class="flex flex-wrap gap-1">
                                <span x-show="state.en_edited || state.gu_edited" x-cloak class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">{{ __('messages.translations_edited_badge') }}</span>
                                <span x-show="!gu.trim()" x-cloak class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">{{ __('messages.translations_missing_badge') }}</span>
                                <span x-show="dirty" x-cloak class="text-[10px] font-extrabold uppercase tracking-wider text-primary-700 bg-primary-50 border border-primary-100 px-1.5 py-0.5 rounded">{{ __('messages.translations_unsaved') }}</span>
                            </div>
                        </div>

                        <!-- English & Gujarati editors -->
                        @foreach(['en' => 'English', 'gu' => 'ગુજરાતી'] as $locale => $localeLabel)
                            <div class="space-y-1 min-w-0">
                                <label class="lg:hidden text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ $localeLabel }}</label>
                                <textarea name="{{ $locale }}" rows="1" x-model="{{ $locale }}"
                                    x-init="$nextTick(() => grow($el))" @input="grow($el)"
                                    class="tr-area w-full text-[13px] font-medium text-slate-800 px-3 py-2 bg-slate-50 border rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 transition-colors"
                                    :class="state.{{ $locale }}_edited ? 'border-amber-300' : 'border-slate-200'"
                                    @if($locale === 'gu') lang="gu" @endif></textarea>
                                <div class="flex items-start justify-between gap-2 text-[10px] font-semibold text-slate-400">
                                    <div class="min-w-0" x-data="{ showOriginal: false }">
                                        <template x-if="state.{{ $locale }}_edited">
                                            <div>
                                                <button type="button" @click="showOriginal = !showOriginal" class="text-amber-600 hover:text-amber-700 font-bold">
                                                    <span x-text="showOriginal ? @js(__('messages.translations_hide_original')) : @js(__('messages.translations_show_original'))"></span>
                                                </button>
                                                <p x-show="showOriginal" x-transition class="mt-1 p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-500 whitespace-pre-line break-words" x-text="state.{{ $locale }}_default || '—'"></p>
                                            </div>
                                        </template>
                                    </div>
                                    <span class="shrink-0 tabular-nums" x-text="{{ $locale }}.length"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-2 mt-2.5" x-show="dirty || saved || error || state.en_edited || state.gu_edited" x-cloak>
                        <span x-show="saved" x-transition.opacity class="mr-auto text-xs font-bold text-emerald-600">✓ {{ __('messages.translations_saved_short') }}</span>
                        <span x-show="error" x-text="error" class="mr-auto text-xs font-bold text-rose-600"></span>

                        <button type="button" x-show="!dirty && (state.en_edited || state.gu_edited)" @click="reset()" :disabled="busy"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-lg transition-colors">
                            {{ __('messages.translations_reset') }}
                        </button>
                        <button type="button" x-show="dirty" @click="cancel()" :disabled="busy"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-lg transition-colors">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" x-show="dirty" :disabled="busy"
                            class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 disabled:opacity-60 text-white font-bold text-xs rounded-lg shadow-xs transition-colors">
                            <span x-show="!busy">{{ __('messages.save') }}</span>
                            <span x-show="busy" x-cloak>…</span>
                        </button>
                    </div>
                </form>
            @empty
                <div class="bg-white rounded-xl border border-slate-100 p-10 text-center text-xs font-semibold text-slate-400">
                    {{ __('messages.translations_none_found') }}
                </div>
            @endforelse
        </div>

        <div>{{ $translations->links() }}</div>
    </div>

    <script>
        function translationRow(initial) {
            return {
                en: initial.en,
                gu: initial.gu,
                state: initial,
                busy: false,
                saved: false,
                error: '',
                get dirty() {
                    return this.en !== this.state.en || this.gu !== this.state.gu;
                },
                grow(el) {
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight + 2, 320) + 'px';
                },
                regrow() {
                    this.$nextTick(() => this.$root.querySelectorAll('textarea').forEach((el) => this.grow(el)));
                },
                cancel() {
                    this.en = this.state.en;
                    this.gu = this.state.gu;
                    this.error = '';
                    this.regrow();
                },
                async send(url, body) {
                    this.busy = true;
                    this.error = '';
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: body,
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) throw new Error(data.message || '');
                        this.state = data;
                        this.en = data.en;
                        this.gu = data.gu;
                        this.saved = true;
                        setTimeout(() => this.saved = false, 2200);
                        this.regrow();
                    } catch (e) {
                        this.error = @js(__('messages.translations_save_error'));
                    } finally {
                        this.busy = false;
                    }
                },
                save() {
                    if (!this.dirty || this.busy) return;
                    this.send(this.$root.action, new FormData(this.$root));
                },
                reset() {
                    if (this.busy || !confirm(@js(__('messages.translations_reset_confirm')))) return;
                    const body = new FormData();
                    body.append('_token', this.$root.querySelector('input[name=_token]').value);
                    body.append('group', this.$root.querySelector('input[name=group]').value);
                    body.append('key', this.$root.querySelector('input[name=key]').value);
                    this.send(@js(route('admin.translations.reset')), body);
                },
            };
        }
    </script>
@endsection
