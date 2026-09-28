@extends('layouts.admin')

@section('page_title', __('messages.plan_community_event'))

@section('content')
    <div class="max-w-6xl bg-white border border-slate-100 rounded-xl pt-2 pb-6 px-6 shadow-sm">

        <!-- Errors -->
        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl">
                <ul class="list-disc pl-4 text-xs font-semibold space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data"
              class="space-y-3"
              x-data="{ eventType: '{{ old('event_type', 'normal') }}', allowFormFillup: {{ old('has_registration_form', '1') == '1' ? 'true' : 'false' }}, bannerPreview: null }">
            @csrf

            <input type="hidden" name="max_participants" value="0">

            <!-- Title, Event Type & Allow Form Fillup Checkbox -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <!-- Event Title -->
                <div class="md:col-span-6 space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.event_title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>

                <!-- Event Type -->
                <div :class="eventType !== 'normal' ? 'md:col-span-3' : 'md:col-span-6'" class="space-y-0.5 transition-all duration-200">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.event_type') }}</label>
                    <select name="event_type" x-model="eventType" required
                        class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                        <option value="normal" {{ old('event_type') == 'normal' ? 'selected' : '' }}>1. {{ __('messages.normal_event') }}</option>
                        <option value="inam_vitaran" {{ old('event_type') == 'inam_vitaran' ? 'selected' : '' }}>2. {{ __('messages.inam_vitaran_event') }}</option>
                        <option value="yuva_melo" {{ old('event_type') == 'yuva_melo' ? 'selected' : '' }}>3. {{ __('messages.yuva_melo_event') }}</option>
                    </select>
                </div>

                <!-- Allow Form Fillup Checkbox (Hidden when eventType is normal) -->
                <div x-show="eventType !== 'normal'" x-cloak class="md:col-span-3 space-y-0.5 transition-all duration-200">
                    <label class="text-[10px] font-bold text-slate-500 uppercase block">{{ __('messages.allow_form_fillup') }}</label>
                    <label class="flex items-center gap-2 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-100/80 transition-colors h-[34px]">
                        <input type="hidden" name="has_registration_form" value="0">
                        <input type="checkbox" name="has_registration_form" value="1" x-model="allowFormFillup"
                            class="w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 cursor-pointer shrink-0">
                        <span class="text-[11px] font-bold text-slate-700 select-none truncate" x-text="eventType === 'inam_vitaran' ? '{{ __('messages.enable_student_award_form') }}' : '{{ __('messages.enable_registration_form') }}'"></span>
                    </label>
                </div>
            </div>

            <!-- Form Fillup Start & End Dates (Only when Allow Form Fillup is checked) -->
            <div x-show="eventType !== 'normal' && allowFormFillup" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center transition-all duration-200">
                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                        <span>{{ __('messages.form_published_date') }}</span>
                        <span class="text-[9px] text-slate-400 font-normal">({{ __('messages.form_start_date') }})</span>
                    </label>
                    <input type="date" name="form_start_date" value="{{ old('form_start_date') }}"
                           @click="$event.target.showPicker?.()"
                           @focus="$event.target.showPicker?.()"
                           class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>

                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                        <span>{{ __('messages.form_fillup_last_date') }}</span>
                        <span class="text-[9px] text-rose-500 font-bold">({{ __('messages.last_date_deadline') }})</span>
                    </label>
                    <input type="date" name="form_end_date" value="{{ old('form_end_date') }}"
                           @click="$event.target.showPicker?.()"
                           @focus="$event.target.showPicker?.()"
                           class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>
            </div>

            <!-- Date, Time, and Banner Image -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.event_date') }}</label>
                    <input type="date" name="date" value="{{ old('date') }}"
                           @click="$event.target.showPicker?.()"
                           @focus="$event.target.showPicker?.()"
                           required
                           class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>

                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.time') ?? 'તારીખ / સમય' }}</label>
                    <input type="time" name="time" value="{{ old('time') }}"
                           @click="$event.target.showPicker?.()"
                           @focus="$event.target.showPicker?.()"
                           required
                           class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>

                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.banner_image') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="file" name="banner"
                            @change="if ($event.target.files.length) { bannerPreview = URL.createObjectURL($event.target.files[0]) }"
                            class="text-[10px] block w-full text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        <template x-if="bannerPreview">
                            <div class="relative w-14 h-8 rounded border border-slate-200 bg-slate-900 shrink-0 overflow-hidden shadow-xs" title="Banner Preview">
                                <img :src="bannerPreview" class="w-full h-full object-cover">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Pass Purchase Last Date (Mandatory for every event type) -->
            <div class="space-y-0.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                    <span>{{ __('messages.pass_purchase_last_date') }} <span class="text-rose-500">*</span></span>
                    <span class="text-[9px] text-rose-500 font-bold">({{ __('messages.last_date_deadline') }})</span>
                </label>
                <input type="date" name="registration_end_date" value="{{ old('registration_end_date') }}" required
                       @click="$event.target.showPicker?.()"
                       @focus="$event.target.showPicker?.()"
                       class="w-full md:w-1/2 text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
            </div>

            <!-- Total Pass Purchase Limit (All Event Types) -->
            <div class="space-y-0.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                    <span>{{ __('messages.total_pass_limit') }}</span>
                    <span class="text-[9px] text-slate-400 font-normal">({{ __('messages.blank_for_unlimited') }})</span>
                </label>
                <input type="number" name="total_pass_limit" step="1" min="1" value="{{ old('total_pass_limit') }}" placeholder="{{ __('messages.blank_for_unlimited') }}"
                    class="w-full md:w-1/2 text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
            </div>

            <div class="space-y-0.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.venue_hall_address') }}</label>
                <input type="text" name="venue" value="{{ old('venue') }}" required
                    class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
            </div>

            <div class="space-y-0.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                    <span>{{ __('messages.google_map_link_embed_url') }}</span>
                    <span class="text-[9px] text-slate-400 font-normal">({{ __('messages.google_map_iframe_code') }})</span>
                </label>
                <input type="text" name="google_map_link" value="{{ old('google_map_link') }}" placeholder="e.g. https://www.google.com/maps/embed?pb=... or Google Map Link"
                    class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
            </div>

            <div class="grid grid-cols-1 gap-3" :class="eventType === 'yuva_melo' ? 'sm:grid-cols-3' : 'sm:grid-cols-2'">
                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.event_pass_fee') }}</label>
                    <input type="number" name="pass_fee" step="1" min="0" value="{{ old('pass_fee', '0') }}" placeholder="0 for Free Event"
                        class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                </div>

                <!-- Yuva Melo Registration Form Fee -->
                <div class="space-y-0.5" x-show="eventType === 'yuva_melo'" x-transition>
                    <label class="text-[10px] font-bold text-purple-700 uppercase flex items-center justify-between">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>{{ __('messages.form_fee') }}</span>
                        </span>
                        <span class="text-[9px] text-purple-500 font-normal">({{ __('messages.youth_form_fee') }})</span>
                    </label>
                    <input type="number" name="form_fee" step="1" min="0" value="{{ old('form_fee', '0') }}" placeholder="0 for Free Form"
                        class="w-full text-xs font-bold text-purple-900 px-3 py-1.5 bg-purple-50/50 border border-purple-200 rounded-lg focus:bg-white focus:border-purple-500">
                </div>

                <div class="space-y-0.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.status') }}</label>
                    <select name="status" required
                        class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>{{ __('messages.draft_private') }}</option>
                        <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>{{ __('messages.published_public') }}</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                    </select>
                </div>
            </div>

            <!-- Who can buy passes -->
            <div class="space-y-0.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-between">
                    <span>{{ __('messages.pass_purchase_access') }}</span>
                    <span class="text-[9px] text-slate-400 font-normal">({{ __('messages.pass_purchase_access_hint') }})</span>
                </label>
                <select name="pass_purchase_access" required
                    class="w-full md:w-1/2 text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                    <option value="members_only" {{ old('pass_purchase_access', 'members_only') == 'members_only' ? 'selected' : '' }}>{{ __('messages.pass_access_members_only') }}</option>
                    <option value="anyone" {{ old('pass_purchase_access', 'members_only') == 'anyone' ? 'selected' : '' }}>{{ __('messages.pass_access_anyone') }}</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.event_description') }}</label>
                <input type="hidden" name="description" id="description_input" value="{{ old('description') }}">
                <div class="rounded-lg overflow-hidden border border-slate-200 shadow-xs">
                    <div id="quill_editor" class="bg-slate-50 min-h-[140px] text-xs font-medium">
                        {!! old('description') !!}
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var editorEl = document.querySelector('#quill_editor');
                    var descInput = document.querySelector('#description_input');
                    if (editorEl && descInput && typeof Quill !== 'undefined') {
                        var quill = new Quill('#quill_editor', {
                            theme: 'snow',
                            placeholder: '{{ __('messages.write_event_description_placeholder') }}',
                            modules: {
                                toolbar: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    [{ 'color': [] }, { 'background': [] }],
                                    ['clean']
                                ]
                            }
                        });

                        // Real-time sync on every keystroke
                        quill.on('text-change', function() {
                            var html = quill.root.innerHTML;
                            descInput.value = (html === '<p><br></p>') ? '' : html;
                        });

                        // Sync on form submission
                        var form = editorEl.closest('form');
                        if (form) {
                            form.addEventListener('submit', function() {
                                var html = quill.root.innerHTML;
                                descInput.value = (html === '<p><br></p>') ? '' : html;
                            });
                        }
                    }
                });
            </script>

            <div class="pt-3 border-t border-slate-100 flex justify-end items-center gap-2">
                <a href="{{ route('admin.events.index') }}"
                    class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg transition-colors">{{ __('messages.cancel') }}</a>
                <button type="submit"
                    class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-sm transition-colors">
                    {{ __('messages.save_and_publish') }}
                </button>
            </div>
        </form>
    </div>
@endsection