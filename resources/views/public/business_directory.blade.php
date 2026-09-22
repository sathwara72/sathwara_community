@extends('layouts.public')

@section('content')
@include('partials.page_header', [
    'title' => __('messages.business_directory'),
    'subtitle' => __('messages.business_subtitle'),
    'breadcrumb' => __('messages.business_directory')
])

<!-- Search and Directory Grid -->
<section class="py-10 bg-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-24">
                <!-- Search & Category Filter Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/60 shadow-xs space-y-3">
                    <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">{{ __('messages.search_directory') }}</h3>
                    <form method="GET" action="{{ route('business.directory') }}" class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_company_owner') }}" 
                               class="w-full text-xs sm:text-[13px] font-bold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:ring-0">
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Mobile Category Dropdown (Visible only on mobile/tablet < lg) -->
                    <div class="block lg:hidden pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="mobile_category_select" class="text-[11px] font-black text-slate-700 uppercase tracking-wider">
                                {{ __('messages.categories') }} ({{ $categories->count() }})
                            </label>
                            @if(request('category'))
                                <a href="{{ route('business.directory', request()->only('search')) }}" 
                                   class="text-[11px] font-extrabold text-primary-600 hover:underline inline-flex items-center gap-0.5">
                                    <span>{{ __('messages.all_categories') }}</span>
                                    <span>&times;</span>
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <select id="mobile_category_select" 
                                    onchange="if (this.value) window.location.href = this.value;"
                                    class="w-full text-xs font-bold py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 focus:ring-0 appearance-none cursor-pointer">
                                <option value="{{ route('business.directory', request()->only('search')) }}" {{ !request('category') ? 'selected' : '' }}>
                                    {{ __('messages.all_categories') }} ({{ $categories->sum('businesses_count') }})
                                </option>
                                @foreach($categories as $cat)
                                    <option value="{{ route('business.directory', array_merge(request()->only('search'), ['category' => $cat->slug])) }}" 
                                            {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->businesses_count }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories (Desktop Sidebar Only) -->
                <div class="hidden lg:block bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/60 shadow-xs space-y-3" x-data="{ catFilter: '' }">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">{{ __('messages.categories') }}</h3>
                        <span class="text-[11px] font-extrabold text-slate-400">({{ $categories->count() }})</span>
                    </div>

                    @if($categories->count() > 6)
                        <div class="relative">
                            <input type="text" x-model="catFilter" placeholder="{{ __('messages.search_directory') }}..." 
                                   class="w-full text-[11px] font-bold pl-7 pr-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    @endif

                    <div class="flex flex-col space-y-1 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar" style="max-height: 300px; overflow-y: auto;">
                        <a href="{{ route('business.directory', request()->only('search')) }}" 
                           class="text-xs sm:text-[13px] font-bold px-3 py-1.5 rounded-xl flex justify-between items-center transition-all {{ !request('category') ? 'bg-primary-50 text-primary-600 font-black' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ __('messages.all_categories') }}</span>
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('business.directory', array_merge(request()->only('search'), ['category' => $cat->slug])) }}" 
                               x-show="!catFilter || '{{ strtolower(addslashes($cat->name)) }}'.includes(catFilter.toLowerCase())"
                               class="text-xs sm:text-[13px] font-bold px-3 py-1.5 rounded-xl flex justify-between items-center transition-all {{ request('category') == $cat->slug ? 'bg-primary-50 text-primary-600 font-black' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span class="truncate pr-2">{{ $cat->name }}</span>
                                <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md shrink-0 transition-all {{ request('category') == $cat->slug ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-slate-500' }}">{{ $cat->businesses_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Register & Business Login Callout -->
                <div class="bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 rounded-2xl p-5 text-white shadow-lg space-y-3.5 relative overflow-hidden border border-slate-800">
                    <!-- Ambient Glow Accents -->
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-primary-500/15 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -left-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="space-y-1 relative z-10">
                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-400 inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-pulse"></span>
                            {{ __('messages.community_network') }}
                        </span>
                        <h4 class="text-sm sm:text-base font-black tracking-tight text-white leading-snug">
                            {{ __('messages.add_your_business') }}
                        </h4>
                        <p class="text-[11px] text-slate-300 leading-relaxed font-medium">
                            {{ __('messages.promote_org') }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col space-y-2.5 pt-1 relative z-10">
                        <!-- Primary: Register Business -->
                        <a href="{{ route('register.business') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-primary-500/25 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>{{ __('messages.business_registration') }}</span>
                        </a>

                        <!-- Secondary: Business Login -->
                        <a href="{{ route('business.login') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/15 text-amber-300 hover:text-white font-bold text-xs rounded-xl border border-white/10 hover:border-amber-400/30 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 backdrop-blur-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>{{ __('messages.business_login') }}</span>
                            <span class="text-amber-400/80 text-[11px]">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Businesses Grid (Compact Vertical Cards Grid) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse($businesses as $biz)
                        <div class="group bg-white border border-slate-200/80 rounded-2xl p-4 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between space-y-3">
                            <div>
                                <!-- Top Row: Logo + Category Badge (Zero Overlapping) -->
                                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-200/80 shadow-xs shrink-0 p-0.5 flex items-center justify-center overflow-hidden">
                                        <img class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" 
                                             src="{{ str_starts_with($biz->logo_path, 'http') ? $biz->logo_path : asset('storage/' . $biz->logo_path) }}" 
                                             alt="{{ $biz->business_name }}">
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        <span class="text-[11px] font-extrabold text-primary-700 bg-primary-50 border border-primary-100 px-2.5 py-0.5 rounded-md uppercase tracking-wider">
                                            {{ $biz->category?->name ?? __('messages.general') }}
                                        </span>
                                     
                                          
                                    
                                    </div>
                                </div>

                                <!-- Info Content -->
                                <div class="pt-3 space-y-2">
                                    <div>
                                        <a href="{{ route('business.details', $biz->id) }}" class="block">
                                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-primary-600 transition-colors line-clamp-1" title="{{ $biz->business_name }}">
                                                {{ $biz->business_name }}
                                            </h3>
                                        </a>
                                        <p class="text-xs text-slate-500 font-semibold truncate mt-1">
                                            {{ __('messages.owner') }}: <span class="text-slate-800 font-bold">{{ $biz->owner_name }}</span>
                                            {{-- @if($biz->member_id)
                                                <span class="mx-1 text-slate-300">•</span> {{ __('messages.member_id') }}: <span class="text-slate-800 font-bold">{{ $biz->member_id }}</span>
                                            @endif --}}
                                        </p>
                                        {{-- @if($biz->approved_at)
                                            <p class="text-[11px] text-slate-400 font-semibold mt-1">
                                                {{ __('messages.purchase') }}: <span class="text-slate-600">{{ $biz->approved_at->format('d M Y') }}</span>
                                                <span class="mx-1 text-slate-300">•</span>
                                                {{ __('messages.expires') }}: <span class="text-slate-600">{{ $biz->approved_at->addYear()->format('d M Y') }}</span>
                                            </p>
                                        @endif --}}
                                    </div>

                                    <p class="text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed min-h-[36px]">
                                        {{ $biz->description ?? __('messages.no_business_description') }}
                                    </p>

                                    <!-- Address & Contact Info Badges -->
                                    <div class="space-y-1 pt-2.5 text-xs text-slate-600 font-bold border-t border-slate-100">
                                        <div class="flex items-center gap-1.5 truncate" title="{{ $biz->area?->name ?? $biz->address }}">
                                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate">{{ $biz->area?->name ?? $biz->address }}</span>
                                        </div>
                                        @if($biz->phone)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <a href="tel:{{ $biz->phone }}" class="hover:text-primary-600 transition-colors">{{ $biz->phone }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="pt-2.5 border-t border-slate-100 flex items-center gap-2">
                                @if($biz->phone)
                                    <a href="tel:{{ $biz->phone }}" class="flex-1 text-center py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                                        {{ __('messages.call_now') }}
                                    </a>
                                @endif
                                <a href="{{ route('business.details', $biz->id) }}" class="flex-1 text-center py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors shadow-2xs">
                                    {{ __('messages.view_details') }} &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 text-slate-500 font-bold bg-white rounded-2xl border border-slate-200/60 shadow-xs">
                            {{ __('messages.no_matching_businesses') }}
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $businesses->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
