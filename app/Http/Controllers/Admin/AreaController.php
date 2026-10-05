<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display Area Management List
     */
    public function index(Request $request)
    {
        $query = Area::withCount('memberProfiles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "%{$search}%");
            });
        }

        $areas = $query->orderBy('name')->paginate(15)->withQueryString();
        $totalAreas = Area::count();

        return view('admin.areas.index', compact('areas', 'totalAreas'));
    }

    /**
     * Store New Area
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'pincode' => 'nullable|string|max:10',
        ]);

        Area::create([
            'name' => $request->name,
            'pincode' => $request->pincode,
        ]);

        return redirect()->route('admin.areas.index')->with('success', 'Area added successfully.');
    }

    /**
     * Update Existing Area
     */
    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
            'pincode' => 'nullable|string|max:10',
        ]);

        $area->update([
            'name' => $request->name,
            'pincode' => $request->pincode,
        ]);

        return redirect()->route('admin.areas.index')->with('success', 'Area updated successfully.');
    }

    /**
     * Delete Area
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        
        if ($area->memberProfiles()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete area as it is currently assigned to registered members.');
        }

        $area->delete();
        return redirect()->route('admin.areas.index')->with('success', 'Area deleted successfully.');
    }

    /**
     * Export Areas CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=areas_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $query = Area::withCount('memberProfiles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "%{$search}%");
            });
        }

        $areas = $query->orderBy('name')->get();

        $callback = function() use ($areas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_area_name'),
                __('messages.csv_pincode'),
                __('messages.csv_assigned_members_count'),
                __('messages.csv_created_at')
            ]);

            $sr = 0;
            foreach ($areas as $area) {
                fputcsv($file, [
                    ++$sr,
                    $area->name,
                    $area->pincode ?? 'N/A',
                    $area->member_profiles_count ?? 0,
                    $area->created_at ? $area->created_at->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Sample CSV for Area Import
     */
    public function downloadSampleCsv()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=areas_sample_template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Area Name', 'Pincode']);
            fputcsv($file, ['Bapunagar', '380024']);
            fputcsv($file, ['Nikol', '382350']);
            fputcsv($file, ['Satellite', '380015']);
            fputcsv($file, ['Naroda', '382330']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Areas from CSV / Excel file
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.mimes'    => 'The file must be a valid CSV format (.csv).',
            'csv_file.max'      => 'The file size must not exceed 5MB.',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to read the uploaded CSV file.');
        }

        // Read first bytes to strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $isFirstRow = true;
        $nameColIndex = 0;
        $pincodeColIndex = 1;

        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            // If comma delimiter yielded 1 element with semicolons, try splitting by semicolon
            if (count($row) === 1 && str_contains($row[0], ';')) {
                $row = str_getcsv($row[0], ';');
            }

            // Skip empty rows
            if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                continue;
            }

            // Detect and skip header row
            if ($isFirstRow) {
                $isFirstRow = false;
                $firstCell = mb_strtolower(trim($row[0] ?? ''));
                
                // If header row has column headers like 'area_name', 'name', 'વિસ્તાર', etc.
                if (str_contains($firstCell, 'name') || str_contains($firstCell, 'area') || str_contains($firstCell, 'વિસ્તાર') || str_contains($firstCell, 'id')) {
                    // Check if ID column exists at index 0 and Name at index 1
                    if ($firstCell === 'id' && isset($row[1])) {
                        $nameColIndex = 1;
                        $pincodeColIndex = 2;
                    }
                    continue; // Skip header
                }
            }

            $areaName = trim($row[$nameColIndex] ?? '');
            $pincode = trim($row[$pincodeColIndex] ?? '');

            // Clean up pincode 'N/A' or empty
            if (in_array(strtoupper($pincode), ['N/A', 'NULL', '-'])) {
                $pincode = null;
            }

            if (empty($areaName)) {
                $skippedCount++;
                continue;
            }

            // Find existing area (case-insensitive)
            $existingArea = Area::whereRaw('LOWER(name) = ?', [mb_strtolower($areaName)])->first();

            if ($existingArea) {
                // If existing area has no pincode and new pincode is provided, update it
                if (!empty($pincode) && empty($existingArea->pincode)) {
                    $existingArea->update(['pincode' => substr($pincode, 0, 10)]);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                // Create new Area
                Area::create([
                    'name'    => $areaName,
                    'pincode' => !empty($pincode) ? substr($pincode, 0, 10) : null,
                ]);
                $importedCount++;
            }
        }

        fclose($handle);

        $message = "Import completed: {$importedCount} new area(s) added.";
        if ($updatedCount > 0) {
            $message .= " {$updatedCount} area(s) updated.";
        }
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} existing/empty row(s) skipped.";
        }

        return redirect()->route('admin.areas.index')->with('success', $message);
    }
}
