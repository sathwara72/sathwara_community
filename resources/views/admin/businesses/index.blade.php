@extends('layouts.admin')
@section('page_title', __('messages.business_directory'))
@section('content')
    @php
        $user = auth()->user();
        $userPerms = $user->permissions->pluck('name');
        $canAddBusiness = $user->hasRole('Administrator') || $userPerms->contains('businesses_manage') || $userPerms->contains('businesses_add');
        $canEditBusiness = $user->hasRole('Administrator') || $userPerms->contains('businesses_manage') || $userPerms->contains('businesses_edit');
        $canDeleteBusiness = $user->hasRole('Administrator') || $userPerms->contains('businesses_manage') || $userPerms->contains('businesses_delete');
    @endphp
    <div x-data="{
        activeTab: 'listings',
        detailsOpen: false, addCatOpen: false, editCatOpen: false,
        editCatName: '', editCatUrl: '',
        selectedBusiness: { name:'',category:'',owner:'',phone:'',whatsapp:'',email:'',website:'',description:'',address:'',logo:'',gallery:[] }
    }" class="space-y-4">

        {{-- Tab Bar --}}
        <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-4 border-b border-slate-100">
                <div class="flex items-center">
                    <button @click="activeTab='listings'"
                        :class="activeTab==='listings' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-b-2 border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex items-center space-x-2 px-3 py-3 text-xs font-bold transition-all duration-150 -mb-px">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ __('messages.business_listings') }}</span>
                        <span
                            :class="activeTab==='listings' ? 'bg-primary-50 text-primary-600' : 'bg-slate-100 text-slate-500'"
                            class="px-1.5 py-0.5 rounded text-[10px] font-extrabold">{{ $businesses->total() }}</span>
                    </button>

                    <button @click="activeTab='categories'"
                        :class="activeTab==='categories' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-b-2 border-transparent text-slate-400 hover:text-slate-600'"
                        class="flex items-center space-x-2 px-3 py-3 text-xs font-bold transition-all duration-150 -mb-px">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span>{{ __('messages.categories') }}</span>
                        <span
                            :class="activeTab==='categories' ? 'bg-primary-50 text-primary-600' : 'bg-slate-100 text-slate-500'"
                            class="px-1.5 py-0.5 rounded text-[10px] font-extrabold">{{ $categories->count() }}</span>
                    </button>
                </div>

                <div x-show="activeTab==='categories'" x-cloak>
                    <button @click="addCatOpen=true"
                        class="inline-flex items-center px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('messages.add_category') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- LISTINGS TAB --}}
        <div x-show="activeTab==='listings'" x-cloak class="space-y-3">
            <!-- Single Integrated Toolbar Line (All in 1 Row) -->
            <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-xs">
                <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-2.5">
                    <form method="GET" action="{{ route('admin.businesses.index') }}"
                        class="flex items-center gap-2 flex-grow min-w-0">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <!-- Search Input -->
                        <div class="relative flex-1 min-w-0 max-w-sm">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('messages.search_business_placeholder') }}"
                                class="h-8.5 w-full text-xs font-semibold pl-8 pr-7 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:outline-none transition-all">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            @if(request()->filled('search'))
                                <a href="{{ route('admin.businesses.index', request()->except('search')) }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-xs" title="Clear search">&times;</a>
                            @endif
                        </div>

                        <!-- Search Button -->
                        <button type="submit"
                            class="h-8.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-colors shrink-0 whitespace-nowrap">
                            {{ __('messages.search') }}
                        </button>

                        <!-- Category Select Dropdown -->
                        <div class="shrink-0 w-36 sm:w-44">
                            <select name="category_id" onchange="this.form.submit()"
                                class="h-8.5 w-full text-xs font-semibold px-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:outline-none transition-all truncate">
                                <option value="">{{ __('messages.all_categories') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <div class="flex items-center gap-2 shrink-0 justify-end flex-nowrap overflow-x-auto">
                        <!-- Status Filter Tabs -->
                        <div
                            class="flex items-center p-0.5 rounded-lg bg-slate-100/80 border border-slate-200/60 shrink-0 overflow-x-auto">
                            <a href="{{ route('admin.businesses.index', array_merge(request()->except(['status', 'page']))) }}"
                                class="px-2 py-1 rounded-md text-xs font-bold transition-all whitespace-nowrap {{ empty(request('status')) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('messages.all') }} ({{ $totalCount }})
                            </a>

                            <a href="{{ route('admin.businesses.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}"
                                class="px-2 py-1 rounded-md text-xs font-bold transition-all whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 hover:bg-amber-100/50' }}">
                                {{ __('messages.pending') }} ({{ $pendingCount }})
                            </a>

                            <a href="{{ route('admin.businesses.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}"
                                class="px-2 py-1 rounded-md text-xs font-bold transition-all whitespace-nowrap {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-emerald-100/50' }}">
                                {{ __('messages.approved') }} ({{ $approvedCount }})
                            </a>

                            <a href="{{ route('admin.businesses.index', array_merge(request()->except('page'), ['status' => 'rejected'])) }}"
                                class="px-2 py-1 rounded-md text-xs font-bold transition-all whitespace-nowrap {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-700 hover:bg-rose-100/50' }}">
                                {{ __('messages.rejected') }} ({{ $rejectedCount }})
                            </a>
                        </div>

                        @if($canEditBusiness)
                            @include('admin.partials.fee_settings_popup', [
                                'title' => 'Business Fees',
                                'action' => route('admin.fees.business'),
                                'fields' => [
                                    ['name' => 'business_registration_fee', 'label' => 'New business registration', 'value' => \App\Models\Setting::get('business_registration_fee', '500'), 'help' => 'Free: businesses register without paying (still needs approval).'],
                                    ['name' => 'business_renewal_fee', 'label' => 'Yearly renewal', 'value' => \App\Models\Setting::get('business_renewal_fee', \App\Models\Setting::get('business_registration_fee', '500')), 'help' => 'Free: businesses renew from their panel with one click.'],
                                ],
                            ])
                        @endif

                        <!-- Export CSV Button -->
                        <a href="{{ route('admin.businesses.export', request()->all()) }}"
                            class="h-8.5 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-lg border border-emerald-200/60 shadow-xs transition-colors inline-flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('messages.export_excel') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl overflow-x-auto shadow-sm">
                <table class="w-full text-left border-collapse min-w-full">
                    <thead>
                        <tr
                            class="bg-slate-50 text-xs font-black uppercase text-slate-700 tracking-wider border-b border-slate-200 whitespace-nowrap">
                            <th class="py-3 px-3">{{ __('messages.business') }}</th>
                            <th class="py-3 px-3">{{ __('messages.owner') }}</th>
                            <th class="py-3 px-3">{{ __('messages.phone') }}</th>
                            <th class="py-3 px-3">{{ __('messages.category') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('messages.status') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('messages.subscription_dates') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('messages.membership_status') }}</th>
                            <th class="py-3 px-3 text-right shrink-0">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                        @forelse($businesses as $b)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center space-x-2.5 gap-2">
                                        <img class="w-8 h-8 rounded-lg object-cover border border-slate-100 bg-slate-50 shrink-0"
                                            src="{{ str_starts_with($b->logo_path, 'http') ? $b->logo_path : asset('storage/' . $b->logo_path) }}"
                                            alt="">
                                        <div class="min-w-0">
                                            <p class="text-slate-900 font-bold leading-tight truncate max-w-[140px] sm:max-w-[180px]">{{ $b->business_name }}</p>
                                            <p class="text-xs text-slate-500 font-medium truncate max-w-[140px] sm:max-w-[180px] mt-0.5">
                                                {{ $b->address }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-slate-800 font-bold whitespace-nowrap">{{ $b->owner_name }}</td>
                                <td class="py-2.5 px-3 text-slate-700 whitespace-nowrap text-xs font-semibold">{{ $b->phone }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap"><span
                                        class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-bold inline-block border border-slate-200/60">{{ $b->category?->name ?? 'N/A' }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($b->status == 'approved') <span
                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-lg font-extrabold text-xs whitespace-nowrap">{{ __('messages.approved') }}</span>
                                    @elseif($b->status == 'rejected') <span
                                        class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200/60 rounded-lg font-extrabold text-xs whitespace-nowrap">{{ __('messages.rejected') }}</span>
                                    @else <span
                                        class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200/60 rounded-lg font-extrabold text-xs whitespace-nowrap">{{ __('messages.pending') }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($b->approved_at)
                                        <div class="text-xs leading-tight space-y-1">
                                            <div class="font-bold text-slate-800 text-left sm:text-center">
                                                <span class="text-slate-400 font-semibold text-[11px]">Start:</span> {{ $b->approved_at->format('d M Y') }}
                                            </div>
                                            <div class="font-bold text-slate-700 text-left sm:text-center">
                                                <span class="text-slate-400 font-semibold text-[11px]">Expiry:</span> {{ $b->approved_at->addYear()->format('d M Y') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($b->membership_status == 'active') <span
                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-lg font-extrabold text-xs whitespace-nowrap">{{ __('messages.active') }}</span>
                                    @else <span
                                        class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200/60 rounded-lg font-extrabold text-xs whitespace-nowrap">{{ __('messages.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-right shrink-0">
                                    <div class="flex justify-end items-center space-x-1.5 gap-1.5">
                                        <a href="{{ route('admin.businesses.show', $b->id) }}"
                                            class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"
                                            title="{{ __('messages.view_details') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                                            </svg>
                                        </a>
                                        @if($canEditBusiness)
                                            <a href="{{ route('admin.businesses.edit', $b->id) }}"
                                                class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors"
                                                title="{{ __('messages.edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endif
                                        @if($b->status !== 'approved' && $canEditBusiness)
                                            <form method="POST" action="{{ route('admin.businesses.approve', $b->id) }}">@csrf
                                                <button
                                                    class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                                    title="{{ __('messages.approve') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($b->status === 'pending' && $canEditBusiness)
                                            <a href="{{ route('admin.businesses.show', ['id' => $b->id, 'reject' => 1]) }}"
                                                    class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"
                                                    title="{{ __('messages.reject') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </a>
                                        @endif
                                        @if($b->status === 'approved' && $b->membership_status === 'active' && $canEditBusiness)
                                            <form method="POST" action="{{ route('admin.businesses.deactivate', $b->id) }}">@csrf
                                                <button
                                                    class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"
                                                    title="Mark Inactive">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($b->status === 'approved' && $b->membership_status === 'inactive' && $canEditBusiness)
                                            <form method="POST" action="{{ route('admin.businesses.activate', $b->id) }}">@csrf
                                                <button
                                                    class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                                    title="Mark Active">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canDeleteBusiness)
                                            <button
                                                @click="$dispatch('confirm-delete',{action:'{{ route('admin.businesses.destroy', $b->id) }}',message:'{{ __('messages.delete_confirm_business', ['name' => $b->business_name]) }}'})"
                                                class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"
                                                title="{{ __('messages.delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 text-sm font-medium">
                                    {{ __('messages.no_businesses_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $businesses->links() }}</div>
        </div>

        {{-- CATEGORIES TAB --}}
        <div x-show="activeTab==='categories'" x-cloak class="space-y-3">
            @if($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-800 font-semibold">
                    <ul class="list-disc pl-4 space-y-0.5">@foreach($errors->all() as $e)
                    <li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @forelse($categories as $cat)
                    <div
                        class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm hover:shadow-md hover:border-primary-100 transition-all group">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button
                                    @click="editCatName='{{ addslashes($cat->name) }}';editCatUrl='{{ route('admin.businesses.categories.update', $cat->id) }}';editCatOpen=true"
                                    class="w-6 h-6 flex items-center justify-center rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="$dispatch('confirm-delete',{action:'{{ route('admin.businesses.categories.destroy', $cat->id) }}',message:'{{ __('messages.delete_confirm_category', ['name' => $cat->name]) }}'})"
                                    class="w-6 h-6 flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $cat->name }}</p>
                        <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-600 font-bold">{{ $cat->businesses_count }}
                                {{ __('messages.businesses') }}</span>
                            @if($cat->businesses_count > 0)
                                <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                            @else
                                <span class="w-2 h-2 bg-slate-200 rounded-full"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-5 py-12 text-center text-slate-400 text-xs bg-white border border-slate-100 rounded-xl">
                        {{ __('messages.no_categories_yet') }}</div>
                @endforelse
            </div>
        </div>



        {{-- Add Category Modal --}}
        <template x-teleport="body">
            <div x-show="addCatOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm"
                x-transition x-cloak>
                <div class="bg-white rounded-xl max-w-sm w-full p-5 border border-slate-100 shadow-xl"
                    @click.away="addCatOpen=false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-xs font-extrabold text-slate-950">{{ __('messages.new_category') }}</h3>
                        <button @click="addCatOpen=false" class="text-slate-400 hover:text-slate-600"><svg class="w-4 h-4"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <form method="POST" action="{{ route('admin.businesses.categories.store') }}" class="space-y-3">
                        @csrf
                        <div class="space-y-0.5">
                            <label
                                class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.category_name') }}</label>
                            <input type="text" name="name" required placeholder="e.g. Electrical Services"
                                class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                        </div>
                        <div class="flex justify-end space-x-2 pt-1">
                            <button type="button" @click="addCatOpen=false"
                                class="px-3 py-1.5 border border-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-50">{{ __('messages.cancel') }}</button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-sm">{{ __('messages.create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- Edit Category Modal --}}
        <template x-teleport="body">
            <div x-show="editCatOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm"
                x-transition x-cloak>
                <div class="bg-white rounded-xl max-w-sm w-full p-5 border border-slate-100 shadow-xl"
                    @click.away="editCatOpen=false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-xs font-extrabold text-slate-950">{{ __('messages.edit_category') }}</h3>
                        <button @click="editCatOpen=false" class="text-slate-400 hover:text-slate-600"><svg class="w-4 h-4"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <form method="POST" :action="editCatUrl" class="space-y-3">
                        @csrf
                        <div class="space-y-0.5">
                            <label
                                class="text-[10px] font-bold text-slate-500 uppercase">{{ __('messages.category_name') }}</label>
                            <input type="text" name="name" required x-model="editCatName"
                                placeholder="e.g. Electrical Services"
                                class="w-full text-xs font-semibold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500">
                        </div>
                        <div class="flex justify-end space-x-2 pt-1">
                            <button type="button" @click="editCatOpen=false"
                                class="px-3 py-1.5 border border-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-50">{{ __('messages.cancel') }}</button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-sm">{{ __('messages.save_changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
@endsection