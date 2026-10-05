<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Services\PaymentMailer;
use App\Models\Setting;
use App\Mail\ApplicationRejectedMail;
use App\Services\RazorpayVerifier;
use App\Http\Controllers\Controller;
use App\Mail\BusinessPaymentLinkMail;
use App\Mail\BusinessRenewalReceiptMail;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessPaymentLink;
use App\Services\RazorpayPaymentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    /**
     * List Businesses
     */
    public function index(Request $request)
    {
        $query = Business::with(['category', 'area']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $businesses = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $categories = BusinessCategory::withCount('businesses')->orderBy('name')->get();

        $pendingCount = Business::where('status', 'pending')->count();
        $approvedCount = Business::where('status', 'approved')->count();
        $rejectedCount = Business::where('status', 'rejected')->count();
        $totalCount = Business::count();
        $deletedCount = Business::onlyTrashed()->count();

        return view('admin.businesses.index', compact('businesses', 'categories', 'pendingCount', 'approvedCount', 'rejectedCount', 'totalCount', 'deletedCount'));
    }

    /**
     * Approve Business
     */
    public function approve($id)
    {
        $business = Business::findOrFail($id);
        $business->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'membership_status' => 'active',
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Business directory entry approved successfully.');
    }

    /**
     * Reject Business
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:2000']);

        $business = Business::findOrFail($id);
        $business->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'membership_status' => 'inactive',
            'approved_at' => null,
        ]);

        $this->sendRejectionEmail($business->email, 'business', $business->owner_name ?: $business->business_name, $business->business_name, $request->rejection_reason);

        return redirect()->route('admin.businesses.show', $business->id)->with('warning', 'Business directory entry rejected and the owner has been emailed the reason.');
    }

    /**
     * Deactivate Business
     */
    public function deactivate($id)
    {
        $business = Business::findOrFail($id);
        $business->update([
            'membership_status' => 'inactive',
        ]);

        return redirect()->back()->with('warning', 'Business directory entry marked as inactive.');
    }

    /**
     * Activate Business
     */
    public function activate($id)
    {
        $business = Business::findOrFail($id);
        $business->update([
            'membership_status' => 'active',
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Business directory entry marked as active.');
    }

    /**
     * Delete Business
     */
    public function destroy($id)
    {
        $business = Business::findOrFail($id);
        $business->delete(); // soft delete: payments, history and files are kept and it can be restored

        return redirect()->route('admin.businesses.index')->with('success', 'Business moved to Deleted. You can restore it from the Deleted tab.');
    }

    /**
     * Bring back a soft-deleted business (unless its login email is now used by another business).
     */
    public function restore($id)
    {
        $business = Business::onlyTrashed()->findOrFail($id);

        if ($business->email && Business::where('email', $business->email)->exists()) {
            return redirect()->back()->with('error', "Cannot restore: another business now uses the login email {$business->email}. Change that business's email first.");
        }

        $business->restore();

        return redirect()->route('admin.businesses.show', $business->id)->with('success', 'Business restored.');
    }

    /**
     * Show Business
     */
    public function show($id)
    {
        // Deleted businesses can still be opened by admins (to review or restore them)
        $business = Business::withTrashed()->with(['category', 'paymentLinks'])->findOrFail($id);
        return view('admin.businesses.show', compact('business'));
    }

    /**
     * Generate a 24-hour Razorpay payment link for a business renewal and email it.
     */
    public function generatePaymentLink(Request $request, $id)
    {
        $business = Business::findOrFail($id);

        if (!$business->isRenewalDue()) {
            return redirect()->back()->with('error', 'This business\'s 1-year approval period has not completed yet — renewal payment links can only be generated once it has expired.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $result = app(RazorpayPaymentLinkService::class)->createLink($business, (float) $request->amount);
        } catch (\Throwable $e) {
            Log::error('Generate business payment link failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Razorpay payment link. Please check the Razorpay credentials in Settings and try again.');
        }

        $link = BusinessPaymentLink::create([
            'business_id' => $business->id,
            'amount' => $request->amount,
            'razorpay_link_id' => $result['id'],
            'razorpay_link_url' => $result['short_url'],
            'status' => 'created',
            'created_by' => auth()->id(),
            'expires_at' => $result['expires_at'],
        ]);

        if (!empty($business->email)) {
            try {
                Mail::to($business->email)->send(new BusinessPaymentLinkMail($business, $link));
            } catch (\Throwable $e) {
                Log::error('Business Payment Link Mail Error: ' . $e->getMessage());
                return redirect()->back()->with('warning', 'Payment link generated, but the email could not be sent. You can copy/share it manually or use Resend Email.');
            }
        }

        return redirect()->back()->with('success', 'Payment link generated and emailed to the business (valid 24 hours).');
    }

    /**
     * Resend the payment link email for an existing link.
     */
    public function resendPaymentLinkEmail($id, $linkId)
    {
        $business = Business::findOrFail($id);
        $link = BusinessPaymentLink::where('business_id', $business->id)->findOrFail($linkId);

        if (empty($business->email)) {
            return redirect()->back()->with('error', 'This business has no email address on file.');
        }

        try {
            Mail::to($business->email)->send(new BusinessPaymentLinkMail($business, $link));
        } catch (\Throwable $e) {
            Log::error('Resend Business Payment Link Mail Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to resend the payment link email.');
        }

        return redirect()->back()->with('success', 'Payment link email resent.');
    }

    /**
     * Manually mark a payment link as paid (fallback if the Razorpay webhook is not configured/reachable).
     */
    public function markPaymentLinkPaid(Request $request, $id, $linkId)
    {
        $business = Business::findOrFail($id);
        $link = BusinessPaymentLink::where('business_id', $business->id)->findOrFail($linkId);

        $request->validate([
            'razorpay_payment_id' => 'nullable|string|max:255',
        ]);

        if ($link->status === 'paid') {
            return redirect()->back()->with('warning', 'This payment link is already marked as paid.');
        }

        $link->status = 'paid';
        $link->paid_at = now();
        $link->razorpay_payment_id = $request->razorpay_payment_id ?: $link->razorpay_payment_id;
        $link->save();

        $business->approved_at = now();
        $business->status = 'approved';
        $business->membership_status = 'active';
        $business->payment_status = 'paid';
        $business->payment_id = $link->razorpay_payment_id;
        $business->payment_amount = $link->amount;
        $business->save();

        $payer = $business->owner_name . ' (' . $business->business_name . ')';
        if ($link->razorpay_payment_id) {
            app(RazorpayVerifier::class)->record($link->razorpay_payment_id, 'business_renewal', (float) $link->amount, $business, $payer, $business->phone);
        } else {
            Transaction::recordOffice('business_renewal', (float) $link->amount, $business, $payer, $business->phone);
        }

        Log::info('Business payment link manually marked paid by admin', [
            'business_id' => $business->id,
            'link_id' => $link->id,
            'admin_id' => auth()->id(),
        ]);

        PaymentMailer::send($business->email, new BusinessRenewalReceiptMail($business, $link), 'Business renewal', [
            'Business' => $business->business_name,
            'Amount' => '₹' . number_format((float) $link->amount, 2),
            'Payment ID' => $link->razorpay_payment_id,
            'Phone' => $business->phone,
        ]);

        return redirect()->back()->with('success', 'Business renewal marked as paid and receipt emailed.');
    }

    /**
     * Edit Business
     */
    public function edit($id)
    {
        $business = Business::findOrFail($id);
        $categories = BusinessCategory::orderBy('name')->get();
        $areas = \App\Models\Area::orderBy('name')->get();
        return view('admin.businesses.edit', compact('business', 'categories', 'areas'));
    }

    /**
     * Update Business
     */
    public function update(Request $request, $id)
    {
        $business = Business::findOrFail($id);

        $request->validate([
            'member_id' => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:business_categories,id',
            'area_id' => 'required|exists:areas,id',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'phone' => 'required|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'membership_status' => 'required|in:active,inactive',
            'approved_at' => 'nullable|date',
            'logo' => 'nullable|image|max:2048',
            'payment_screenshot' => 'nullable|image|max:2048',
            'gallery' => 'nullable|array|max:6',
            'gallery.*' => 'nullable|image|max:10240',
        ]);

        $data = [
            'area_id' => $request->area_id,
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ];

        if ($request->filled('member_id')) {
            $rawMemberId = trim($request->member_id);
            $numericId = (int) preg_replace('/[^0-9]/', '', $rawMemberId);

            $memberUser = null;
            if ($numericId > 0) {
                $memberUser = \App\Models\User::find($numericId);
            }
            if (!$memberUser) {
                $profile = \App\Models\MemberProfile::where('id', $numericId)
                            ->orWhere('phone', $rawMemberId)
                            ->first();
                if ($profile) {
                    $memberUser = $profile->user;
                }
            }

            if (!$memberUser) {
                return back()->withInput()->withErrors([
                    'member_id' => 'Entered Member ID (' . $request->member_id . ') does not exist in the database.',
                ]);
            }

            $data['user_id'] = $memberUser->id;
            $data['member_id'] = $rawMemberId;
        } else {
            $data['user_id'] = null;
            $data['member_id'] = null;
        }

        $data = array_merge($data, [
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'youtube' => $request->youtube,
            'linkedin' => $request->linkedin,
            'status' => $request->status,
            'membership_status' => $request->membership_status,
        ]);

        if ($request->filled('approved_at')) {
            $data['approved_at'] = $request->approved_at;
        } elseif ($request->status === 'approved' && !$business->approved_at) {
            $data['approved_at'] = now();
        } elseif ($request->status !== 'approved') {
            $data['approved_at'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('businesses/logos', 'public');
        }

        if ($request->hasFile('payment_screenshot')) {
            if ($business->payment_screenshot_path && Storage::disk('public')->exists($business->payment_screenshot_path)) {
                Storage::disk('public')->delete($business->payment_screenshot_path);
            }
            $data['payment_screenshot_path'] = $request->file('payment_screenshot')->store('businesses/payments', 'public');
        }

        if ($request->has('remove_gallery_images')) {
            $galleryPaths = $business->gallery_images ?? [];
            foreach ($request->remove_gallery_images as $imgToRemove) {
                if (in_array($imgToRemove, $galleryPaths)) {
                    if (Storage::disk('public')->exists($imgToRemove)) {
                        Storage::disk('public')->delete($imgToRemove);
                    }
                    $galleryPaths = array_diff($galleryPaths, [$imgToRemove]);
                }
            }
            $data['gallery_images'] = array_values($galleryPaths);
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = $data['gallery_images'] ?? ($business->gallery_images ?? []);
            $remainingSlots = max(0, 6 - count($galleryPaths));
            if ($remainingSlots > 0) {
                $files = array_slice($request->file('gallery'), 0, $remainingSlots);
                foreach ($files as $file) {
                    $path = $file->store('businesses/gallery', 'public');
                    $galleryPaths[] = $path;
                }
            }
            $data['gallery_images'] = array_values($galleryPaths);
        }

        $business->update($data);

        return redirect()->back()->with('success', 'Business directory entry updated successfully.');
    }

    /**
     * Category Index & Store
     */
    public function categories()
    {
        $categories = BusinessCategory::withCount('businesses')->orderBy('name')->paginate(10)->withQueryString();
        return view('admin.businesses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:business_categories,name',
        ]);

        BusinessCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.businesses.index')->with('success', 'Category created successfully.');
    }

    public function editCategory($id)
    {
        $category = BusinessCategory::findOrFail($id);
        return view('admin.businesses.edit_category', compact('category'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = BusinessCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:business_categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.businesses.index')->with('success', 'Category updated successfully.');
    }

    public function destroyCategory($id)
    {
        $category = BusinessCategory::findOrFail($id);
        
        if ($category->businesses()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category containing registered businesses.');
        }

        $category->delete();
        return redirect()->route('admin.businesses.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Export Businesses CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=businesses_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $query = Business::with(['category', 'area']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $businesses = $query->orderBy('created_at', 'desc')->get();

        $callback = function() use ($businesses) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                __('messages.csv_sr_no'),
                __('messages.csv_business_name'),
                __('messages.csv_owner_name'),
                __('messages.csv_category'),
                __('messages.csv_phone'),
                __('messages.csv_email'),
                __('messages.csv_area'),
                __('messages.csv_city'),
                __('messages.csv_state'),
                __('messages.csv_status'),
                __('messages.csv_membership_started'),
                __('messages.csv_membership_expires')
            ]);

            $sr = 0;
            foreach ($businesses as $b) {
                $statusKey = strtolower($b->status ?? '');
                fputcsv($file, [
                    ++$sr,
                    $b->business_name,
                    $b->owner_name,
                    $b->category ? $b->category->name : '',
                    $b->phone ?? '',
                    $b->email ?? '',
                    $b->area ? $b->area->name : '',
                    $b->city ?? '',
                    $b->state ?? '',
                    __('messages.' . $statusKey) != 'messages.' . $statusKey ? __('messages.' . $statusKey) : ucfirst($b->status),
                    // Membership runs one year from approval / last renewal
                    $b->approved_at ? $b->approved_at->format('d-M-Y') : '',
                    $b->approved_at ? $b->approved_at->copy()->addYear()->format('d-M-Y') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Email the applicant why they were rejected. A mail failure never blocks the admin action.
     */
    private function sendRejectionEmail(?string $email, string $kind, string $name, string $applicationName, string $reason): void
    {
        if (empty($email)) {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicationRejectedMail(
                $kind, $name, $applicationName, $reason,
                Setting::get('contact_email'), Setting::get('contact_phone')
            ));
        } catch (\Throwable $e) {
            Log::error('Rejection email failed: ' . $e->getMessage(), ['email' => $email, 'kind' => $kind]);
        }
    }

}
