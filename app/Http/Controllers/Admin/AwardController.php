<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AwardApplication;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    /**
     * List Applications
     */
    public function index(Request $request)
    {
        $query = AwardApplication::with('user.memberProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('school', 'like', "%{$search}%")
                  ->orWhere('award_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.awards.index', compact('applications'));
    }

    /**
     * Approve Application
     */
    public function approve(Request $request, $id)
    {
        $application = AwardApplication::findOrFail($id);
        $application->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes ?? 'Criteria satisfied.',
        ]);

        return redirect()->back()->with('success', 'Award application approved.');
    }

    /**
     * Reject Application
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $application = AwardApplication::findOrFail($id);
        $application->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()->with('warning', 'Award application rejected.');
    }

    /**
     * Delete Application
     */
    public function destroy($id)
    {
        $application = AwardApplication::findOrFail($id);
        $application->delete();

        return redirect()->route('admin.awards.index')->with('success', 'Award application record removed.');
    }

    /**
     * Export Awards Applications CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=awards_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $query = AwardApplication::with('user.memberProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('school', 'like', "%{$search}%")
                  ->orWhere('award_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_member_code'),
                __('messages.csv_student_name'),
                __('messages.csv_parent_name'),
                __('messages.csv_standard'),
                __('messages.csv_school'),
                __('messages.csv_achievement'),
                __('messages.csv_award_name'),
                __('messages.csv_contact_no'),
                __('messages.csv_certificate_url'),
                __('messages.csv_status'),
                __('messages.csv_submission_date')
            ]);

            $sr = 0;
            foreach ($applications as $app) {
                $statusKey = strtolower($app->status ?? '');
                $user = $app->user;
                fputcsv($file, [
                    ++$sr,
                    $user ? $user->formatted_member_id : '',
                    $app->student_name,
                    $user ? $user->name : '',
                    $app->standard ?? '',
                    $app->school ?? '',
                    $app->achievement ?? '',
                    $app->award_name ?? '',
                    $user->memberProfile->phone ?? ($user->phone ?? ''),
                    $app->certificate_path ? asset('storage/' . $app->certificate_path) : '',
                    __('messages.' . $statusKey) != 'messages.' . $statusKey ? __('messages.' . $statusKey) : ucfirst($app->status),
                    $app->created_at ? $app->created_at->format('d-M-Y') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
