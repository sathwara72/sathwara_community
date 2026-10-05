<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MemberProfile;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class SubAdminController extends Controller
{
    /**
     * Module permissions list definition
     */
    public static function getAvailablePermissions()
    {
        return [
            'members_manage'      => __('messages.mod_members_approvals'),
            'areas_manage'        => __('messages.mod_area_management'),
            'businesses_manage'   => __('messages.mod_business_listings'),
            'events_manage'       => __('messages.mod_events_awards'),
            'gallery_manage'      => __('messages.mod_general_gallery'),
            'sliders_manage'      => __('messages.mod_hero_sliders'),
            'agendas_manage'      => __('messages.mod_core_agendas'),
            'desk_manage'         => __('messages.mod_management_desk'),
            'about_manage'        => __('messages.mod_about_content'),
            // 'committee_manage'    => __('messages.mod_committee_members'),
            'timelines_manage'    => __('messages.mod_milestone_timeline'),
            'announcements_manage'=> __('messages.mod_announcements_news'),
            'settings_manage'     => __('messages.mod_global_settings'),
            'transactions_view'   => __('messages.mod_transactions'),
        ];
    }

    /**
     * Resolve translated label for any permission (Full Module or Action level)
     */
    public static function getPermissionLabel(string $permName): string
    {
        $available = self::getAvailablePermissions();
        if (isset($available[$permName])) {
            return $available[$permName];
        }

        $transKey = 'messages.perm_' . $permName;
        $translated = __($transKey);
        if ($translated !== $transKey) {
            return $translated;
        }

        // Event specific permissions: event_view_1, event_edit_1
        if (preg_match('/^event_(view|edit|manage|create|delete)_(\d+)$/', $permName, $m)) {
            $action = $m[1];
            $actionLabel = match($action) {
                'view'   => __('messages.view'),
                'edit'   => __('messages.edit'),
                'delete' => __('messages.delete'),
                default  => ucfirst($action)
            };
            return __('messages.events') . " #{$m[2]}: {$actionLabel}";
        }

        return ucwords(str_replace('_', ' ', $permName));
    }

    /**
     * List all Sub-Admins
     */
    public function index(Request $request)
    {
        // Enforce Administrator role only for Sub-Admin management
        if (!auth()->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized. Only main Administrators can manage Sub-Admins.');
        }

        // Ensure permissions exist in database
        $availablePermissions = self::getAvailablePermissions();
        foreach (array_keys($availablePermissions) as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Sub Admin', 'guard_name' => 'web']);

        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'Sub Admin');
        })->with('permissions', 'memberProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subAdmins = $query->latest()->paginate(10)->withQueryString();
        $areas = Area::orderBy('name')->get();
        $allEvents = \App\Models\Event::withCount('registrations')->latest()->get();

        return view('admin.sub_admins.index', compact('subAdmins', 'availablePermissions', 'areas', 'allEvents'));
    }

    /**
     * Update Event-specific permissions for Sub-Admin
     */
    public function updateEventPermissions(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'event_permissions' => 'nullable|array',
            'event_permissions.*' => 'string',
        ]);

        $eventPerms = $request->event_permissions ?? [];

        // Ensure permissions exist in database
        foreach ($eventPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Keep non-event permissions and sync new event permissions
        $existingPerms = $user->permissions->pluck('name')->filter(function($p) {
            return !str_starts_with($p, 'event_manage_')
                && !str_starts_with($p, 'event_view_')
                && !str_starts_with($p, 'event_edit_')
                && !str_starts_with($p, 'event_create_')
                && !str_starts_with($p, 'event_delete_');
        })->toArray();

        $mergedPerms = array_unique(array_merge($existingPerms, $eventPerms));
        $user->syncPermissions($mergedPerms);

        return redirect()->route('admin.sub_admins.index')->with('success', 'Event permissions updated for Sub-Admin ' . $user->name);
    }

    /**
     * Store new Sub-Admin
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password'    => 'required|string|min:6',
            'phone'       => 'required|string|max:15',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Create User
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => 'approved',
        ]);

        // Assign Sub Admin Role
        $subAdminRole = Role::firstOrCreate(['name' => 'Sub Admin', 'guard_name' => 'web']);
        $user->assignRole($subAdminRole);

        // Sync Granular Permissions
        if ($request->filled('permissions')) {
            foreach ($request->permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $user->syncPermissions($request->permissions);
        }

        // Create basic profile entry
        MemberProfile::create([
            'user_id'    => $user->id,
            'first_name' => $request->name,
            'middle_name'=> 'SubAdmin',
            'last_name'  => 'Admin',
            'phone'      => $request->phone,
            'whatsapp'   => $request->phone,
            'gender'     => 'Male',
            'address'    => 'Admin Office',
            'area_id'    => Area::first()->id ?? 1,
        ]);

        return redirect()->route('admin.sub_admins.index')->with('success', 'Sub-Admin created successfully with assigned permissions.');
    }

    /**
     * Update Sub-Admin & Permissions
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user->id)],
            'password'    => 'nullable|string|min:6',
            'phone'       => 'required|digits:10',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $userData = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Update profile phone
        if ($user->memberProfile) {
            $user->memberProfile->update([
                'first_name' => $request->name,
                'phone'      => $request->phone,
            ]);
        }

        // Sync Direct Module Permissions (preserving existing event permissions)
        $newPermissions = $request->permissions ?? [];
        $existingEventPerms = $user->permissions->pluck('name')->filter(function($p) {
            return str_starts_with($p, 'event_');
        })->toArray();

        $allPermissionsToSync = array_unique(array_merge($newPermissions, $existingEventPerms));

        foreach ($allPermissionsToSync as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $user->syncPermissions($allPermissionsToSync);

        return redirect()->route('admin.sub_admins.index')->with('success', 'Sub-Admin permissions and details updated successfully.');
    }

    /**
     * Delete Sub-Admin
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);
        
        // Prevent deleting main Admin
        if ($user->id === auth()->id() || $user->hasRole('Administrator')) {
            return back()->with('error', 'Cannot delete main Administrator.');
        }

        if ($user->memberProfile) {
            $user->memberProfile->delete();
        }
        $user->delete();

        return redirect()->route('admin.sub_admins.index')->with('success', 'Sub-Admin deleted successfully.');
    }
}
