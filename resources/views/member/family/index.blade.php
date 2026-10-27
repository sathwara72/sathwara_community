@extends('layouts.member')
@section('page_title', __('messages.family_members'))
@section('content')
    @php
        // DOB picker limits: Father / Mother are older than the member, everyone else younger
        $memberDob = auth()->user()->memberProfile?->dob;
        $dobChildMin = $memberDob ? \Carbon\Carbon::parse($memberDob)->addDay()->format('Y-m-d') : '';
        $dobChildMax = \Carbon\Carbon::yesterday()->format('Y-m-d');
        $dobParentMax = $memberDob ? \Carbon\Carbon::parse($memberDob)->subDay()->format('Y-m-d') : $dobChildMax;
    @endphp
    <div x-data="{
            showAddModal: @json($errors->any() && !session()->has('edit_id')),
            showEditModal: @json($errors->any() && session()->has('edit_id')),
            showTreeModal: false,
            search: '',
            addRelationship: '{{ old('relationship') }}',
            addGender: '{{ old('gender', 'Male') }}',
            addMaritalStatus: '{{ old('marital_status', 'Unmarried') }}',
            editMember: {
                id: '{{ old('id', session('edit_id')) }}',
                parent_id: '{{ old('parent_id') }}',
                name: '{{ old('name') }}',
                relationship: '{{ old('relationship') }}',
                gender: '{{ old('gender') }}',
                marital_status: '{{ old('marital_status') }}',
                education: '{{ old('education') }}',
                occupation: '{{ old('occupation') }}',
                phone: '{{ old('phone') }}',
                email: '{{ old('email') }}',
                blood_group: '{{ old('blood_group') }}',
                dob: '{{ old('dob') }}',
                update_url: '{{ session('edit_id') ? route('member.family.update', session('edit_id')) : '' }}'
            },
            isParentRel(value) {
                return ['Father', 'Mother', 'પિતા', 'માતા'].includes(value);
            },
            openEdit(memberItem) {
                this.editMember = Object.assign({}, memberItem);
                this.showEditModal = true;
            },
            init() {
                this.$watch('showAddModal', value => {
                    if (!value) {
                        document.getElementById('add-member-form')?.reset();
                        this.addRelationship = '';
                        this.addGender = 'Male';
                        this.addMaritalStatus = 'Unmarried';
                    }
                });
                this.$watch('addRelationship', value => {
                    if (['Wife', 'Daughter-in-law', 'Daughter', 'Granddaughter (Son\'s Daughter)', 'Granddaughter (Daughter\'s Daughter)', 'પત્ની', 'દીકરી', 'વહુ', 'પૌત્રી', 'દોહિત્રી', 'Mother', 'માતા'].includes(value)) {
                        this.addGender = 'Female';
                    } else if (['Husband', 'Son', 'Son-in-law', 'Grandson (Son\'s Son)', 'Grandson (Daughter\'s Son)', 'पति', 'પતિ', 'દીકરો', 'જમાઈ', 'પૌત્ર', 'દોહિત્ર', 'Father', 'પિતા'].includes(value)) {
                        this.addGender = 'Male';
                    }

                    if (['Wife', 'Husband', 'Daughter-in-law', 'Son-in-law', 'પત્ની', 'पति', 'પતિ', 'વહુ', 'જમાઈ', 'Father', 'Mother', 'પિતા', 'માતા'].includes(value)) {
                        this.addMaritalStatus = 'Married';
                    }
                });
                this.$watch('editMember.relationship', value => {
                    if (['Wife', 'Daughter-in-law', 'Daughter', 'Granddaughter (Son\'s Daughter)', 'Granddaughter (Daughter\'s Daughter)', 'પત્ની', 'દીકરી', 'વહુ', 'પૌત્રી', 'દોહિત્રી', 'Mother', 'માતા'].includes(value)) {
                        this.editMember.gender = 'Female';
                    } else if (['Husband', 'Son', 'Son-in-law', 'Grandson (Son\'s Son)', 'Grandson (Daughter\'s Son)', 'पति', 'પતિ', 'દીકરો', 'જમાઈ', 'પૌત્ર', 'દોહિત્ર', 'Father', 'પિતા'].includes(value)) {
                        this.editMember.gender = 'Male';
                    }

                    if (['Wife', 'Husband', 'Daughter-in-law', 'Son-in-law', 'પત્ની', 'पति', 'પતિ', 'વહુ', 'જમાઈ', 'Father', 'Mother', 'પિતા', 'માતા'].includes(value)) {
                        this.editMember.marital_status = 'Married';
                    }
                });
            }
        }" class="space-y-4">

        <div
            class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between bg-white p-3.5 sm:p-4 rounded-xl border border-slate-100 shadow-sm gap-3">
            
            <!-- Full Width Search bar -->
            <div class="relative flex-1 min-w-0">
                <!-- <div class="absolute inset-y-0 left-5 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div> -->
                <input type="text" x-model="search" placeholder="{{ __('messages.search_family') }}"
                    class="w-full text-xs font-semibold pl-3 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 transition-colors placeholder:text-slate-400">
                <button type="button" x-show="search" @click="search = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-base leading-none"
                    title="Clear search">
                    &times;
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button @click="showTreeModal = true"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <span>🌳</span> {{ __('messages.view_family_tree') }}
                </button>
                <button @click="showAddModal = true"
                    class="px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer text-center whitespace-nowrap">
                    + {{ __('messages.add_family_member') }}
                </button>
            </div>
        </div>

        <!-- Family List Card Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-2">
            @forelse($family as $member)
                <div x-show="!search || 
                                     '{{ addslashes(strtolower($member->name)) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->relationship)) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->gender ?? '')) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->education ?? '')) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->occupation ?? '')) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->phone ?? '')) }}'.includes(search.toLowerCase()) || 
                                     '{{ addslashes(strtolower($member->email ?? '')) }}'.includes(search.toLowerCase())"
                    class="bg-white border border-slate-100 rounded-2xl p-3.5 shadow-sm hover:shadow-md transition-all space-y-3 relative overflow-hidden">

                    <!-- Card Header: Avatar / Name / Relationship / Actions -->
                    <div class="flex items-start justify-between gap-1.5">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div
                                class="w-9 h-9 rounded-xl bg-primary-50 text-primary-600 font-extrabold flex items-center justify-center text-xs shrink-0 border border-primary-100">
                                {{ mb_substr($member->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-extrabold text-slate-900 leading-tight truncate"
                                    title="{{ $member->name }}">{{ $member->name }}</h4>
                                <div class="flex flex-wrap items-center gap-1 mt-0.5">
                                    <span
                                        class="text-[9px] font-extrabold text-primary-700 bg-primary-50 px-1.5 py-0.5 rounded uppercase tracking-wide border border-primary-100">
                                        {{ $member->relationship }}
                                    </span>
                                    @if($member->gender)
                                        <span class="text-[9px] font-bold text-slate-600 bg-slate-100 px-1 py-0.5 rounded">
                                            {{ $member->gender }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-0.5 shrink-0">
                            <button type="button" @click="openEdit({
                                            id: {{ $member->id }},
                                            parent_id: '{{ $member->parent_id }}',
                                            name: {{ json_encode($member->name) }},
                                            relationship: {{ json_encode($member->relationship) }},
                                            gender: '{{ $member->gender }}',
                                            marital_status: '{{ $member->marital_status }}',
                                            dob: '{{ $member->dob }}',
                                            education: {{ json_encode($member->education) }},
                                            occupation: {{ json_encode($member->occupation) }},
                                            phone: '{{ $member->phone }}',
                                            email: '{{ $member->email }}',
                                            blood_group: '{{ $member->blood_group }}',
                                            update_url: '{{ route('member.family.update', $member->id) }}'
                                        })" title="Edit"
                                class="p-1 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button type="button"
                                @click="$dispatch('confirm-delete', { action: '{{ route('member.family.destroy', $member->id) }}', message: '{{ __('messages.delete_confirm_family_member') }}' })"
                                title="Delete"
                                class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- All Details Grid inside Card -->
                    <div
                        class="grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-600 pt-2.5 border-t border-slate-100">
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.dob') }}:</span>
                            <span
                                class="text-slate-800 font-bold">{{ $member->dob ? date('d-M-Y', strtotime($member->dob)) : __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.marital_status') }}:</span>
                            <span
                                class="text-slate-800 font-bold truncate block">{{ $member->marital_status ? __('messages.' . strtolower($member->marital_status)) : __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.blood_group') }}:</span>
                            <span
                                class="text-rose-600 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100 inline-block">{{ $member->blood_group ?: __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.education') }}:</span>
                            <span
                                class="text-slate-800 font-bold truncate block">{{ $member->education ?: __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.occupation') }}:</span>
                            <span
                                class="text-slate-800 font-bold truncate block">{{ $member->occupation ?: __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.mobile') }}:</span>
                            <span
                                class="text-slate-800 font-bold truncate block">{{ $member->phone ?: __('messages.not_set') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.email') }}:</span>
                            <span
                                class="text-slate-800 font-bold truncate block">{{ $member->email ?: __('messages.not_set') }}</span>
                        </div>
                    </div>

                </div>
            @empty
                <div
                    class="col-span-full bg-white border border-slate-100 rounded-2xl p-12 text-center text-xs text-slate-400 space-y-2">
                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p>{{ __('messages.no_family_members') }}</p>
                </div>
            @endforelse
        </div>

        <!-- ============ ADD MODAL ============ -->
        <template x-teleport="body">
            <div x-show="showAddModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                x-transition x-cloak>
                <div @click.away="showAddModal = false"
                    class="bg-white rounded-xl p-3.5 border border-slate-100 shadow-2xl max-w-md w-full relative max-h-[95vh] overflow-y-auto">
                    <button @click="showAddModal = false"
                        class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 pr-6 border-b border-slate-100 pb-2 mb-3 flex items-center gap-2">
                        👨‍👩‍👧‍👦 {{ __('messages.add_family_member') }}
                    </h3>

                    <form id="add-member-form" x-ref="addForm" method="POST" action="{{ route('member.family.store') }}">
                        @csrf

                        <div class="space-y-3">
                            @if($errors->any() && !session()->has('edit_id'))
                                <div
                                    class="p-3 bg-rose-50 border border-rose-100 text-rose-700 text-xs font-semibold rounded-xl">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <label
                                    class="text-xs font-bold text-slate-700 block">{{ __('messages.name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    placeholder="Enter Full Name"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.relationship') }} <span class="text-rose-500">*</span></label>
                                    @php
                                        $userGender = $profile->gender ?? 'Male';
                                        $isFemaleMember = strtolower($userGender) === 'female';
                                        $hasExistingSpouse = $family->contains(fn($m) => in_array($m->relationship, ['Wife', 'Husband', 'Spouse', 'પત્ની', 'પતિ']));
                                        $hasFather = $family->contains(fn($m) => in_array($m->relationship, ['Father', 'પિતા']));
                                        $hasMother = $family->contains(fn($m) => in_array($m->relationship, ['Mother', 'માતા']));
                                    @endphp
                                    <select name="relationship" required x-model="addRelationship"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="" disabled>{{ __('messages.select_relationship') }}</option>
                                        @if(!$hasExistingSpouse)
                                            @if($isFemaleMember)
                                                <option value="Husband">{{ __('messages.rel_husband') }}</option>
                                            @else
                                                <option value="Wife">{{ __('messages.rel_wife') }}</option>
                                            @endif
                                        @endif
                                        @unless($hasFather)
                                            <option value="Father">{{ __('messages.rel_father') }}</option>
                                        @endunless
                                        @unless($hasMother)
                                            <option value="Mother">{{ __('messages.rel_mother') }}</option>
                                        @endunless
                                        <option value="Son">{{ __('messages.rel_son') }}</option>
                                        <option value="Daughter">{{ __('messages.rel_daughter') }}</option>
                                        <option value="Daughter-in-law">{{ __('messages.rel_daughter_in_law') }}</option>
                                        <option value="Son-in-law">{{ __('messages.rel_son_in_law') }}</option>
                                        <option value="Grandson (Son's Son)">{{ __('messages.rel_grandson_sons_son') }}
                                        </option>
                                        <option value="Granddaughter (Son's Daughter)">
                                            {{ __('messages.rel_granddaughter_sons_daughter') }}</option>
                                        <option value="Grandson (Daughter's Son)">
                                            {{ __('messages.rel_grandson_daughters_son') }}</option>
                                        <option value="Granddaughter (Daughter's Daughter)">
                                            {{ __('messages.rel_granddaughter_daughters_daughter') }}</option>
                                        <option value="Other">{{ __('messages.rel_other') }}</option>
                                    </select>

                                    @php
                                        $sons = $family->filter(fn($m) => in_array($m->relationship, ['Son', 'Son (દીકરો)', 'દીકરો']));
                                        $daughters = $family->filter(fn($m) => in_array($m->relationship, ['Daughter', 'Daughter (દીકરી)', 'દીકરી']));
                                    @endphp

                                    <div x-show="['Grandson (Son\'s Son)', 'Granddaughter (Son\'s Daughter)', 'Daughter-in-law'].includes(addRelationship)"
                                        class="space-y-1 mt-2">
                                        <label
                                            class="text-xs font-bold text-primary-600 block">{{ __('messages.select_parent_son') }}</label>
                                        <select name="parent_id"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                            <option value="">{{ __('messages.select_son') }}</option>
                                            @foreach($sons as $s)
                                                <option value="{{ $s->id }}" {{ old('parent_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div x-show="['Grandson (Daughter\'s Son)', 'Granddaughter (Daughter\'s Daughter)', 'Son-in-law'].includes(addRelationship)"
                                        class="space-y-1 mt-2">
                                        <label
                                            class="text-xs font-bold text-primary-600 block">{{ __('messages.select_parent_daughter') }}</label>
                                        <select name="parent_id"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                            <option value="">{{ __('messages.select_daughter') }}</option>
                                            @foreach($daughters as $d)
                                                <option value="{{ $d->id }}" {{ old('parent_id') == $d->id ? 'selected' : '' }}>
                                                    {{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.gender') }} <span class="text-rose-500">*</span></label>
                                    <select name="gender" required x-model="addGender"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="Male">{{ __('messages.gender_male') }}</option>
                                        <option value="Female">{{ __('messages.gender_female') }}</option>
                                        <option value="Other">{{ __('messages.gender_other') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.marital_status') }} <span class="text-rose-500">*</span></label>
                                    <select name="marital_status" required x-model="addMaritalStatus"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="Unmarried">{{ __('messages.unmarried') }}</option>
                                        <option value="Married">{{ __('messages.married') }}</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.dob') }}</label>
                                    <input type="date" name="dob" value="{{ old('dob') }}"
                                        :min="isParentRel(addRelationship) ? '' : '{{ $dobChildMin }}'"
                                        :max="isParentRel(addRelationship) ? '{{ $dobParentMax }}' : '{{ $dobChildMax }}'"
                                        @click="$event.target.showPicker?.()" @focus="$event.target.showPicker?.()"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none cursor-pointer h-10">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.education') }}</label>
                                    <input type="text" name="education" value="{{ old('education') }}"
                                        placeholder="e.g. Graduate"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.blood_group') }}</label>
                                    <select name="blood_group"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="">{{ __('messages.select_blood_group') }}</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                        placeholder="family@member.com"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.occupation') }}</label>
                                    <input type="text" name="occupation" value="{{ old('occupation') }}"
                                        placeholder="e.g. Student"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.phone') }}</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                        placeholder="10-digit number"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>
                                <div></div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex justify-end items-center space-x-2">
                                <button type="button" @click="showAddModal = false"
                                    class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">{{ __('messages.cancel') }}</button>
                                <button type="submit"
                                    class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer">
                                    {{ __('messages.save_member') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============ EDIT MODAL ============ -->
        <template x-teleport="body">
            <div x-show="showEditModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                x-transition x-cloak>
                <div @click.away="showEditModal = false"
                    class="bg-white rounded-xl p-3.5 border border-slate-100 shadow-2xl max-w-md w-full relative max-h-[95vh] overflow-y-auto">
                    <button @click="showEditModal = false"
                        class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 pr-6 border-b border-slate-100 pb-2 mb-3 flex items-center gap-2">
                        ✏️ {{ __('messages.edit_family_member') }}
                    </h3>

                    <form method="POST" :action="editMember.update_url">
                        @csrf
                        @method('PUT')

                        <div class="space-y-3">
                            @if($errors->any() && session()->has('edit_id'))
                                <div
                                    class="p-3 bg-rose-50 border border-rose-100 text-rose-700 text-xs font-semibold rounded-xl">
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <label
                                    class="text-xs font-bold text-slate-700 block">{{ __('messages.name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editMember.name" required
                                    placeholder="Enter Full Name"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.relationship') }} <span class="text-rose-500">*</span></label>
                                    <select name="relationship" required x-model="editMember.relationship"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        @if($isFemaleMember)
                                            <option value="Husband"
                                                x-show="!{{ $hasExistingSpouse ? 'true' : 'false' }} || ['Husband', 'Spouse', 'પતિ'].includes(editMember.relationship)">
                                                {{ __('messages.rel_husband') }}</option>
                                        @else
                                            <option value="Wife"
                                                x-show="!{{ $hasExistingSpouse ? 'true' : 'false' }} || ['Wife', 'Spouse', 'પત્ની'].includes(editMember.relationship)">
                                                {{ __('messages.rel_wife') }}</option>
                                        @endif
                                        <option value="Father"
                                            x-show="!{{ $hasFather ? 'true' : 'false' }} || ['Father', 'પિતા'].includes(editMember.relationship)">
                                            {{ __('messages.rel_father') }}</option>
                                        <option value="Mother"
                                            x-show="!{{ $hasMother ? 'true' : 'false' }} || ['Mother', 'માતા'].includes(editMember.relationship)">
                                            {{ __('messages.rel_mother') }}</option>
                                        <option value="Son">{{ __('messages.rel_son') }}</option>
                                        <option value="Daughter">{{ __('messages.rel_daughter') }}</option>
                                        <option value="Daughter-in-law">{{ __('messages.rel_daughter_in_law') }}</option>
                                        <option value="Son-in-law">{{ __('messages.rel_son_in_law') }}</option>
                                        <option value="Grandson (Son's Son)">{{ __('messages.rel_grandson_sons_son') }}
                                        </option>
                                        <option value="Granddaughter (Son's Daughter)">
                                            {{ __('messages.rel_granddaughter_sons_daughter') }}</option>
                                        <option value="Grandson (Daughter's Son)">
                                            {{ __('messages.rel_grandson_daughters_son') }}</option>
                                        <option value="Granddaughter (Daughter's Daughter)">
                                            {{ __('messages.rel_granddaughter_daughters_daughter') }}</option>
                                        <option value="Other">{{ __('messages.rel_other') }}</option>
                                    </select>

                                    <div x-show="['Grandson (Son\'s Son)', 'Granddaughter (Son\'s Daughter)', 'Daughter-in-law', 'પૌત્ર', 'પૌત્રી', 'વહુ'].some(r => (editMember.relationship || '').includes(r))"
                                        class="space-y-1 mt-2">
                                        <label class="text-xs font-bold text-primary-600 block">Select
                                            Parent Son / Husband</label>
                                        <select name="parent_id" x-model="editMember.parent_id"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                            <option value="">Select Son</option>
                                            @foreach($sons as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div x-show="['Grandson (Daughter\'s Son)', 'Granddaughter (Daughter\'s Daughter)', 'Son-in-law', 'દોહિત્ર', 'દોહિત્રી', 'જમાઈ'].some(r => (editMember.relationship || '').includes(r))"
                                        class="space-y-1 mt-2">
                                        <label class="text-xs font-bold text-primary-600 block">Select
                                            Parent Daughter / Wife</label>
                                        <select name="parent_id" x-model="editMember.parent_id"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                            <option value="">Select Daughter</option>
                                            @foreach($daughters as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.gender') }} <span class="text-rose-500">*</span></label>
                                    <select name="gender" required x-model="editMember.gender"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.marital_status') }} <span class="text-rose-500">*</span></label>
                                    <select name="marital_status" required x-model="editMember.marital_status"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="Unmarried">{{ __('messages.unmarried') }}</option>
                                        <option value="Married">{{ __('messages.married') }}</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.dob') }}</label>
                                    <input type="date" name="dob" x-model="editMember.dob"
                                        :min="isParentRel(editMember.relationship) ? '' : '{{ $dobChildMin }}'"
                                        :max="isParentRel(editMember.relationship) ? '{{ $dobParentMax }}' : '{{ $dobChildMax }}'"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.education') }}</label>
                                    <input type="text" name="education" x-model="editMember.education"
                                        placeholder="e.g. Graduate"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-700 block">{{ __('messages.blood_group') }}</label>
                                    <select name="blood_group" x-model="editMember.blood_group"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-700 block">{{ __('messages.email') }}</label>
                                    <input type="email" name="email" x-model="editMember.email"
                                        placeholder="family@member.com"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>

                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.occupation') }}</label>
                                    <input type="text" name="occupation" x-model="editMember.occupation"
                                        placeholder="e.g. Student"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label
                                        class="text-xs font-bold text-slate-700 block">{{ __('messages.phone') }}</label>
                                    <input type="text" name="phone" x-model="editMember.phone" maxlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                        placeholder="10-digit number"
                                        class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                                </div>
                                <div></div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex justify-end items-center space-x-2">
                                <button type="button" @click="showEditModal = false"
                                    class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">Cancel</button>
                                <button type="submit"
                                    class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer">
                                    {{ __('messages.update_details') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- FAMILY TREE PREVIEW MODAL -->
        <template x-teleport="body">
            <div x-show="showTreeModal" x-cloak
                class="fixed inset-0 z-[100] overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 min-h-screen w-screen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="bg-white border border-slate-200 text-slate-800 rounded-3xl max-w-5xl w-full p-4 sm:p-6 shadow-2xl space-y-4 relative max-h-[92vh] flex flex-col my-auto mx-auto"
                    @click.away="showTreeModal = false">

                    <!-- Modal Header -->
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3.5 shrink-0">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 font-black flex items-center justify-center text-lg border border-emerald-100 shadow-2xs">
                                🌳
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                    {{ __('messages.family_tree_title') }}</h3>
                                <p class="text-xs text-slate-500 font-medium">{{ __('messages.family_tree_subtitle') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="window.print()"
                                class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <span>🖨️</span> {{ __('messages.print_family_tree') ?? 'Print Family Tree' }}
                            </button>
                            <button @click="showTreeModal = false"
                                class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs font-bold transition cursor-pointer">
                                ✕
                            </button>
                        </div>
                    </div>

                    @php
                        $wives = $family->filter(fn($m) => in_array($m->relationship, ['Wife', 'Spouse', 'પત્ની']));
                        $husbands = $family->filter(fn($m) => in_array($m->relationship, ['Husband', 'Spouse', 'પતિ']));
                        $spouses = $wives->concat($husbands);
                        $sons = $family->filter(fn($m) => in_array($m->relationship, ['Son', 'Son (દીકરો)', 'દીકરો']));
                        $daughters = $family->filter(fn($m) => in_array($m->relationship, ['Daughter', 'Daughter (દીકરી)', 'દીકરી']));
                        $children = $sons->concat($daughters);
                        $inlaws = $family->filter(fn($m) => in_array($m->relationship, ['Daughter-in-law', 'Son-in-law', 'વહુ', 'જમાઈ']));
                        $parents = $family->filter(fn($m) => in_array($m->relationship, ['Father', 'પિતા']))
                            ->concat($family->filter(fn($m) => in_array($m->relationship, ['Mother', 'માતા'])));
                        $grandchildren = $family->filter(fn($m) => in_array($m->relationship, ["Grandson (Son's Son)", "Granddaughter (Son's Daughter)", "Grandson (Daughter's Son)", "Granddaughter (Daughter's Daughter)", 'પૌત્ર', 'પૌત્રી', 'દોહિત્ર', 'દોહિત્રી']));
                        $others = $family->reject(
                            fn($m) =>
                            in_array($m->relationship, ['Wife', 'Husband', 'Spouse', 'પત્ની', 'પતિ', 'Son', 'Daughter', 'Son (દીકરો)', 'Daughter (દીકરી)', 'દીકરો', 'દીકરી', 'Daughter-in-law', 'Son-in-law', 'વહુ', 'જમાઈ', "Grandson (Son's Son)", "Granddaughter (Son's Daughter)", "Grandson (Daughter's Son)", "Granddaughter (Daughter's Daughter)", 'પૌત્ર', 'પૌત્રી', 'દોહિત્ર', 'દોહિત્રી', 'Father', 'Mother', 'પિતા', 'માતા'])
                        );
                    @endphp

                    <!-- SCROLLABLE CANVAS FOR CONNECTED TREE -->
                    <div class="overflow-x-auto overflow-y-auto flex-1 p-3 sm:p-6 bg-slate-50/50 rounded-2xl border border-slate-100">
                        <div class="w-max min-w-full flex flex-col items-center py-2">

                            <!-- PARENTS (above the member) -->
                            @if($parents->count() > 0)
                                <div class="flex flex-col items-center">
                                    <div class="flex items-center justify-center space-x-2 sm:space-x-3">
                                        @foreach($parents as $par)
                                            @if(!$loop->first)
                                                <div class="flex items-center space-x-1 px-1">
                                                    <div style="width: 14px; height: 2.5px; background-color: #f43f5e;"></div>
                                                    <span class="text-xs">💖</span>
                                                    <div style="width: 14px; height: 2.5px; background-color: #f43f5e;"></div>
                                                </div>
                                            @endif
                                            <div
                                                class="bg-amber-50/90 border-2 border-amber-300 rounded-2xl px-3.5 py-2 text-center shadow-sm min-w-[130px] sm:min-w-[155px] max-w-[200px]">
                                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug break-words flex items-center justify-center gap-1.5"
                                                    title="{{ $par->name }}">
                                                    <span>{{ in_array($par->relationship, ['Father', 'પિતા']) ? '👴' : '👵' }}</span>
                                                    {{ $par->name }}
                                                </h4>
                                                <span
                                                    class="text-[9px] font-bold text-amber-700 bg-white px-2 py-0.5 rounded-md border border-amber-200 inline-block mt-1 shadow-2xs">
                                                    {{ $par->relationship }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- Stem down to the member -->
                                    <div style="width: 2.5px; height: 18px; background-color: #64748b;"></div>
                                </div>
                            @endif

                            <!-- PRIMARY MEMBER & SPOUSE -->
                            <div class="flex flex-col items-center">
                                <div class="flex items-center justify-center space-x-2 sm:space-x-3">
                                    <!-- Primary Member Card -->
                                    <div
                                        class="bg-primary-50/90 border-2 border-primary-400 rounded-2xl px-3.5 py-2 text-center shadow-sm min-w-[130px] sm:min-w-[155px] max-w-[200px]">
                                        <h4 class="text-xs sm:text-sm font-black text-slate-900 leading-snug break-words flex items-center justify-center gap-1.5"
                                            title="{{ $user->name }}">
                                            <svg class="w-3.5 h-3.5 text-primary-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>{{ $user->name }}</span>
                                        </h4>
                                        <span
                                            class="text-[9px] font-bold text-primary-700 bg-white px-2 py-0.5 rounded-md border border-primary-200 inline-block mt-1 shadow-2xs">
                                            Member (#{{ sprintf('%05d', $user->id) }})
                                        </span>
                                    </div>

                                    @foreach($spouses as $spouse)
                                        <!-- Marriage Line Connection -->
                                        <div class="flex items-center space-x-1 px-1">
                                            <div style="width: 14px; height: 2.5px; background-color: #f43f5e;"></div>
                                            <span class="text-xs">💖</span>
                                            <div style="width: 14px; height: 2.5px; background-color: #f43f5e;"></div>
                                        </div>

                                        <!-- Spouse Card -->
                                        <div
                                            class="bg-rose-50/90 border-2 border-rose-300 rounded-2xl px-3.5 py-2 text-center shadow-sm min-w-[130px] sm:min-w-[155px] max-w-[200px]">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug break-words flex items-center justify-center gap-1.5"
                                                title="{{ $spouse->name }}">
                                                <span>{{ in_array($spouse->relationship, ['Husband', 'Spouse', 'પતિ']) && $spouse->gender != 'Female' ? '👨‍💼' : '👩' }}</span>
                                                {{ $spouse->name }}
                                            </h4>
                                            <span
                                                class="text-[9px] font-bold text-rose-700 bg-white px-2 py-0.5 rounded-md border border-rose-200 inline-block mt-1 shadow-2xs">
                                                {{ $spouse->relationship }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- CHILDREN (HORIZONTAL BRANCHING TREE WITH DIRECT DEPENDENTS) -->
                            @if($children->count() > 0)
                                <div class="flex flex-col items-center w-full relative mt-1">
                                    <!-- Vertical Stem from Primary Member touching Children Branch Line -->
                                    <div style="width: 2.5px; height: 18px; background-color: #64748b;"></div>

                                    <div class="flex justify-center items-start w-full relative">
                                        @foreach($children as $child)
                                            @php
                                                $childDependents = $family->filter(fn($m) => $m->parent_id == $child->id);
                                            @endphp
                                            <div class="flex flex-col items-center relative px-2 sm:px-3">
                                                <!-- Bold Horizontal Connector Segment across Children -->
                                                @if($children->count() > 1)
                                                    @if(!$loop->first)
                                                        <div class="absolute top-0 left-0 w-1/2"
                                                            style="height: 2.5px; background-color: #64748b;"></div>
                                                    @endif
                                                    @if(!$loop->last)
                                                        <div class="absolute top-0 right-0 w-1/2"
                                                            style="height: 2.5px; background-color: #64748b;"></div>
                                                    @endif
                                                @endif

                                                <!-- Vertical Stem to Child Card -->
                                                <div class="z-10" style="width: 2.5px; height: 14px; background-color: #64748b;"></div>

                                                <div
                                                    class="bg-indigo-50/80 border-2 border-indigo-300 rounded-2xl px-3 py-2 text-center shadow-sm min-w-[125px] sm:min-w-[145px] max-w-[185px] z-10">
                                                    <h5 class="text-xs font-bold text-slate-900 leading-snug break-words flex items-center justify-center gap-1.5"
                                                        title="{{ $child->name }}">
                                                        <span>{{ $child->gender == 'Female' ? '👧' : '👦' }}</span> {{ $child->name }}
                                                    </h5>
                                                    <span
                                                        class="text-[9px] font-bold text-indigo-700 bg-white px-2 py-0.5 rounded-md border border-indigo-200 inline-block mt-1 shadow-2xs">
                                                        {{ $child->relationship }}
                                                    </span>
                                                    @if($child->dob)
                                                        <p class="text-[9px] font-medium text-slate-500 mt-1 border-t border-indigo-100/80 pt-1">
                                                            DOB: {{ date('d-M-Y', strtotime($child->dob)) }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <!-- Render Dependents Directly Under Their Specified Parent -->
                                                @if($childDependents->count() > 0)
                                                    <div class="flex flex-col items-center w-full relative mt-1">
                                                        <div style="width: 2.5px; height: 16px; background-color: #64748b;"></div>

                                                        <div class="flex justify-center items-start relative w-full">
                                                            @foreach($childDependents as $dep)
                                                                <div class="flex flex-col items-center relative px-1.5 sm:px-2">
                                                                    @if($childDependents->count() > 1)
                                                                        @if(!$loop->first)
                                                                            <div class="absolute top-0 left-0 w-1/2"
                                                                                style="height: 2.5px; background-color: #64748b;"></div>
                                                                        @endif
                                                                        @if(!$loop->last)
                                                                            <div class="absolute top-0 right-0 w-1/2"
                                                                                style="height: 2.5px; background-color: #64748b;"></div>
                                                                        @endif
                                                                    @endif
                                                                    <div class="z-10"
                                                                        style="width: 2.5px; height: 12px; background-color: #64748b;"></div>

                                                                    <div
                                                                        class="bg-amber-50/80 border border-amber-300 rounded-xl px-2.5 py-1.5 text-center shadow-sm min-w-[115px] sm:min-w-[135px] max-w-[170px] z-10">
                                                                        <h6 class="text-[11px] font-bold text-slate-900 leading-snug break-words flex items-center justify-center gap-1"
                                                                            title="{{ $dep->name }}">
                                                                            <span>🌟</span> {{ $dep->name }}
                                                                        </h6>
                                                                        <span
                                                                            class="text-[8px] font-bold text-amber-800 bg-white px-1.5 py-0.5 rounded border border-amber-200 inline-block mt-0.5">
                                                                            {{ $dep->relationship }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- UNASSIGNED GRANDCHILDREN & IN-LAWS (If parent_id is not set) -->
                            @php
                                $unassignedGrandchildren = $inlaws->concat($grandchildren)->filter(fn($m) => !$m->parent_id);
                            @endphp

                            @if($unassignedGrandchildren->count() > 0)
                                <div class="flex flex-col items-center w-full relative mt-1">
                                    <div style="width: 2.5px; height: 16px; background-color: #64748b;"></div>

                                    <div class="flex justify-center items-start relative w-full">
                                        @foreach($unassignedGrandchildren as $gc)
                                            <div class="flex flex-col items-center relative px-1.5 sm:px-2">
                                                @if($unassignedGrandchildren->count() > 1)
                                                    @if(!$loop->first)
                                                        <div class="absolute top-0 left-0 w-1/2"
                                                            style="height: 2.5px; background-color: #64748b;"></div>
                                                    @endif
                                                    @if(!$loop->last)
                                                        <div class="absolute top-0 right-0 w-1/2"
                                                            style="height: 2.5px; background-color: #64748b;"></div>
                                                    @endif
                                                @endif

                                                <div class="z-10" style="width: 2.5px; height: 12px; background-color: #64748b;"></div>

                                                <div
                                                    class="bg-amber-50/80 border border-amber-300 rounded-xl px-2.5 py-1.5 text-center shadow-sm min-w-[115px] sm:min-w-[135px] max-w-[170px] z-10">
                                                    <h6 class="text-[11px] font-bold text-slate-900 leading-snug break-words flex items-center justify-center gap-1"
                                                        title="{{ $gc->name }}">
                                                        <span>🌟</span> {{ $gc->name }}
                                                    </h6>
                                                    <span
                                                        class="text-[8px] font-bold text-amber-800 bg-white px-1.5 py-0.5 rounded border border-amber-200 inline-block mt-0.5">
                                                        {{ $gc->relationship }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- OTHER EXTENDED MEMBERS -->
                            @if($others->count() > 0)
                                <div class="border-t border-slate-200 pt-3 mt-3 w-full flex flex-col items-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @foreach($others as $ot)
                                            <span
                                                class="text-[10px] font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                                {{ $ot->name }} ({{ $ot->relationship }})
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection