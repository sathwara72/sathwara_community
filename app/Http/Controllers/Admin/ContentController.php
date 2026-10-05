<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Agenda;
use App\Models\ManagementDesk;
use App\Models\CommitteeMember;
use App\Models\Timeline;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    // ================= SLIDERS =================
    public function sliders(Request $request)
    {
        $query = Slider::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('subtitle', 'like', "%{$request->search}%");
        }
        $sliders = $query->orderBy('display_order')->paginate(12)->withQueryString();
        return view('admin.content.sliders', compact('sliders'));
    }

    public function storeSlider(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|image|max:3072',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'display_order' => 'required|integer',
        ]);

        $path = $request->file('image')->store('content/sliders', 'public');

        Slider::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_path' => $path,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
            'status' => $request->status,
            'display_order' => $request->display_order,
        ]);

        return redirect()->back()->with('success', 'Slider created successfully.');
    }

    public function updateSlider(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:3072',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'display_order' => 'required|integer',
        ]);

        $path = $slider->image_path;
        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($slider->image_path) && !str_starts_with($slider->image_path, 'http')) {
                Storage::disk('public')->delete($slider->image_path);
            }
            $path = $request->file('image')->store('content/sliders', 'public');
        }

        $slider->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_path' => $path,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
            'status' => $request->status,
            'display_order' => $request->display_order,
        ]);

        return redirect()->route('admin.content.sliders')->with('success', 'Slider updated successfully.');
    }

    public function destroySlider($id)
    {
        $slider = Slider::findOrFail($id);
        if (Storage::disk('public')->exists($slider->image_path) && !str_starts_with($slider->image_path, 'http')) {
            Storage::disk('public')->delete($slider->image_path);
        }
        $slider->delete();
        return redirect()->back()->with('success', 'Slider deleted.');
    }

    // ================= AGENDAS =================
    public function agendas(Request $request)
    {
        $query = Agenda::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }
        $agendas = $query->orderBy('display_order')->paginate(12)->withQueryString();
        return view('admin.content.agendas', compact('agendas'));
    }

    public function storeAgenda(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_gu' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'display_order' => 'required|integer',
        ]);

        Agenda::create($request->all());
        return redirect()->back()->with('success', 'Agenda created.');
    }

    public function updateAgenda(Request $request, $id)
    {
        $agenda = Agenda::findOrFail($id);
        $request->validate([
            'title' => 'nullable|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_gu' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer',
        ]);

        $agenda->update([
            'title' => $request->input('title', $agenda->title),
            'title_gu' => $request->input('title_gu', $agenda->title_gu),
            'description' => $request->description,
            'description_gu' => $request->description_gu,
            'icon' => $request->input('icon', $agenda->icon),
            'display_order' => $request->input('display_order', $agenda->display_order),
        ]);

        return redirect()->route('admin.content.agendas')->with('success', 'Agenda updated.');
    }

    public function destroyAgenda($id)
    {
        Agenda::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Agenda deleted.');
    }

    // ================= MANAGEMENT DESK =================
    public function managementDesk(Request $request)
    {
        $query = ManagementDesk::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('name_gu', 'like', "%{$request->search}%")
                ->orWhere('designation', 'like', "%{$request->search}%")
                ->orWhere('designation_gu', 'like', "%{$request->search}%");
        }
        $members = $query->orderBy('display_order')->paginate(12)->withQueryString();
        return view('admin.content.management_desk', compact('members'));
    }

    public function storeDesk(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'designation' => 'required|string|max:255',
            'designation_gu' => 'nullable|string|max:255',
            'photo' => 'required|image|max:2048',
            'display_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $path = $request->file('photo')->store('content/desk', 'public');

        ManagementDesk::create([
            'name' => $request->name,
            'name_gu' => $request->name_gu,
            'designation' => $request->designation,
            'designation_gu' => $request->designation_gu,
            'photo_path' => $path,
            'display_order' => $request->display_order,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Desk member created.');
    }

    public function updateDesk(Request $request, $id)
    {
        $member = ManagementDesk::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'designation' => 'required|string|max:255',
            'designation_gu' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'display_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $path = $member->photo_path;
        if ($request->hasFile('photo')) {
            if (Storage::disk('public')->exists($member->photo_path) && !str_starts_with($member->photo_path, 'http')) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $path = $request->file('photo')->store('content/desk', 'public');
        }

        $member->update([
            'name' => $request->name,
            'name_gu' => $request->name_gu,
            'designation' => $request->designation,
            'designation_gu' => $request->designation_gu,
            'photo_path' => $path,
            'display_order' => $request->display_order,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.content.desk')->with('success', 'Desk entry updated.');
    }

    public function destroyDesk($id)
    {
        $member = ManagementDesk::findOrFail($id);
        if (Storage::disk('public')->exists($member->photo_path) && !str_starts_with($member->photo_path, 'http')) {
            Storage::disk('public')->delete($member->photo_path);
        }
        $member->delete();
        return redirect()->back()->with('success', 'Desk entry deleted.');
    }

    // ================= COMMITTEE MEMBERS =================
    public function committee(Request $request)
    {
        $query = CommitteeMember::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('designation', 'like', "%{$request->search}%");
        }
        $members = $query->orderBy('display_order')->paginate(12)->withQueryString();
        return view('admin.content.committee', compact('members'));
    }

    public function storeCommittee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'display_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('content/committee', 'public');
        }

        CommitteeMember::create([
            'name' => $request->name,
            'designation' => $request->designation,
            'photo_path' => $path,
            'display_order' => $request->display_order,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Committee member added.');
    }



    public function updateCommittee(Request $request, $id)
    {
        $member = CommitteeMember::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'display_order' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $path = $member->photo_path;
        if ($request->hasFile('photo')) {
            if ($member->photo_path && Storage::disk('public')->exists($member->photo_path) && !str_starts_with($member->photo_path, 'http')) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $path = $request->file('photo')->store('content/committee', 'public');
        }

        $member->update([
            'name' => $request->name,
            'designation' => $request->designation,
            'photo_path' => $path,
            'display_order' => $request->display_order,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.content.committee')->with('success', 'Committee member updated.');
    }

    public function destroyCommittee($id)
    {
        $member = CommitteeMember::findOrFail($id);
        if ($member->photo_path && Storage::disk('public')->exists($member->photo_path) && !str_starts_with($member->photo_path, 'http')) {
            Storage::disk('public')->delete($member->photo_path);
        }
        $member->delete();
        return redirect()->back()->with('success', 'Committee member removed.');
    }

    // ================= TIMELINES =================
    public function timelines(Request $request)
    {
        $query = Timeline::query();
        if ($request->filled('search')) {
            $query->where('year', 'like', "%{$request->search}%")
                ->orWhere('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }
        $timelines = $query->orderBy('display_order')->paginate(12)->withQueryString();
        return view('admin.content.timelines', compact('timelines'));
    }

    public function storeTimeline(Request $request)
    {
        $request->validate([
            'year'           => 'required|string|max:10',
            'title'          => 'required|string|max:255',
            'title_gu'       => 'nullable|string|max:255',
            'description'    => 'required|string',
            'description_gu' => 'nullable|string',
            'display_order'  => 'required|integer',
        ]);

        Timeline::create([
            'year'           => $request->year,
            'title'          => $request->title,
            'title_gu'       => $request->title_gu,
            'description'    => $request->description,
            'description_gu' => $request->description_gu,
            'display_order'  => $request->display_order,
        ]);
        return redirect()->back()->with('success', 'Timeline milestone added.');
    }

    public function updateTimeline(Request $request, $id)
    {
        $timeline = Timeline::findOrFail($id);
        $request->validate([
            'year'           => 'required|string|max:10',
            'title'          => 'required|string|max:255',
            'title_gu'       => 'nullable|string|max:255',
            'description'    => 'required|string',
            'description_gu' => 'nullable|string',
            'display_order'  => 'required|integer',
        ]);

        $timeline->update([
            'year'           => $request->year,
            'title'          => $request->title,
            'title_gu'       => $request->title_gu,
            'description'    => $request->description,
            'description_gu' => $request->description_gu,
            'display_order'  => $request->display_order,
        ]);
        return redirect()->back()->with('success', 'Timeline milestone updated.');
    }

    public function destroyTimeline($id)
    {
        Timeline::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Timeline entry deleted.');
    }

    // ================= UPDATES =================
    public function updates(Request $request)
    {
        $query = Update::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }
        $updates = $query->orderBy('publish_date', 'desc')->paginate(15)->withQueryString();
        return view('admin.content.updates', compact('updates'));
    }

    public function storeUpdate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publish_date' => 'required|date',
            'image' => 'nullable|image|max:3072',
            'status' => 'required|in:draft,published',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('content/updates', 'public');
        }

        Update::create([
            'title' => $request->title,
            'description' => $request->description,
            'publish_date' => $request->publish_date,
            'image_path' => $path,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Announcement published.');
    }



    public function updateUpdate(Request $request, $id)
    {
        $update = Update::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publish_date' => 'required|date',
            'image' => 'nullable|image|max:3072',
            'status' => 'required|in:draft,published',
        ]);

        $path = $update->image_path;
        if ($request->hasFile('image')) {
            if ($update->image_path && Storage::disk('public')->exists($update->image_path) && !str_starts_with($update->image_path, 'http')) {
                Storage::disk('public')->delete($update->image_path);
            }
            $path = $request->file('image')->store('content/updates', 'public');
        }

        $update->update([
            'title' => $request->title,
            'description' => $request->description,
            'publish_date' => $request->publish_date,
            'image_path' => $path,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.content.updates')->with('success', 'Announcement updated.');
    }

    public function destroyUpdate($id)
    {
        $update = Update::findOrFail($id);
        if ($update->image_path && Storage::disk('public')->exists($update->image_path) && !str_starts_with($update->image_path, 'http')) {
            Storage::disk('public')->delete($update->image_path);
        }
        $update->delete();
        return redirect()->back()->with('success', 'Announcement deleted.');
    }

    public function exportSlidersCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=sliders_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $query = Slider::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('subtitle', 'like', "%{$request->search}%");
        }
        $sliders = $query->orderBy('display_order')->get();

        $callback = function () use ($sliders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_title'),
                __('messages.csv_subtitle'),
                __('messages.csv_button_text'),
                __('messages.csv_button_link'),
                __('messages.csv_display_order'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($sliders as $s) {
                fputcsv($file, [
                    ++$sr,
                    $s->title ?? '',
                    $s->subtitle ?? '',
                    $s->button_text ?? '',
                    $s->button_link ?? '',
                    $s->display_order ?? 0,
                    $s->status ? __('messages.active') : __('messages.inactive'),
                    $s->created_at ? $s->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportAgendasCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=agendas_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $query = Agenda::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }
        $agendas = $query->orderBy('display_order')->get();

        $callback = function () use ($agendas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_title'),
                __('messages.csv_description'),
                __('messages.csv_icon'),
                __('messages.csv_display_order'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($agendas as $a) {
                fputcsv($file, [
                    ++$sr,
                    $a->title ?? '',
                    $a->description ?? '',
                    $a->icon ?? '',
                    $a->display_order ?? 0,
                    $a->status ? __('messages.active') : __('messages.inactive'),
                    $a->created_at ? $a->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportDeskCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=management_desk_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $desks = ManagementDesk::orderBy('display_order')->get();

        $callback = function () use ($desks) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_name'),
                __('messages.csv_designation'),
                __('messages.csv_message'),
                __('messages.csv_display_order'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($desks as $d) {
                fputcsv($file, [
                    ++$sr,
                    $d->name ?? '',
                    $d->designation ?? '',
                    $d->message ?? '',
                    $d->display_order ?? 0,
                    $d->status ? __('messages.active') : __('messages.inactive'),
                    $d->created_at ? $d->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportCommitteeCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=committee_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $committee = CommitteeMember::orderBy('display_order')->get();

        $callback = function () use ($committee) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_name'),
                __('messages.csv_designation'),
                __('messages.csv_phone'),
                __('messages.csv_email'),
                __('messages.csv_city'),
                __('messages.csv_display_order'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($committee as $c) {
                fputcsv($file, [
                    ++$sr,
                    $c->name ?? '',
                    $c->designation ?? '',
                    $c->phone ?? '',
                    $c->email ?? '',
                    $c->city ?? '',
                    $c->display_order ?? 0,
                    $c->status ? __('messages.active') : __('messages.inactive'),
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportTimelinesCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=timelines_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $timelines = Timeline::orderBy('year', 'desc')->get();

        $callback = function () use ($timelines) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_year'),
                __('messages.csv_title'),
                __('messages.csv_description'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($timelines as $t) {
                fputcsv($file, [
                    ++$sr,
                    $t->year ?? '',
                    $t->title ?? '',
                    $t->description ?? '',
                    $t->status ? __('messages.active') : __('messages.inactive'),
                    $t->created_at ? $t->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportUpdatesCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=updates_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $updates = Update::orderBy('publish_date', 'desc')->get();

        $callback = function () use ($updates) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_title'),
                __('messages.csv_description'),
                __('messages.csv_publish_date'),
                __('messages.csv_status'),
                __('messages.csv_created_at')
            ]);
            $sr = 0;
            foreach ($updates as $u) {
                $statusKey = strtolower($u->status ?? 'active');
                fputcsv($file, [
                    ++$sr,
                    $u->title ?? '',
                    strip_tags(html_entity_decode($u->description ?? '')),
                    $u->publish_date ?? '',
                    __('messages.' . $statusKey) != 'messages.' . $statusKey ? __('messages.' . $statusKey) : ucfirst($u->status ?? 'active'),
                    $u->created_at ? $u->created_at->format('Y-m-d H:i') : ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
