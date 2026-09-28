<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $query = Event::published();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $events = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        $user = auth()->user();
        $myRegistrations = $user 
            ? $user->eventRegistrations()->passes()->with('event')->orderBy('created_at', 'desc')->get() 
            : collect();
        $registrations = $user 
            ? $user->eventRegistrations()->pluck('status', 'event_id')->toArray() 
            : [];

        return view('member.event.index', compact('events', 'registrations', 'myRegistrations'));
    }

    /**
     * Display the specified event.
     */
    public function show($id)
    {
        $event = Event::published()->findOrFail($id);
        $user = auth()->user();
        $registration = $user ? $user->eventRegistrations()
            ->where('event_id', $id)
            ->where(function($q) use ($event) {
                if ($event->event_type === 'inam_vitaran') {
                    $q->whereNull('form_data->student_name');
                } elseif ($event->event_type === 'yuva_melo') {
                    $q->whereNull('form_data->surname')
                      ->whereNull('form_data->qualification');
                }
            })
            ->latest()
            ->first() : null;
        $gallery = $event->galleries()->get();

        return view('member.event.show', compact('event', 'registration', 'gallery'));
    }

    /**
     * Display the dedicated registration form page for the specified event.
     */
    public function showRegistrationForm($id)
    {
        $event = Event::published()->findOrFail($id);
        
        if (!($event->has_registration_form ?? $event->registration_option)) {
            return redirect()->route('event.details', $event->id)->with('warning', 'Registration form is not enabled for this event.');
        }
        $user = auth()->user();
        $allUserRegistrations = $user ? $user->eventRegistrations()->where('event_id', $id)->orderBy('created_at', 'desc')->get() : collect();

        // Check if user has registered / purchased a pass for this event
        $hasEventPass = $allUserRegistrations->isNotEmpty();

        // Filter registrations to show in the submitted cards list
        $registrations = $allUserRegistrations->filter(function($r) use ($event) {
            if ($event->event_type === 'inam_vitaran') {
                return !empty($r->form_data['student_name']);
            }
            if ($event->event_type === 'yuva_melo') {
                return !empty($r->form_data['surname']) || !empty($r->form_data['first_name']) || !empty($r->form_data['qualification']);
            }
            return true;
        });

        $registration = $registrations->first();
        $familyMembers = $user ? $user->familyMembers()->orderBy('name')->get() : collect();
        $areas = \App\Models\Area::orderBy('name')->get();

        return view('member.event.register', compact('event', 'registration', 'registrations', 'familyMembers', 'areas', 'hasEventPass'));
    }
}
