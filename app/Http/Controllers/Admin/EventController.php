<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\EventRegistration;
use App\Models\SponsorshipType;
use App\Models\EventSponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * List Events
     */
    public function index(Request $request)
    {
        $query = Event::with('registrations');

        $user = auth()->user();
        if ($user->hasRole('Sub Admin') && !$user->permissions->pluck('name')->contains('events_manage')) {
            $allowedEventIds = $user->permissions->pluck('name')
                ->filter(fn($p) => str_starts_with($p, 'event_'))
                ->map(function($p) {
                    return (int) str_replace(['event_manage_', 'event_view_', 'event_edit_', 'event_create_'], '', $p);
                })->filter()->unique()->toArray();

            $query->whereIn('id', $allowedEventIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $events = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show Event Details
     */
    public function show($id)
    {
        $event = Event::withCount('registrations')->findOrFail($id);
        $gallery = Gallery::where('event_id', $event->id)->orderBy('display_order')->get();
        $allRegistrations = EventRegistration::where('event_id', $event->id)
            ->with(['user.memberProfile', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sponsorshipTypes = SponsorshipType::where('event_id', $event->id)
            ->withCount(['sponsors', 'approvedSponsors'])
            ->orderBy('display_order')
            ->orderBy('amount', 'desc')
            ->get();

        $sponsors = EventSponsor::where('event_id', $event->id)
            ->with(['sponsorshipType', 'user', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate event summary statistics
        $stats = [
            'total_registrations' => $allRegistrations->count(),
            'total_passes' => $event->total_passes_count,
            'total_inam_forms' => $allRegistrations->filter(fn($r) => !empty($r->form_data['student_name']))->count(),
            'total_yuva_forms' => $allRegistrations->filter(fn($r) => !empty($r->form_data['surname']) || !empty($r->form_data['first_name']) || !empty($r->form_data['qualification']))->count(),
            'last_pass_no' => (int)($allRegistrations->whereNotNull('pass_number')->max('pass_number') ?? 0),
            'last_inam_no' => (int)($allRegistrations->whereNotNull('inam_number')->max('inam_number') ?? 0),
            'last_yuva_melo_no' => (int)($allRegistrations->whereNotNull('yuva_melo_number')->max('yuva_melo_number') ?? 0),
            'total_sponsorship_types' => $sponsorshipTypes->count(),
            'total_sponsors' => $sponsors->count(),
            'approved_sponsors_count' => $sponsors->where('status', 'approved')->count(),
            'total_sponsorship_amount' => $sponsors->where('status', 'approved')->sum('amount'),
        ];

        // For inam_vitaran and yuva_melo events, only show student/candidate form registrations on the show page
        $registrations = $allRegistrations->filter(function($r) use ($event) {
            if ($event->event_type === 'inam_vitaran') {
                return !empty($r->form_data['student_name']);
            }
            if ($event->event_type === 'yuva_melo') {
                return !empty($r->form_data['surname']) || !empty($r->form_data['qualification']) || !empty($r->form_data['birth_date']) || !empty($r->form_data['first_name']);
            }
            return true;
        });

        return view('admin.events.show', compact('event', 'gallery', 'registrations', 'stats', 'sponsorshipTypes', 'sponsors'));
    }

    /**
     * Create Form
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store Event
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:normal,inam_vitaran,yuva_melo',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'google_map_link' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'registration_end_date' => 'required|date',
            'banner' => 'nullable|image|max:3072',
            'has_registration_form' => 'nullable|boolean',
            'form_start_date' => 'nullable|date',
            'form_end_date' => 'nullable|date|after_or_equal:form_start_date',
            'pass_fee' => 'nullable|numeric|min:0',
            'pass_purchase_access' => 'required|in:members_only,anyone',
            'total_pass_limit' => 'nullable|integer|min:1',
            'form_fee' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published,cancelled',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('events/banners', 'public');
        }

        $hasRegistrationForm = ($request->event_type === 'normal') ? false : (bool) $request->has_registration_form;

        Event::create([
            'title' => $request->title,
            'event_type' => $request->event_type,
            'description' => $request->description ?? '',
            'venue' => $request->venue,
            'google_map_link' => $request->google_map_link,
            'date' => $request->date,
            'time' => $request->time,
            'published_date' => $request->published_date,
            'registration_end_date' => $request->registration_end_date,
            'banner_path' => $bannerPath ?? '',
            'registration_option' => $hasRegistrationForm,
            'has_registration_form' => $hasRegistrationForm,
            'form_start_date' => $request->form_start_date,
            'form_end_date' => $request->form_end_date,
            'pass_fee' => $request->pass_fee ?? 0.00,
            'pass_purchase_access' => $request->pass_purchase_access,
            'total_pass_limit' => $request->total_pass_limit ?: null,
            'form_fee' => $request->form_fee ?? 0.00,
            'max_participants' => $request->max_participants,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    private function checkEditPermission($eventId)
    {
        $user = auth()->user();
        if ($user->hasRole('Administrator')) {
            return;
        }
        $userPerms = $user->permissions->pluck('name');
        if ($userPerms->contains('events_manage') || $userPerms->contains('events_edit') || $userPerms->contains('event_manage_' . $eventId) || $userPerms->contains('event_edit_' . $eventId)) {
            return;
        }
        abort(403, 'You do not have permission to edit or modify this event.');
    }

    private function checkDeletePermission($eventId)
    {
        $user = auth()->user();
        if ($user->hasRole('Administrator')) {
            return;
        }
        $userPerms = $user->permissions->pluck('name');
        if ($userPerms->contains('events_manage') || $userPerms->contains('events_delete') || $userPerms->contains('event_manage_' . $eventId) || $userPerms->contains('event_delete_' . $eventId)) {
            return;
        }
        abort(403, 'You do not have permission to delete this event.');
    }

    /**
     * Edit Form
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $this->checkEditPermission($event->id);
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update Event
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->checkEditPermission($event->id);

        $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:normal,inam_vitaran,yuva_melo',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'google_map_link' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'registration_end_date' => 'required|date',
            'banner' => 'nullable|image|max:3072',
            'has_registration_form' => 'nullable|boolean',
            'form_start_date' => 'nullable|date',
            'form_end_date' => 'nullable|date|after_or_equal:form_start_date',
            'pass_fee' => 'nullable|numeric|min:0',
            'pass_purchase_access' => 'required|in:members_only,anyone',
            'total_pass_limit' => 'nullable|integer|min:1',
            'form_fee' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published,cancelled',
        ]);

        $bannerPath = $event->banner_path;
        if ($request->hasFile('banner')) {
            if (Storage::disk('public')->exists($event->banner_path) && !str_starts_with($event->banner_path, 'http')) {
                Storage::disk('public')->delete($event->banner_path);
            }
            $bannerPath = $request->file('banner')->store('events/banners', 'public');
        }

        $hasRegistrationForm = ($request->event_type === 'normal') ? false : (bool) $request->has_registration_form;

        $event->update([
            'title' => $request->title,
            'event_type' => $request->event_type,
            'description' => $request->description ?? '',
            'venue' => $request->venue,
            'google_map_link' => $request->google_map_link,
            'date' => $request->date,
            'time' => $request->time,
            'published_date' => $request->published_date,
            'registration_end_date' => $request->registration_end_date,
            'banner_path' => $bannerPath ?? '',
            'registration_option' => $hasRegistrationForm,
            'has_registration_form' => $hasRegistrationForm,
            'form_start_date' => $request->form_start_date,
            'form_end_date' => $request->form_end_date,
            'pass_fee' => $request->pass_fee ?? 0.00,
            'pass_purchase_access' => $request->pass_purchase_access,
            'total_pass_limit' => $request->total_pass_limit ?: null,
            'form_fee' => $request->form_fee ?? 0.00,
            'max_participants' => $request->max_participants,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Delete Event
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $this->checkDeletePermission($event->id);
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }

    /**
     * View Registered Participants (Pass Registrations)
     */
    public function registrations($id)
    {
        $event = Event::findOrFail($id);
        $allRegistrations = EventRegistration::where('event_id', $event->id)
            ->with(['user.memberProfile', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        // For inam_vitaran and yuva_melo events: in registrations (Pass Registrations) list, ONLY show general event pass attendees (exclude student/candidate forms)
        $registrations = $allRegistrations->filter(function($r) use ($event) {
            if ($event->event_type === 'inam_vitaran') {
                return empty($r->form_data['student_name']);
            }
            if ($event->event_type === 'yuva_melo') {
                return empty($r->form_data['surname']) && empty($r->form_data['qualification']) && empty($r->form_data['birth_date']) && empty($r->form_data['first_name']);
            }
            return true;
        });

        return view('admin.events.registrations', compact('event', 'registrations'));
    }

    /**
     * Toggle Selection for Event Registration
     */
    public function toggleSelectRegistration($id)
    {
        $registration = EventRegistration::findOrFail($id);
        $this->checkEditPermission($registration->event_id);
        $registration->update([
            'is_selected' => !$registration->is_selected
        ]);

        $message = $registration->is_selected ? 'Registration selected successfully.' : 'Registration unselected successfully.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Manage Event Gallery Photos
     */
    public function gallery($id)
    {
        $event = Event::findOrFail($id);
        $photos = Gallery::where('event_id', $event->id)->orderBy('display_order')->paginate(15)->withQueryString();
        return view('admin.events.gallery', compact('event', 'photos'));
    }

    /**
     * Upload Event Gallery Photos (Supports Multiple Images & ZIP file)
     */
    public function uploadGallery(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'image' => 'nullable|file|mimes:zip,jpeg,png,jpg,gif,svg,webp,mp4,mov,webm,ogg,m4v,avi,mkv|max:102400',
            'images.*' => 'nullable|file|mimes:zip,jpeg,png,jpg,gif,svg,webp,mp4,mov,webm,ogg,m4v,avi,mkv|max:102400',
        ]);

        $uploadedCount = 0;

        // Handle single file / ZIP file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'zip') {
                if (!class_exists('\ZipArchive')) {
                    return redirect()->back()->with('error', 'PHP ZipArchive extension is not enabled on this server.');
                }

                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    $tempPath = storage_path('app/temp_event_zip_' . time() . '_' . uniqid());
                    if (!file_exists($tempPath)) {
                        mkdir($tempPath, 0777, true);
                    }

                    $zip->extractTo($tempPath);
                    $zip->close();

                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($tempPath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'mov', 'webm', 'ogg', 'm4v'];

                    foreach ($files as $name => $f) {
                        if (!$f->isDir()) {
                            $filePath = $f->getRealPath();
                            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                            if (in_array($fileExtension, $allowedExtensions)) {
                                $fileName = 'zip_' . uniqid() . '.' . $fileExtension;
                                $destinationDir = 'events/gallery';
                                Storage::disk('public')->makeDirectory($destinationDir);

                                $publicPath = $destinationDir . '/' . $fileName;
                                Storage::disk('public')->put($publicPath, file_get_contents($filePath));

                                Gallery::create([
                                    'event_id' => $event->id,
                                    'image_path' => $publicPath,
                                    'caption' => $event->title,
                                    'display_order' => Gallery::where('event_id', $event->id)->max('display_order') + 1,
                                ]);
                                $uploadedCount++;
                            }
                        }
                    }

                    $this->deleteTempDir($tempPath);

                    if ($uploadedCount === 0) {
                        return redirect()->back()->with('error', 'No valid photos or videos found inside the ZIP archive.');
                    }

                    return redirect()->back()->with('success', "$uploadedCount media files extracted and uploaded successfully from ZIP archive.");
                } else {
                    return redirect()->back()->with('error', 'Failed to open the ZIP file.');
                }
            } else {
                $path = $file->store('events/gallery', 'public');
                Gallery::create([
                    'event_id' => $event->id,
                    'image_path' => $path,
                    'caption' => $event->title,
                    'display_order' => Gallery::where('event_id', $event->id)->max('display_order') + 1,
                ]);
                $uploadedCount++;
            }
        }

        // Handle multiple image uploads (including if a zip was passed in array)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'zip') {
                    if (class_exists('\ZipArchive')) {
                        $zip = new \ZipArchive();
                        if ($zip->open($file->getRealPath()) === true) {
                            $tempPath = storage_path('app/temp_event_zip_' . time() . '_' . uniqid());
                            if (!file_exists($tempPath)) {
                                mkdir($tempPath, 0777, true);
                            }
                            $zip->extractTo($tempPath);
                            $zip->close();

                            $files = new \RecursiveIteratorIterator(
                                new \RecursiveDirectoryIterator($tempPath),
                                \RecursiveIteratorIterator::LEAVES_ONLY
                            );
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'mov', 'webm', 'ogg', 'm4v'];
                            foreach ($files as $name => $f) {
                                if (!$f->isDir()) {
                                    $filePath = $f->getRealPath();
                                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                    if (in_array($fileExtension, $allowedExtensions)) {
                                        $fileName = 'zip_' . uniqid() . '.' . $fileExtension;
                                        $destinationDir = 'events/gallery';
                                        Storage::disk('public')->makeDirectory($destinationDir);
                                        $publicPath = $destinationDir . '/' . $fileName;
                                        Storage::disk('public')->put($publicPath, file_get_contents($filePath));
                                        Gallery::create([
                                            'event_id' => $event->id,
                                            'image_path' => $publicPath,
                                            'caption' => $event->title,
                                            'display_order' => Gallery::where('event_id', $event->id)->max('display_order') + 1,
                                        ]);
                                        $uploadedCount++;
                                    }
                                }
                            }
                            $this->deleteTempDir($tempPath);
                        }
                    }
                } else {
                    $path = $file->store('events/gallery', 'public');
                    Gallery::create([
                        'event_id' => $event->id,
                        'image_path' => $path,
                        'caption' => $event->title,
                        'display_order' => Gallery::where('event_id', $event->id)->max('display_order') + 1,
                    ]);
                    $uploadedCount++;
                }
            }
        }

        if ($uploadedCount === 0) {
            return redirect()->back()->with('error', 'Please select photos, videos, or a ZIP file to upload.');
        }

        return redirect()->back()->with('success', "$uploadedCount gallery media uploaded successfully.");
    }

    private function deleteTempDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dirPath/$file")) ? $this->deleteTempDir("$dirPath/$file") : unlink("$dirPath/$file");
        }
        rmdir($dirPath);
    }

    /**
     * Delete Event Gallery Photo
     */
    public function deleteGalleryPhoto($id)
    {
        $photo = Gallery::findOrFail($id);

        if (Storage::disk('public')->exists($photo->image_path) && !str_starts_with($photo->image_path, 'http')) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();
        return redirect()->back()->with('success', 'Photo removed from gallery.');
    }

    /**
     * Export Events CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=events_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $query = Event::withCount('registrations');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $events = $query->orderBy('date', 'desc')->get();

        $callback = function () use ($events) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_event_title'),
                __('messages.csv_event_type'),
                __('messages.csv_date'),
                __('messages.csv_time'),
                __('messages.csv_venue'),
                __('messages.csv_pass_fee'),
                __('messages.csv_registrations_count'),
                __('messages.csv_status')
            ]);

            $sr = 0;
            foreach ($events as $e) {
                $statusKey = strtolower($e->status ?? 'active');
                fputcsv($file, [
                    ++$sr,
                    $e->title,
                    $e->event_type ?? 'normal',
                    $e->date,
                    $e->time,
                    $e->venue,
                    $e->pass_fee ?? 0,
                    $e->registrations_count ?? 0,
                    __('messages.' . $statusKey) != 'messages.' . $statusKey ? __('messages.' . $statusKey) : ucfirst($e->status ?? 'active'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Event Registrations CSV / Excel (Pass Registrations)
     */
    public function exportRegistrationsCsv($id)
    {
        $event = Event::findOrFail($id);
        $allRegistrations = EventRegistration::where('event_id', $event->id)->with(['user.memberProfile', 'transactions'])->orderBy('created_at', 'asc')->get();

        // For inam_vitaran and yuva_melo events: export pass attendees only
        $registrations = $allRegistrations->filter(function($r) use ($event) {
            if ($event->event_type === 'inam_vitaran') {
                return empty($r->form_data['student_name']);
            }
            if ($event->event_type === 'yuva_melo') {
                return empty($r->form_data['surname']) && empty($r->form_data['qualification']) && empty($r->form_data['birth_date']) && empty($r->form_data['first_name']);
            }
            return true;
        });

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => $event->exportDisposition('passes'),
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Sr. No.', 'Pass No.', 'Member Code', 'Participant Name', 'Contact Number', 'Person Count', 'Pass Fee (INR)', 'Payment Status', 'Payment ID', 'Transaction ID', 'Purchase Date', 'Remarks']);
            $sr = 0;
            foreach ($registrations as $index => $r) {
                $fd = $r->form_data ?? [];
                $passNo = $r->pass_number ? sprintf('%03d', $r->pass_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $index + 1));
                fputcsv($file, [
                    ++$sr,
                    $passNo,
                    $r->user->member_code ?? '',
                    $fd['full_name'] ?? ($r->user ? $r->user->name : 'Participant'),
                    $fd['contact_number'] ?? ($r->user->memberProfile->phone ?? ($fd['mobile'] ?? '')),
                    $fd['person_count'] ?? 1,
                    $r->payment_amount ?? 0,
                    ucfirst($r->payment_status ?? 'paid'),
                    $r->payment_id ?? '-',
                    $r->transactions->pluck('transaction_no')->implode(', '),
                    $fd['submission_date'] ?? ($r->created_at ? $r->created_at->format('d-M-Y h:i A') : ''),
                    $fd['remarks'] ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Approve Event Registration
     */
    public function approveRegistration($id)
    {
        $registration = EventRegistration::findOrFail($id);
        $registration->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Registration approved successfully.');
    }

    /**
     * Reject Event Registration
     */
    public function rejectRegistration($id)
    {
        $registration = EventRegistration::findOrFail($id);
        $registration->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Registration rejected.');
    }

    /**
     * Export Inam Vitaran Student Submissions CSV
     */
    public function exportInamSubmissionsCsv(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $allRegistrations = EventRegistration::where('event_id', $event->id)
            ->with(['user.memberProfile'])
            ->get();

        $topFilter = $request->query('top', 'all');
        $standardFilter = $request->query('standard', 'all');
        $search = trim((string)$request->query('search', ''));

        $registrations = $allRegistrations->filter(function($r) {
            return !empty($r->form_data['student_name']);
        });

        // Compute numeric percentage & standard
        $processed = $registrations->map(function($r) {
            $fd = $r->form_data ?? [];
            $stdRaw = trim((string)($fd['schoolStandard'] ?? $fd['standard'] ?? $fd['school_standard'] ?? $fd['education'] ?? $fd['course'] ?? 'General'));
            $stream = trim((string)($fd['schoolStream'] ?? $fd['stream'] ?? ''));
            if (!empty($stream) && $stream !== 'Other' && !str_contains($stdRaw, $stream)) {
                $stdName = $stdRaw . ' (' . $stream . ')';
            } else {
                $stdName = $stdRaw ?: 'General';
            }
            $r->std_name = $stdName;

            $pct = 0;
            if (!empty($fd['percentage'])) {
                $pct = (float)preg_replace('/[^0-9.]/', '', (string)$fd['percentage']);
            } elseif (!empty($fd['received_marks']) && !empty($fd['total_marks']) && (float)$fd['total_marks'] > 0) {
                $pct = round(((float)$fd['received_marks'] / (float)$fd['total_marks']) * 100, 2);
            }
            $r->calc_pct = $pct;
            return $r;
        });

        // Filter by standard if selected
        if (!empty($standardFilter) && $standardFilter !== 'all') {
            $processed = $processed->filter(fn($r) => $r->std_name === $standardFilter);
        }

        // Filter by search query if present
        if (!empty($search)) {
            $s = mb_strtolower($search);
            $processed = $processed->filter(function($r) use ($s) {
                $fd = $r->form_data ?? [];
                $name = mb_strtolower($fd['student_name'] ?? ($r->user->name ?? ''));
                $father = mb_strtolower($fd['father_name'] ?? ($fd['parent_name'] ?? ''));
                $school = mb_strtolower($fd['school_college'] ?? '');
                $phone = mb_strtolower($fd['mobile_no'] ?? $fd['mobile'] ?? ($r->user->memberProfile->phone ?? ''));
                $inamNo = (string)($r->inam_number ?? '');
                return str_contains($name, $s) || str_contains($father, $s) || str_contains($school, $s) || str_contains($phone, $s) || str_contains($inamNo, $s);
            });
        }

        // Group by standard & sort by percentage descending
        $grouped = $processed->groupBy('std_name')->sortBy(function($students, $key) {
            if (preg_match('/(\d+)/', $key, $m)) {
                return (int)$m[1];
            }
            return 999;
        });

        $sortedRows = collect();
        foreach ($grouped as $stdName => $studentsInStd) {
            $sortedStd = $studentsInStd->sortByDesc('calc_pct')->values();
            
            // Apply Top 3 or Top 5 limit per standard if requested
            if ($topFilter === 'top3') {
                $sortedStd = $sortedStd->take(3);
            } elseif ($topFilter === 'top5') {
                $sortedStd = $sortedStd->take(5);
            }

            foreach ($sortedStd as $idx => $student) {
                $student->std_rank = $idx + 1;
                $sortedRows->push($student);
            }
        }

        $filenameSuffix = ($topFilter === 'top3' ? '_top3' : ($topFilter === 'top5' ? '_top5' : ''));
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => $event->exportDisposition('inam_submissions' . $filenameSuffix),
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($sortedRows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Sr. No.',
                'Member Code',
                'Student Name',
                'Parent Name',
                'Total Marks',
                'Obtained Marks',
                'Percentage',
                'Standard',
                'Rank',
                'Contact No',
                'Marksheet URL',
                'Submission Date',
            ]);

            $sr = 0;
            foreach ($sortedRows as $index => $r) {
                $fd = $r->form_data ?? [];
                $phone = $fd['mobile_no'] ?? $fd['mobile'] ?? $fd['contact_number'] ?? ($r->user->memberProfile->phone ?? ($r->user->phone ?? ''));
                $rawMarksheet = $fd['marksheet_url'] ?? $fd['marksheet'] ?? $fd['result_photo'] ?? $fd['result_url'] ?? '';
                $marksheetUrl = '';
                if (!empty($rawMarksheet)) {
                    $marksheetUrl = str_starts_with($rawMarksheet, 'http') ? $rawMarksheet : asset('storage/' . $rawMarksheet);
                }
                $memberCode = $r->user->member_code ?? ($r->user->memberProfile->member_code ?? ($fd['member_code'] ?? ($r->user ? '#' . sprintf('%05d', $r->user->id) : '')));
                $submissionDate = $r->created_at ? $r->created_at->format('d-M-Y') : ($fd['submission_date'] ?? '');

                fputcsv($file, [
                    ++$sr,
                    $memberCode,
                    $fd['student_name'] ?? ($r->user ? $r->user->name : ''),
                    $fd['father_name'] ?? ($fd['parent_name'] ?? ''),
                    $fd['total_marks'] ?? '',
                    $fd['received_marks'] ?? ($fd['obtained_marks'] ?? ''),
                    $r->calc_pct > 0 ? $r->calc_pct . '%' : (!empty($fd['percentage']) ? (str_contains((string)$fd['percentage'], '%') ? $fd['percentage'] : $fd['percentage'] . '%') : ''),
                    $r->std_name,
                    'Rank ' . ($r->std_rank ?? ($index + 1)),
                    $phone,
                    $marksheetUrl,
                    $submissionDate,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Yuva Melo Candidate Submissions CSV
     */
    public function exportYuvaSubmissionsCsv($id)
    {
        $event = Event::findOrFail($id);
        $allRegistrations = EventRegistration::where('event_id', $event->id)
            ->with(['user.memberProfile'])
            ->orderBy('created_at', 'asc')
            ->get();

        $registrations = $allRegistrations->filter(function($r) {
            return !empty($r->form_data['surname']) || !empty($r->form_data['qualification']) || !empty($r->form_data['birth_date']) || !empty($r->form_data['first_name']);
        });

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => $event->exportDisposition('yuva_melo_candidates'),
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Sr. No.', 'Yuva Melo No.', 'Status', 'Member Code', 'Name', 'Surname', 'Gender', 'Father Name', 'Grandpa Name', 'Address', 'Mobile Number 1', 'Whatsapp Number', 'Birth Date', 'Age', 'Height', 'Weight', 'Qualification', 'Occupation', 'Occupation Address', 'Monthly Income', 'Elder Brothers', 'Married Elder Brothers', 'Younger Brothers', 'Married Younger Brothers', 'Elder Sisters', 'Married Elder Sisters', 'Younger Sisters', 'Married Younger Sisters', 'Father Occupation', 'Father Occupation Address', 'Father Mobile', 'Father Age', 'Father Income', 'Native Place', 'Mother Name', 'Mother Occupation', 'Maternal Uncle Name', 'Maternal Grandfather Name', 'Maternal Grandfather Address', 'Maternal Grandfather Occupation', 'Business', 'House', 'Own House', 'Vehicle', 'Divorce', 'Special Need', 'Physical Disability', 'Disability Duration', 'Other Info', 'Special Info', 'Photo URL', 'Submission Date'
            ]);

            $sr = 0;
            foreach ($registrations as $index => $r) {
                $fd = $r->form_data ?? [];
                $yuvaNo = $r->yuva_melo_number ? sprintf('%03d', $r->yuva_melo_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $index + 1));
                fputcsv($file, [
                    ++$sr,
                    $yuvaNo,
                    ucfirst($r->status ?? 'approved'),
                    $r->user->member_code ?? '',
                    $fd['first_name'] ?? ($r->user ? $r->user->name : ''),
                    $fd['surname'] ?? '',
                    $fd['gender'] ?? '',
                    $fd['father_name'] ?? '',
                    $fd['grandfather_name'] ?? '',
                    $fd['address'] ?? '',
                    $fd['mobile_no'] ?? '',
                    $fd['whatsapp'] ?? '',
                    $fd['birth_date'] ?? '',
                    $fd['age'] ?? '',
                    $fd['height'] ?? '',
                    $fd['weight'] ?? '',
                    $fd['qualification'] ?? '',
                    $fd['occupation'] ?? '',
                    $fd['occupation_address'] ?? '',
                    $fd['monthly_income'] ?? '',
                    $fd['elder_brother'] ?? '',
                    $fd['elder_brother_married'] ?? '',
                    $fd['younger_brother'] ?? '',
                    $fd['younger_brother_married'] ?? '',
                    $fd['elder_sister'] ?? '',
                    $fd['elder_sister_married'] ?? '',
                    $fd['younger_sister'] ?? '',
                    $fd['younger_sister_married'] ?? '',
                    $fd['father_occupation'] ?? '',
                    $fd['father_occupation_address'] ?? '',
                    $fd['father_mobile'] ?? '',
                    $fd['father_age'] ?? '',
                    $fd['father_income'] ?? '',
                    $fd['native_place'] ?? '',
                    $fd['mother_name'] ?? '',
                    $fd['mother_occupation'] ?? '',
                    $fd['maternal_uncle_name'] ?? '',
                    $fd['maternal_grandfather_name'] ?? '',
                    $fd['maternal_grandfather_address'] ?? '',
                    $fd['maternal_grandfather_occupation'] ?? '',
                    $fd['business'] ?? '',
                    $fd['house'] ?? '',
                    $fd['own_house'] ?? '',
                    $fd['vehicle'] ?? '',
                    $fd['divorce'] ?? '',
                    $fd['special_need'] ?? '',
                    $fd['physical_disability'] ?? '',
                    $fd['disability_duration'] ?? '',
                    $fd['other_info'] ?? '',
                    $fd['special_info'] ?? '',
                    $fd['member_photo_url'] ?? ($fd['selfie_url'] ?? ''),
                    $fd['submission_date'] ?? ($r->created_at ? $r->created_at->format('d-M-Y h:i A') : ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show registration edit form for Admin.
     */
    public function editRegistration($id)
    {
        $registration = EventRegistration::findOrFail($id);
        $event = $registration->event;
        $areas = \App\Models\Area::orderBy('name')->get();
        return view('admin.events.edit_registration', compact('registration', 'event', 'areas'));
    }

    /**
     * Update registration data by Admin.
     */
    public function updateRegistration(Request $request, $id)
    {
        $registration = EventRegistration::findOrFail($id);
        $event = $registration->event;

        $formData = $registration->form_data ?? [];

        // Merge inputs
        $inputs = $request->except(['_token', 'member_photo', 'aadhaar_photo', 'selfie', 'whatsapp_image', 'payment_image', 'marksheet_file']);
        foreach ($inputs as $k => $v) {
            if (!is_null($v)) {
                $formData[$k] = $v;
            }
        }

        if ($request->filled('area_id')) {
            $areaObj = \App\Models\Area::find($request->area_id);
            if ($areaObj) {
                $formData['area'] = $areaObj->name;
            }
        }

        // File uploads
        $fileFields = ['member_photo', 'aadhaar_photo', 'selfie', 'whatsapp_image', 'payment_image'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('yuva_melo/' . $field, $filename, 'public');
                $formData[$field . '_url'] = asset('storage/' . $path);
            }
        }

        if ($request->hasFile('marksheet_file')) {
            $file = $request->file('marksheet_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('marksheets', $filename, 'public');
            $formData['marksheet_url'] = asset('storage/' . $path);
        }

        if (!empty($formData['first_name']) || !empty($formData['surname'])) {
            $formData['full_name'] = trim(($formData['first_name'] ?? '') . ' ' . ($formData['surname'] ?? ''));
        }

        $registration->update([
            'form_data' => $formData,
        ]);

        return redirect()->route('admin.events.show', $event->id)->with('success', 'Candidate registration details updated successfully.');
    }

    /**
     * Show Live Mobile Web Camera Gate Scanner
     */
    public function showScanner($id)
    {
        $event = Event::findOrFail($id);

        // Ensure pass tokens exist for all pass registrations
        $registrations = EventRegistration::where('event_id', $event->id)->passes()->get();
        foreach ($registrations as $reg) {
            \App\Services\PassTokenService::getOrGenerateTokens($reg);
        }

        $totalPasses = $event->total_passes_count;
        $checkedInCount = $event->checked_in_passes_count;

        return view('admin.events.scanner', compact('event', 'totalPasses', 'checkedInCount'));
    }

    /**
     * Verify QR Pass Token (AJAX API Endpoint)
     */
    public function verifyPass(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $qrToken = trim((string)($request->input('qr_token') ?? $request->input('pass_code')));

        if (empty($qrToken)) {
            return response()->json([
                'success' => false,
                'status' => 'empty',
                'message' => 'No QR Code payload provided.',
                'detail' => 'Please position the QR code inside the scanner frame.',
            ], 400);
        }

        $result = \App\Services\PassTokenService::verifyAndCheckIn($qrToken, $event->id, auth()->id());

        $tokenData = null;
        if (!empty($result['token'])) {
            $t = $result['token'];
            $reg = $t->registration;
            $tokenData = [
                'pass_code' => $t->pass_code,
                'pass_index' => $t->pass_index,
                'attendee' => $reg->form_data['full_name'] ?? ($reg->user->name ?? 'Guest Attendee'),
                'mobile' => $reg->form_data['mobile'] ?? ($reg->user->phone ?? '-'),
                'is_checked_in' => $t->is_checked_in,
                'checked_in_at' => $t->checked_in_at ? $t->checked_in_at->format('d M Y, h:i A') : null,
            ];
        }

        return response()->json([
            'success' => $result['success'],
            'status' => $result['status'],
            'message' => $result['message'],
            'detail' => $result['detail'],
            'token' => $tokenData,
            'checked_in_count' => $event->checked_in_passes_count,
            'total_passes' => $event->total_passes_count,
        ]);
    }
}
