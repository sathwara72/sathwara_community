<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Saves the Free / Paid fee popups on the Members, Businesses and Event pages.
 * "Free" is stored as an amount of 0, which every payment flow already treats as no payment due.
 */
class FeeSettingsController extends Controller
{
    public function updateMembership(Request $request)
    {
        $this->requireAny(['members_manage', 'members_edit']);
        $this->saveSettings($request, ['member_signup_fee']);

        return back()->with('success', 'Membership fee updated.');
    }

    public function updateBusiness(Request $request)
    {
        $this->requireAny(['businesses_manage', 'businesses_edit']);
        $this->saveSettings($request, ['business_registration_fee', 'business_renewal_fee']);

        return back()->with('success', 'Business registration and renewal fees updated.');
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->requireAny(['events_manage', 'events_edit', 'event_manage_' . $event->id, 'event_edit_' . $event->id]);
        $fields = $event->event_type === 'yuva_melo' ? ['pass_fee', 'form_fee'] : ['pass_fee'];

        $event->update($this->amounts($request, $fields));

        return back()->with('success', 'Fees for this event updated.');
    }

    /**
     * Fees can only be changed by someone allowed to edit that module (view-only access is not enough).
     */
    private function requireAny(array $permissions): void
    {
        $user = auth()->user();
        if ($user->hasRole('Administrator') || $user->permissions->pluck('name')->intersect($permissions)->isNotEmpty()) {
            return;
        }

        abort(403, 'You do not have permission to change these fees.');
    }

    private function saveSettings(Request $request, array $keys): void
    {
        foreach ($this->amounts($request, $keys) as $key => $amount) {
            Setting::set($key, (string) $amount);
        }
    }

    /**
     * Read each fee's Free/Paid choice and amount. A paid fee must be at least ₹1.
     *
     * @return array<string, float>
     */
    private function amounts(Request $request, array $keys): array
    {
        $rules = [];
        foreach ($keys as $key) {
            $rules["{$key}_mode"] = 'required|in:free,paid';
            $rules[$key] = "required_if:{$key}_mode,paid|nullable|numeric|min:1|max:1000000";
        }
        $request->validate($rules, ['*.min' => 'A paid fee must be at least ₹1. Choose Free for no fee.']);

        $amounts = [];
        foreach ($keys as $key) {
            $amounts[$key] = $request->input("{$key}_mode") === 'paid' ? round((float) $request->input($key), 2) : 0.0;
        }

        return $amounts;
    }
}
