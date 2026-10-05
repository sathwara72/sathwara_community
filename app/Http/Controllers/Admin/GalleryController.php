<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * General Gallery Index
     */
    public function index(Request $request)
    {
        $query = Gallery::whereNull('event_id');
        if ($request->filled('search')) {
            $query->where('caption', 'like', "%{$request->search}%");
        }
        $photos = $query->orderBy('display_order')->paginate(15)->withQueryString();
        return view('admin.gallery.index', compact('photos'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $userPerms = $user->permissions->pluck('name');
        if (!$user->hasRole('Administrator') && !$userPerms->contains('gallery_manage') && !$userPerms->contains('gallery_add')) {
            abort(403, 'You do not have permission to add gallery photos.');
        }

        // Pre-check for PHP file upload errors (e.g. Disk Full, Temp Dir errors)
        $filesToCheck = [];
        if ($request->hasFile('images')) {
            $filesToCheck = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
        } elseif ($request->hasFile('image')) {
            $filesToCheck = [$request->file('image')];
        }

        foreach ($filesToCheck as $file) {
            if ($file && !$file->isValid()) {
                $errorCode = $file->getError();
                if ($errorCode === UPLOAD_ERR_CANT_WRITE) {
                    return redirect()->back()->withErrors([
                        'images' => 'File upload failed: Server disk space (C: drive) is completely FULL! Please free up disk space on your computer.'
                    ])->withInput();
                } elseif ($errorCode === UPLOAD_ERR_NO_TMP_DIR) {
                    return redirect()->back()->withErrors([
                        'images' => 'File upload failed: PHP temporary folder is missing or not writable.'
                    ])->withInput();
                }
            }
        }

        $request->validate([
            'image' => 'nullable|file|mimes:zip,jpeg,png,jpg,gif,svg,webp,mp4,mov,webm,ogg,m4v,avi,mkv|max:102400',
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,mov,webm,ogg,m4v,avi,mkv,zip|max:102400',
            'caption' => 'nullable|string|max:255',
        ], [
            'images.*.uploaded' => 'One of the files failed to upload. Please verify that your file is under 100MB and your drive has free space.',
            'images.*.mimes' => 'All files must be valid photos or videos (JPG, PNG, WebP, GIF, MP4, MOV, WebM, etc.).',
            'images.*.max' => 'Each photo/video file must be less than 100MB in size.',
            'image.uploaded' => 'The file failed to upload. Please verify that your computer drive has free disk space.',
        ]);

        $eventId = $request->input('event_id');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'zip') {
                if (!class_exists('\ZipArchive')) {
                    return redirect()->back()->with('error', 'PHP ZipArchive extension is not enabled on this server.');
                }

                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    $tempPath = storage_path('app/temp_zip_' . time());
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
                    $uploadedCount = 0;

                    foreach ($files as $name => $f) {
                        if (!$f->isDir()) {
                            $filePath = $f->getRealPath();
                            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                            if (in_array($fileExtension, $allowedExtensions)) {
                                $fileName = 'zip_' . uniqid() . '.' . $fileExtension;
                                $destinationDir = $eventId ? 'events/gallery' : 'gallery/general';
                                Storage::disk('public')->makeDirectory($destinationDir);

                                $publicPath = $destinationDir . '/' . $fileName;
                                Storage::disk('public')->put($publicPath, file_get_contents($filePath));

                                Gallery::create([
                                    'event_id' => $eventId,
                                    'image_path' => $publicPath,
                                    'caption' => $request->caption ?? ($eventId ? 'Event Media' : 'Gallery Media'),
                                    'display_order' => Gallery::where('event_id', $eventId)->max('display_order') + 1,
                                ]);
                                $uploadedCount++;
                            }
                        }
                    }

                    $this->deleteDir($tempPath);

                    if ($uploadedCount === 0) {
                        return redirect()->back()->with('error', 'No valid photos or videos found in the ZIP archive.');
                    }

                    return redirect()->back()->with('success', "$uploadedCount media files extracted and uploaded successfully from ZIP archive.");
                } else {
                    return redirect()->back()->with('error', 'Failed to open the ZIP file.');
                }
            } else {
                $path = $file->store($eventId ? 'events/gallery' : 'gallery/general', 'public');
                Gallery::create([
                    'event_id' => $eventId,
                    'image_path' => $path,
                    'caption' => $request->caption ?? 'Community Media',
                    'display_order' => Gallery::where('event_id', $eventId)->max('display_order') + 1,
                ]);
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store($eventId ? 'events/gallery' : 'gallery/general', 'public');
                Gallery::create([
                    'event_id' => $eventId,
                    'image_path' => $path,
                    'caption' => $request->caption ?? 'Community Media',
                    'display_order' => Gallery::where('event_id', $eventId)->max('display_order') + 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Media added successfully to gallery.');
    }

    /**
     * Delete Photo
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $userPerms = $user->permissions->pluck('name');
        if (!$user->hasRole('Administrator') && !$userPerms->contains('gallery_manage') && !$userPerms->contains('gallery_delete')) {
            abort(403, 'You do not have permission to delete gallery photos.');
        }

        $photo = Gallery::findOrFail($id);

        if (Storage::disk('public')->exists($photo->image_path) && !str_starts_with($photo->image_path, 'http')) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();
        return redirect()->back()->with('success', 'Photo deleted successfully.');
    }

    /**
     * Recursive folder deletion helper
     */
    private function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Export General Gallery CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=gallery_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $query = Gallery::whereNull('event_id');
        if ($request->filled('search')) {
            $query->where('caption', 'like', "%{$request->search}%");
        }
        $photos = $query->orderBy('display_order')->get();

        $callback = function() use ($photos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_caption'),
                __('messages.csv_image_path'),
                __('messages.csv_display_order'),
                __('messages.csv_uploaded_at')
            ]);

            $sr = 0;
            foreach ($photos as $p) {
                fputcsv($file, [
                    ++$sr,
                    $p->caption ?? '',
                    $p->image_path ?? '',
                    $p->display_order ?? 0,
                    $p->created_at ? $p->created_at->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
