@extends('layouts.member')

@section('page_title', 'Add Family Member')

@section('content')
@php
    // DOB picker limits: Father / Mother are older than the member, everyone else younger
    $memberDob = auth()->user()->memberProfile?->dob;
    $dobChildMin = $memberDob ? \Carbon\Carbon::parse($memberDob)->addDay()->format('Y-m-d') : '';
    $dobChildMax = \Carbon\Carbon::yesterday()->format('Y-m-d');
    $dobParentMax = $memberDob ? \Carbon\Carbon::parse($memberDob)->subDay()->format('Y-m-d') : $dobChildMax;
@endphp
<div class="max-w-xl bg-white border border-slate-100 rounded-2xl p-5 sm:p-6 shadow-sm">
    <h2 class="text-sm sm:text-base font-black text-slate-900 pb-2.5 border-b border-slate-100 flex items-center gap-2 mb-4">
        👨‍👩‍👧‍👦 {{ __('messages.add_family_member') }}
    </h2>
    
    <!-- Errors -->
    @if ($errors->any())
        <div class="mb-4 p-3 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl">
            <ul class="list-disc pl-4 text-xs font-semibold space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('member.family.store') }}" class="grid grid-cols-1 gap-4"
        x-data="{ 
            rel: '{{ old('relationship') }}', 
            gender: '{{ old('gender', 'Male') }}', 
            marital_status: '{{ old('marital_status', 'Unmarried') }}',
            isParentRel(value) {
                return ['Father', 'Mother', 'પિતા', 'માતા'].includes(value);
            },
            onRelChange(val) {
                this.rel = val;
                if (['Wife', 'Husband', 'Daughter-in-law', 'Son-in-law', 'Spouse', 'પત્ની', 'પતિ', 'વહુ', 'જમાઈ', 'Father', 'Mother', 'પિતા', 'માતા'].includes(val)) {
                    this.marital_status = 'Married';
                }
                if (['Husband', 'Son-in-law', 'Son', 'પતિ', 'જમાઈ', 'દીકરો', 'Father', 'પિતા'].includes(val)) {
                    this.gender = 'Male';
                } else if (['Wife', 'Daughter-in-law', 'Daughter', 'પત્ની', 'વહુ', 'દીકરી', 'Mother', 'માતા'].includes(val)) {
                    this.gender = 'Female';
                }
            }
        }">
        @csrf
        
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-700 block">{{ __('messages.name') }} <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter Full Name" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.relationship') }} <span class="text-rose-500">*</span></label>
                @php
                    $userGender = $profile->gender ?? 'Male';
                    $isFemaleMember = strtolower($userGender) === 'female';
                    $hasExistingSpouse = isset($family) && $family->contains(fn($m) => in_array($m->relationship, ['Wife', 'Husband', 'Spouse', 'પત્ની', 'પતિ']));
                    $hasFather = isset($family) && $family->contains(fn($m) => in_array($m->relationship, ['Father', 'પિતા']));
                    $hasMother = isset($family) && $family->contains(fn($m) => in_array($m->relationship, ['Mother', 'માતા']));
                @endphp
                <select name="relationship" required @change="onRelChange($event.target.value)" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                    <option value="" disabled {{ old('relationship') ? '' : 'selected' }}>Select Relationship</option>
                    @if(!$hasExistingSpouse)
                        @if($isFemaleMember)
                            <option value="Husband" {{ old('relationship') == 'Husband' ? 'selected' : '' }}>{{ __('messages.rel_husband') }}</option>
                        @else
                            <option value="Wife" {{ old('relationship') == 'Wife' ? 'selected' : '' }}>{{ __('messages.rel_wife') }}</option>
                        @endif
                    @endif
                    @unless($hasFather)
                        <option value="Father" {{ old('relationship') == 'Father' ? 'selected' : '' }}>{{ __('messages.rel_father') }}</option>
                    @endunless
                    @unless($hasMother)
                        <option value="Mother" {{ old('relationship') == 'Mother' ? 'selected' : '' }}>{{ __('messages.rel_mother') }}</option>
                    @endunless
                    <option value="Son" {{ old('relationship') == 'Son' ? 'selected' : '' }}>{{ __('messages.rel_son') }}</option>
                    <option value="Daughter" {{ old('relationship') == 'Daughter' ? 'selected' : '' }}>{{ __('messages.rel_daughter') }}</option>
                    <option value="Daughter-in-law" {{ old('relationship') == 'Daughter-in-law' ? 'selected' : '' }}>{{ __('messages.rel_daughter_in_law') }}</option>
                    <option value="Son-in-law" {{ old('relationship') == 'Son-in-law' ? 'selected' : '' }}>{{ __('messages.rel_son_in_law') }}</option>
                    <option value="Grandson (Son's Son)" {{ old('relationship') == "Grandson (Son's Son)" ? 'selected' : '' }}>{{ __('messages.rel_grandson_sons_son') }}</option>
                    <option value="Granddaughter (Son's Daughter)" {{ old('relationship') == "Granddaughter (Son's Daughter)" ? 'selected' : '' }}>{{ __('messages.rel_granddaughter_sons_daughter') }}</option>
                    <option value="Grandson (Daughter's Son)" {{ old('relationship') == "Grandson (Daughter's Son)" ? 'selected' : '' }}>{{ __('messages.rel_grandson_daughters_son') }}</option>
                    <option value="Granddaughter (Daughter's Daughter)" {{ old('relationship') == "Granddaughter (Daughter's Daughter)" ? 'selected' : '' }}>{{ __('messages.rel_granddaughter_daughters_daughter') }}</option>
                    <option value="Other" {{ old('relationship') == 'Other' ? 'selected' : '' }}>{{ __('messages.rel_other') }}</option>
                </select>

                @php
                    $sons = isset($family) ? $family->filter(fn($m) => in_array($m->relationship, ['Son', 'Son (દીકરો)', 'દીકરો'])) : collect();
                    $daughters = isset($family) ? $family->filter(fn($m) => in_array($m->relationship, ['Daughter', 'Daughter (દીકરી)', 'દીકરી'])) : collect();
                @endphp

                <div x-show="['Grandson (Son\'s Son)', 'Granddaughter (Son\'s Daughter)', 'Daughter-in-law'].includes(rel)" class="space-y-1.5 mt-2">
                    <label class="text-xs font-bold text-primary-600 block">Select Parent Son / Husband</label>
                    <select name="parent_id" class="w-full text-xs font-semibold h-10 px-3 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                        <option value="">Select Son</option>
                        @foreach($sons as $s)
                            <option value="{{ $s->id }}" {{ old('parent_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="['Grandson (Daughter\'s Son)', 'Granddaughter (Daughter\'s Daughter)', 'Son-in-law'].includes(rel)" class="space-y-1.5 mt-2">
                    <label class="text-xs font-bold text-primary-600 block">Select Parent Daughter / Wife</label>
                    <select name="parent_id" class="w-full text-xs font-semibold h-10 px-3 bg-primary-50 border border-primary-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                        <option value="">Select Daughter</option>
                        @foreach($daughters as $d)
                            <option value="{{ $d->id }}" {{ old('parent_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.gender') }} <span class="text-rose-500">*</span></label>
                <select name="gender" x-model="gender" required class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.marital_status') }} <span class="text-rose-500">*</span></label>
                <select name="marital_status" x-model="marital_status" required class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                    <option value="Unmarried">Unmarried</option>
                    <option value="Married">Married</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.dob') }}</label>
                <input type="date" name="dob" value="{{ old('dob') }}" :min="isParentRel(rel) ? '' : '{{ $dobChildMin }}'" :max="isParentRel(rel) ? '{{ $dobParentMax }}' : '{{ $dobChildMax }}'" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.education') }}</label>
                <input type="text" name="education" value="{{ old('education') }}" placeholder="e.g. High School" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">{{ __('messages.occupation') }}</label>
                <input type="text" name="occupation" value="{{ old('occupation') }}" placeholder="e.g. Student" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-700 block">Phone Number (Optional, 10 Digits)</label>
            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="10-digit number" class="w-full text-xs font-semibold h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-end items-center space-x-3">
            <a href="{{ route('member.family.index') }}" class="px-5 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer">
                Save Member
            </button>
        </div>
    </form>
</div>
@endsection
