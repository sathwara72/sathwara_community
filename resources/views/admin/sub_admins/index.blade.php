@extends('layouts.admin')

@section('page_title', __('messages.sub_admins_access_management'))

@section('content')
<div class="space-y-4" x-data="{ 
    search: '',
    showAddModal: {{ $errors->any() ? 'true' : 'false' }}, 
    showEditModal: false, 
    showEventsModal: false,
    eventsSearch: '',
    editAdmin: {},
    eventsAdmin: {},
    openEdit(admin) {
        // Deep copy permissions array so edits are reactive
        this.editAdmin = JSON.parse(JSON.stringify(admin));
        if (!this.editAdmin.permissions) this.editAdmin.permissions = [];
        this.showEditModal = true;
    },
    openEvents(admin) {
        this.eventsAdmin = JSON.parse(JSON.stringify(admin));
        if (!this.eventsAdmin.permissions) this.eventsAdmin.permissions = [];
        
        const allEventIds = @json($allEvents->pluck('id'));
        const hasFull = this.eventsAdmin.permissions.includes('events_manage');
        const hasView = this.eventsAdmin.permissions.includes('events_view');
        const hasEdit = this.eventsAdmin.permissions.includes('events_edit');

        allEventIds.forEach(id => {
            if (hasFull || hasView) {
                if (!this.eventsAdmin.permissions.includes('event_view_' + id)) {
                    this.eventsAdmin.permissions.push('event_view_' + id);
                }
            }
            if (hasFull || hasEdit) {
                if (!this.eventsAdmin.permissions.includes('event_edit_' + id)) {
                    this.eventsAdmin.permissions.push('event_edit_' + id);
                }
            }
        });

        this.showEventsModal = true;
    },
    toggleSelectAllEvents(e) {
        const allEventIds = @json($allEvents->pluck('id'));
        if (!this.eventsAdmin.permissions) this.eventsAdmin.permissions = [];
        if (e.target.checked) {
            allEventIds.forEach(id => {
                if (!this.eventsAdmin.permissions.includes('event_view_' + id)) {
                    this.eventsAdmin.permissions.push('event_view_' + id);
                }
                if (!this.eventsAdmin.permissions.includes('event_edit_' + id)) {
                    this.eventsAdmin.permissions.push('event_edit_' + id);
                }
            });
        } else {
            allEventIds.forEach(id => {
                this.eventsAdmin.permissions = this.eventsAdmin.permissions.filter(p => 
                    p !== ('event_view_' + id) && p !== ('event_edit_' + id)
                );
            });
        }
    },
    isAllEventsSelected() {
        if (!this.eventsAdmin || !this.eventsAdmin.permissions) return false;
        const allEventIds = @json($allEvents->pluck('id'));
        if (allEventIds.length === 0) return false;
        return allEventIds.every(id => 
            this.eventsAdmin.permissions.includes('event_view_' + id) && 
            this.eventsAdmin.permissions.includes('event_edit_' + id)
        );
    },
    toggleFullAccess(permKey, modPrefix, e) {
        if (!this.editAdmin.permissions) this.editAdmin.permissions = [];
        if (e.target.checked) {
            if (!this.editAdmin.permissions.includes(permKey)) this.editAdmin.permissions.push(permKey);
            if (permKey.endsWith('_view')) return; // view-only modules have no action rights
            [modPrefix + '_view', modPrefix + '_add', modPrefix + '_edit', modPrefix + '_delete'].forEach(p => {
                if (!this.editAdmin.permissions.includes(p)) this.editAdmin.permissions.push(p);
            });
        } else {
            this.editAdmin.permissions = this.editAdmin.permissions.filter(p => 
                p !== permKey && !p.startsWith(modPrefix + '_')
            );
        }
    },
    toggleActionAccess(permKey, modPrefix, actionPerm, e) {
        if (!this.editAdmin.permissions) this.editAdmin.permissions = [];
        if (e.target.checked) {
            if (!this.editAdmin.permissions.includes(actionPerm)) this.editAdmin.permissions.push(actionPerm);
            // Check if all 4 action permissions are checked, if so, also check Full Access
            const allChecked = [modPrefix + '_view', modPrefix + '_add', modPrefix + '_edit', modPrefix + '_delete'].every(p => this.editAdmin.permissions.includes(p));
            if (allChecked && !this.editAdmin.permissions.includes(permKey)) {
                this.editAdmin.permissions.push(permKey);
            }
        } else {
            // Remove action permission AND remove Full Access permKey
            this.editAdmin.permissions = this.editAdmin.permissions.filter(p => p !== actionPerm && p !== permKey);
        }
    }
}">
    <!-- Top Action Bar (Search Filter & Add Button) -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-3 sm:p-4 rounded-xl border border-slate-100 shadow-xs">
        <div class="relative w-full sm:w-72">
            <input type="text" x-model="search" placeholder="{{ __('messages.search_placeholder') ?? 'Search sub-admins...' }}" 
                   class="h-9 w-full text-xs font-semibold pl-9 pr-8 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <button type="button" x-show="search" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-sm" title="Clear search">
                &times;
            </button>
        </div>

        <button type="button" @click="showAddModal = true" 
                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-xs transition-transform hover:-translate-y-0.5 inline-flex items-center gap-1.5 shrink-0">
            <span>+ {{ __('messages.add_sub_admin') }}</span>
        </button>
    </div>

    <!-- Sub-Admins Table -->
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-xs font-black uppercase text-slate-700 tracking-wider border-b border-slate-200">
                    <th class="py-3 px-4">{{ __('messages.sub_admin_user') }}</th>
                    <th class="py-3 px-4">{{ __('messages.contact_phone') }}</th>
                    <th class="py-3 px-4">{{ __('messages.assigned_module_permissions') }}</th>
                    <th class="py-3 px-4 text-right" style="width: 170px;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                @forelse($subAdmins as $admin)
                    <tr x-show="!search || '{{ strtolower(addslashes($admin->name . ' ' . $admin->email . ' ' . ($admin->memberProfile?->phone ?? '')) . ' ' . $admin->permissions->pluck('name')->implode(' ')) }}'.includes(search.toLowerCase().trim())" class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 text-slate-900 font-bold">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white font-black flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-slate-900 text-xs leading-snug">{{ $admin->name }}</h4>
                                    <p class="text-[10px] text-slate-400 font-medium">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-bold">
                            {{ $admin->memberProfile?->phone ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $allAdminPerms = $admin->permissions->pluck('name')->toArray();
                                    // When a full module permission (e.g. events_manage) is active, hide its redundant individual action pills (events_view, etc.)
                                    $filteredPerms = collect($allAdminPerms)->filter(function($p) use ($allAdminPerms) {
                                        $modules = ['members', 'areas', 'businesses', 'events', 'gallery', 'sliders', 'agendas', 'desk', 'about', 'timelines', 'announcements', 'settings'];
                                        foreach ($modules as $mod) {
                                            if (in_array($mod . '_manage', $allAdminPerms) && in_array($p, [$mod . '_view', $mod . '_add', $mod . '_edit', $mod . '_delete'])) {
                                                return false;
                                            }
                                        }
                                        return true;
                                    });
                                @endphp
                                @forelse($filteredPerms as $permName)
                                    <span class="text-[9px] font-extrabold text-primary-700 bg-primary-50 border border-primary-100 px-2 py-0.5 rounded-md">
                                        {{ \App\Http\Controllers\Admin\SubAdminController::getPermissionLabel($permName) }}
                                    </span>
                                @empty
                                    <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">{{ __('messages.no_permissions_assigned') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex justify-end items-center space-x-1.5">
                                @php
                                    $adminPerms = $admin->permissions->pluck('name')->toArray();
                                    $hasEventsAccess = collect($adminPerms)->contains(fn($p) => str_starts_with($p, 'events_') || str_starts_with($p, 'event_'));
                                @endphp
                                @if($hasEventsAccess)

                                    <button type="button" @click="openEvents({
                                        id: {{ $admin->id }},
                                        name: {{ json_encode($admin->name) }},
                                        email: {{ json_encode($admin->email) }},
                                        permissions: {{ json_encode($adminPerms) }},
                                        update_url: '{{ route('admin.sub_admins.update_events', $admin->id) }}'
                                    })" class="flex items-center gap-1 px-2 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 transition-colors shadow-2xs font-black text-[10px]" title="Events & Access Settings">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ __('messages.events') }}</span>
                                    </button>
                                @endif
                                <button type="button" @click="openEdit({
                                    id: {{ $admin->id }},
                                    name: {{ json_encode($admin->name) }},
                                    email: {{ json_encode($admin->email) }},
                                    phone: {{ json_encode($admin->memberProfile?->phone ?? '') }},
                                    permissions: {{ json_encode($admin->permissions->pluck('name')->toArray()) }},
                                    update_url: '{{ route('admin.sub_admins.update', $admin->id) }}'
                                })" class="flex items-center justify-center w-7 h-7 rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 border border-primary-200/60 transition-colors shadow-2xs" title="Edit Permissions">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" @click="$dispatch('confirm-delete', { action: '{{ route('admin.sub_admins.destroy', $admin->id) }}', message: 'Delete Sub-Admin {{ addslashes($admin->name) }}?' })" class="flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200/60 transition-colors shadow-2xs" title="Delete Sub-Admin">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 font-medium">{{ __('messages.no_sub_admins_created') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ============ ADD SUB-ADMIN MODAL ============ -->
    <template x-teleport="body">
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-transition x-cloak>
            <div @click.away="showAddModal = false" style="scrollbar-width: none; -ms-overflow-style: none;" class="bg-white rounded-2xl p-4 border border-slate-100 shadow-2xl max-w-xl w-full space-y-3 relative max-h-[90vh] overflow-y-auto no-scrollbar">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>+ {{ __('messages.add_new_sub_admin') }}</span>
                    </h3>
                    <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.sub_admins.store') }}" class="space-y-3">
                    @csrf

                    @if($errors->any())
                        <div class="p-2.5 bg-rose-50 border border-rose-100 text-rose-700 text-[10px] font-semibold rounded-xl space-y-0.5">
                            <p class="font-extrabold uppercase text-[9px] text-rose-800">{{ __('messages.please_correct_errors') }}:</p>
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.sub_admin_full_name') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Satwara" class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.phone_whatsapp_number') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="9876543210" class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.email_address_login') }} <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="subadmin@satwara.org" class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                        <div class="space-y-0.5" x-data="{ showPass: false }">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="••••••••" class="w-full text-xs font-semibold pl-2.5 pr-8 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                                <button type="button" @click="showPass = !showPass" 
                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    title="{{ __('messages.toggle_password_visibility') }}">
                                    <svg x-show="!showPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access Checkboxes with View, Add, Edit, Delete -->
                    <div class="space-y-1 pt-1">
                        <label class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider block">{{ __('messages.allowed_modules_action_rights') }}</label>
                        <div style="scrollbar-width: none; -ms-overflow-style: none;" class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1">
                            @foreach($availablePermissions as $permKey => $permLabel)
                                @php
                                    $modPrefix = str_replace('_manage', '', $permKey);
                                @endphp
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-1.5 px-2.5 bg-slate-50/80 hover:bg-slate-100/90 border border-slate-200/60 rounded-lg transition-colors gap-1.5">
                                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 focus:ring-0 cursor-pointer">
                                        <span class="text-xs font-extrabold text-slate-900">{{ $permLabel }}</span>
                                        <span class="text-[9px] text-slate-400 font-medium">({{ __('messages.full') }})</span>
                                    </label>

                                    @unless(str_ends_with($permKey, '_view'))
                                    <div class="flex items-center gap-1 shrink-0 flex-wrap">
                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-blue-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_view" class="w-3 h-3 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                                            <span class="text-blue-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>{{ __('messages.view') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-emerald-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_add" class="w-3 h-3 rounded border-slate-300 text-emerald-600 focus:ring-0 cursor-pointer">
                                            <span class="text-emerald-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>{{ __('messages.add') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-amber-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_edit" class="w-3 h-3 rounded border-slate-300 text-amber-600 focus:ring-0 cursor-pointer">
                                            <span class="text-amber-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>{{ __('messages.edit') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-rose-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_delete" class="w-3 h-3 rounded border-slate-300 text-rose-600 focus:ring-0 cursor-pointer">
                                            <span class="text-rose-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>{{ __('messages.delete') }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showAddModal = false" class="px-3.5 py-1.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-lg cursor-pointer">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs cursor-pointer">{{ __('messages.save_sub_admin') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ============ EDIT SUB-ADMIN MODAL ============ -->
    <template x-teleport="body">
        <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-transition x-cloak>
            <div @click.away="showEditModal = false" style="scrollbar-width: none; -ms-overflow-style: none;" class="bg-white rounded-2xl p-4 border border-slate-100 shadow-2xl max-w-xl w-full space-y-3 relative max-h-[90vh] overflow-y-auto no-scrollbar">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('messages.edit_sub_admin_permissions') }}</span>
                    </h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">&times;</button>
                </div>

                <form method="POST" :action="editAdmin.update_url" class="space-y-3">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.sub_admin_full_name') }}</label>
                            <input type="text" name="name" :value="editAdmin.name" required class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.phone_whatsapp_number') }}</label>
                            <input type="text" name="phone" :value="editAdmin.phone" required maxlength="10" class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="space-y-0.5">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.email_address_login') }}</label>
                            <input type="email" name="email" :value="editAdmin.email" required class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                        <div class="space-y-0.5" x-data="{ showEditPass: false }">
                            <label class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('messages.new_password_optional') }}</label>
                            <div class="relative">
                                <input :type="showEditPass ? 'text' : 'password'" name="password" placeholder="{{ __('messages.leave_blank_to_keep_current') }}" class="w-full text-xs font-semibold pl-2.5 pr-8 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 outline-none">
                                <button type="button" @click="showEditPass = !showEditPass" 
                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    title="{{ __('messages.toggle_password_visibility') }}">
                                    <svg x-show="!showEditPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showEditPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access Checkboxes with View, Add, Edit, Delete -->
                    <div class="space-y-1 pt-1">
                        <label class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider block">{{ __('messages.update_allowed_modules_action_rights') }}</label>
                        <div style="scrollbar-width: none; -ms-overflow-style: none;" class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1">
                            @foreach($availablePermissions as $permKey => $permLabel)
                                @php
                                    $modPrefix = str_replace('_manage', '', $permKey);
                                @endphp
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-1.5 px-2.5 bg-slate-50/80 hover:bg-slate-100/90 border border-slate-200/60 rounded-lg transition-colors gap-1.5">
                                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" 
                                               :checked="editAdmin.permissions && editAdmin.permissions.includes('{{ $permKey }}')"
                                               @change="toggleFullAccess('{{ $permKey }}', '{{ $modPrefix }}', $event)"
                                               class="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 focus:ring-0 cursor-pointer">
                                        <span class="text-xs font-extrabold text-slate-900">{{ $permLabel }}</span>
                                        <span class="text-[9px] text-slate-400 font-medium">({{ __('messages.full') }})</span>
                                    </label>

                                    @unless(str_ends_with($permKey, '_view'))
                                    <div class="flex items-center gap-1 shrink-0 flex-wrap">
                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-blue-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_view" 
                                                   :checked="editAdmin.permissions && editAdmin.permissions.includes('{{ $modPrefix }}_view')"
                                                   @change="toggleActionAccess('{{ $permKey }}', '{{ $modPrefix }}', '{{ $modPrefix }}_view', $event)"
                                                   class="w-3 h-3 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                                            <span class="text-blue-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>{{ __('messages.view') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-emerald-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_add" 
                                                   :checked="editAdmin.permissions && editAdmin.permissions.includes('{{ $modPrefix }}_add')"
                                                   @change="toggleActionAccess('{{ $permKey }}', '{{ $modPrefix }}', '{{ $modPrefix }}_add', $event)"
                                                   class="w-3 h-3 rounded border-slate-300 text-emerald-600 focus:ring-0 cursor-pointer">
                                            <span class="text-emerald-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>{{ __('messages.add') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-amber-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_edit" 
                                                   :checked="editAdmin.permissions && editAdmin.permissions.includes('{{ $modPrefix }}_edit')"
                                                   @change="toggleActionAccess('{{ $permKey }}', '{{ $modPrefix }}', '{{ $modPrefix }}_edit', $event)"
                                                   class="w-3 h-3 rounded border-slate-300 text-amber-600 focus:ring-0 cursor-pointer">
                                            <span class="text-amber-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>{{ __('messages.edit') }}</span>
                                            </span>
                                        </label>

                                        <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 cursor-pointer hover:bg-rose-50 transition-colors text-[9px] font-bold select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $modPrefix }}_delete" 
                                                   :checked="editAdmin.permissions && editAdmin.permissions.includes('{{ $modPrefix }}_delete')"
                                                   @change="toggleActionAccess('{{ $permKey }}', '{{ $modPrefix }}', '{{ $modPrefix }}_delete', $event)"
                                                   class="w-3 h-3 rounded border-slate-300 text-rose-600 focus:ring-0 cursor-pointer">
                                            <span class="text-rose-700 inline-flex items-center gap-0.5">
                                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>{{ __('messages.delete') }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showEditModal = false" class="px-3.5 py-1.5 border border-slate-200 text-slate-600 font-bold text-xs rounded-lg cursor-pointer">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs cursor-pointer">{{ __('messages.update_sub_admin') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ============ SUB-ADMIN EVENTS POP-UP MODAL ============ -->
    <template x-teleport="body">
        <div x-show="showEventsModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs" 
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>
            <div @click.away="showEventsModal = false" 
                 class="bg-white rounded-2xl border border-slate-100 shadow-2xl max-w-2xl w-full flex flex-col overflow-hidden relative"
                 style="max-height: calc(100vh - 3rem);">
                
                <!-- Modal Header -->
                <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-extrabold flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ __('messages.events_access_permissions') }}</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                            {{ __('messages.managing_event_access_for') }} <span class="text-primary-400 font-black" x-text="eventsAdmin.name"></span>
                        </p>
                    </div>
                    <button type="button" @click="showEventsModal = false" 
                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold text-xs transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form method="POST" :action="eventsAdmin.update_url" class="flex flex-col min-h-0 m-0 shrink-0">
                    @csrf
                    @method('PUT')

                    <!-- Scrollable Modal Body -->
                    <div class="p-5 space-y-4 overflow-y-auto text-xs" style="max-height: 58vh;">
                        <!-- Info Alert -->
                        <!-- <div class="p-3 bg-indigo-50/90 border border-indigo-100/90 rounded-xl text-indigo-950 flex items-start gap-2.5 shadow-2xs">
                            <span class="text-indigo-600 text-sm shrink-0">ℹ️</span>
                            <div>
                                <h4 class="font-extrabold text-[11px] text-indigo-950">{{ __('messages.event_level_permissions_control') }}</h4>
                                <p class="text-[10px] text-indigo-700 font-medium mt-0.5">{{ __('messages.event_level_permissions_desc') }}</p>
                            </div>
                        </div> -->

                        <!-- ALL EVENTS LIST -->
                        <div class="space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <div class="flex items-center gap-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
                                        {{ __('messages.all_community_events') }} ({{ count($allEvents) }} Total):
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-indigo-50 border border-indigo-100/90 cursor-pointer hover:bg-indigo-100/90 transition-colors text-[10px] font-black text-indigo-700 select-none" title="Select / Unselect All Events Access">
                                        <input type="checkbox" 
                                               :checked="isAllEventsSelected()" 
                                               @change="toggleSelectAllEvents($event)"
                                               class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ __('messages.select_all') }}</span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Live Search Input -->
                                <div class="relative w-full sm:w-64">
                                    <input type="text" x-model="eventsSearch" placeholder="{{ __('messages.search_event_placeholder') }}" 
                                           class="w-full text-xs font-semibold pl-8 pr-7 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none transition-all">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <button type="button" x-show="eventsSearch" @click="eventsSearch = ''" 
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-xs">&times;</button>
                                </div>
                            </div>

                            <div class="space-y-2.5">
                                @forelse($allEvents as $evt)
                                    <div x-show="!eventsSearch || '{{ strtolower(addslashes($evt->title)) }}'.includes(eventsSearch.toLowerCase()) || '{{ strtolower(addslashes($evt->event_type ?? '')) }}'.includes(eventsSearch.toLowerCase())"
                                         class="flex items-center justify-between gap-3 bg-slate-50/90 hover:bg-slate-100/90 border border-slate-200/90 rounded-xl p-3 transition-all">
                                        
                                        <!-- Left Side: Event Title & Badges -->
                                        <div class="flex items-center gap-2 min-w-0">
                                            <h4 class="text-xs font-black text-slate-900 truncate" title="{{ $evt->title }}">{{ $evt->title }}</h4>
                                            @if($evt->event_type)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200/80 shrink-0">
                                                    {{ str_replace('_', ' ', $evt->event_type) }}
                                                </span>
                                            @endif
                                            @if($evt->status === 'closed')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-slate-200 text-slate-600 shrink-0">Closed</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200/80 shrink-0">Active</span>
                                            @endif
                                        </div>

                                        <!-- Right Side: View & Edit Controls -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <!-- View Permission -->
                                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-blue-50 cursor-pointer transition-colors text-[10px] font-bold select-none" title="Allow View Registrations & Details">
                                                <input type="checkbox" name="event_permissions[]" value="event_view_{{ $evt->id }}" 
                                                       x-model="eventsAdmin.permissions"
                                                       class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                                                <span class="text-blue-700 font-black inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>{{ __('messages.view') }}</span>
                                                </span>
                                            </label>

                                            <!-- Edit Permission -->
                                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-amber-50 cursor-pointer transition-colors text-[10px] font-bold select-none" title="Allow Edit / Approve / Reject">
                                                <input type="checkbox" name="event_permissions[]" value="event_edit_{{ $evt->id }}" 
                                                       x-model="eventsAdmin.permissions"
                                                       class="w-3.5 h-3.5 rounded border-slate-300 text-amber-600 focus:ring-0 cursor-pointer">
                                                <span class="text-amber-700 font-black inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>{{ __('messages.edit') }}</span>
                                                </span>
                                            </label>
                                        </div>

                                    </div>

                                @empty
                                    <div class="p-6 text-center text-slate-400 text-xs font-medium bg-slate-50 rounded-xl border border-slate-200/60">
                                        No events created yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions Footer (Fixed at Bottom) -->
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" @click="showEventsModal = false" 
                                class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-100 font-extrabold text-xs rounded-xl transition-colors cursor-pointer">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>{{ __('messages.save_event_permissions') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
