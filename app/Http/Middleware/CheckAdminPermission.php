<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request for Admin module permissions.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Full Administrators have unrestricted access
        if ($user->hasRole('Administrator')) {
            return $next($request);
        }

        // Sub-Admins must have the explicit permission assigned or any granular module permission
        if ($user->hasRole('Sub Admin')) {
            $userPerms = $user->permissions->pluck('name');
            if ($userPerms->contains($permission)) {
                return $next($request);
            }

            // Granular permissions (members_add, event_view_3, about_edit...) open only their own module
            $modPrefix = preg_replace('/_(manage|view)$/', '', $permission);
            if ($userPerms->contains(fn($p) => str_starts_with($p, $modPrefix . '_')
                || ($permission === 'events_manage' && str_starts_with($p, 'event_'))
                // The About page also manages the timeline (same as the sidebar link)
                || ($permission === 'about_manage' && ($p === 'settings_manage' || str_starts_with($p, 'timelines_'))))) {
                return $next($request);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized module access.'], 403);
        }

        return redirect()->route('admin.dashboard')->with('error', 'You do not have access permission for that section.');
    }
}
