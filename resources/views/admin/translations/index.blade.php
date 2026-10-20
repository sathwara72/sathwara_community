@extends('layouts.admin')

@section('page_title', __('messages.website_text_translations'))

@section('content')
    <div class="space-y-4">

        <!-- Info -->
        <div class="p-3.5 bg-sky-50 border border-sky-100 rounded-xl text-xs text-sky-800 font-semibold leading-relaxed">
            {{ __('messages.translations_help') }}
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
            <!-- Group Tabs -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl w-full lg:w-auto">
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
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('messages.translations_search_placeholder') }}"
                        class="h-9 w-full text-xs font-semibold pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 transition-colors">
                    @if(request()->filled('search'))
                        <a href="{{ route('admin.translations.index', request()->except('search', 'page')) }}"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-sm">&times;</a>
                    @endif
                </div>
                <select name="filter" onchange="this.form.submit()"
                        class="h-9 text-xs font-bold px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500">
                    <option value="">{{ __('messages.translations_filter_all') }}</option>
                    <option value="edited" @selected(request('filter') === 'edited')>{{ __('messages.translations_filter_edited') }} ({{ $editedCount }})</option>
                    <option value="missing_gu" @selected(request('filter') === 'missing_gu')>{{ __('messages.translations_filter_missing_gu') }}</option>
                </select>
                <button type="submit"
                    class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700 rounded-xl transition-colors shrink-0">
                    {{ __('messages.search') }}
                </button>
            </form>
        </div>

        <!-- Translation Rows -->
        <div class="space-y-3">
            @forelse($translations as $row)
                <div class="bg-white rounded-xl border {{ $row['edited'] ? 'border-amber-200' : 'border-slate-100' }} shadow-xs p-3.5">
                    <form method="POST" action="{{ route('admin.translations.update') }}" class="space-y-2.5">
                        @csrf
                        <input type="hidden" name="group" value="{{ $group }}">
                        <input type="hidden" name="key" value="{{ $row['key'] }}">

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <code class="text-[11px] font-bold text-slate-500 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-md break-all">{{ $row['key'] }}</code>
                            @if($row['edited'])
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">{{ __('messages.translations_edited_badge') }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            @foreach(['en' => 'English', 'gu' => 'ગુજરાતી'] as $locale => $localeLabel)
                                <div class="space-y-1">
                                    <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ $localeLabel }}</label>
                                    <textarea name="{{ $locale }}" rows="{{ mb_strlen($row[$locale]) > 90 ? 3 : 1 }}"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border {{ $row[$locale . '_override'] !== null ? 'border-amber-300' : 'border-slate-200' }} rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 resize-y">{{ $row[$locale] }}</textarea>
                                    @if($row[$locale . '_override'] !== null)
                                        <p class="text-[10px] font-medium text-slate-400 break-words">{{ __('messages.translations_original') }}: {{ $row[$locale . '_default'] ?: '—' }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            @if($row['edited'])
                                <button type="submit" form="reset-{{ md5($row['key']) }}"
                                    onclick="return confirm(@js(__('messages.translations_reset_confirm')))"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-lg transition-colors">
                                    {{ __('messages.translations_reset') }}
                                </button>
                            @endif
                            <button type="submit"
                                class="px-3.5 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>

                    @if($row['edited'])
                        <form id="reset-{{ md5($row['key']) }}" method="POST" action="{{ route('admin.translations.reset') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="group" value="{{ $group }}">
                            <input type="hidden" name="key" value="{{ $row['key'] }}">
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-100 p-10 text-center text-xs font-semibold text-slate-400">
                    {{ __('messages.translations_none_found') }}
                </div>
            @endforelse
        </div>

        <div>{{ $translations->links() }}</div>
    </div>
@endsection
