<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Services\PaymentMailer;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSponsor;
use App\Models\SponsorshipType;
use App\Mail\SponsorshipReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SponsorshipController extends Controller
{
    /**
     * Store a new Sponsorship Type
     */
    public function storeType(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'max_sponsors' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ]);

        $validated['event_id'] = $event->id;
        $validated['status'] = $request->has('status') ? (bool)$request->status : true;
        $validated['max_sponsors'] = !empty($validated['max_sponsors']) ? (int)$validated['max_sponsors'] : 0;
        $validated['display_order'] = !empty($validated['display_order']) ? (int)$validated['display_order'] : 0;

        SponsorshipType::create($validated);

        return redirect()->route('admin.events.show', ['event' => $event->id, 'tab' => 'sponsorship', 'subtab' => 'types'])
            ->with('success', __('messages.sponsorship_type_created') ?? 'Sponsorship type created successfully.');
    }

    /**
     * Update an existing Sponsorship Type
     */
    public function updateType(Request $request, $id)
    {
        $type = SponsorshipType::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'max_sponsors' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ]);

        $validated['status'] = $request->has('status') ? (bool)$request->status : $type->status;
        $validated['max_sponsors'] = !empty($validated['max_sponsors']) ? (int)$validated['max_sponsors'] : 0;
        $validated['display_order'] = !empty($validated['display_order']) ? (int)$validated['display_order'] : $type->display_order;

        $type->update($validated);

        return redirect()->route('admin.events.show', ['event' => $type->event_id, 'tab' => 'sponsorship', 'subtab' => 'types'])
            ->with('success', __('messages.sponsorship_type_updated') ?? 'Sponsorship type updated successfully.');
    }

    /**
     * Delete a Sponsorship Type
     */
    public function destroyType($id)
    {
        $type = SponsorshipType::findOrFail($id);
        $eventId = $type->event_id;
        $type->delete();

        return redirect()->route('admin.events.show', ['event' => $eventId, 'tab' => 'sponsorship', 'subtab' => 'types'])
            ->with('success', __('messages.sponsorship_type_deleted') ?? 'Sponsorship type deleted successfully.');
    }

    /**
     * Toggle Sponsorship Type active/inactive status
     */
    public function toggleTypeStatus($id)
    {
        $type = SponsorshipType::findOrFail($id);
        $type->status = !$type->status;
        $type->save();

        return response()->json([
            'success' => true,
            'status' => $type->status,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Admin manually store a new Sponsor
     */
    public function storeSponsor(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:255',
            'sponsorship_type_id' => 'nullable|exists:sponsorship_types,id',
            'amount' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|max:4096',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'payment_status' => 'required|in:pending,received,failed',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('sponsors', 'public');
        }

        // Auto-fill amount if empty but sponsorship_type is selected
        $amount = !empty($validated['amount']) ? (float)$validated['amount'] : 0.00;
        if ($amount <= 0 && !empty($validated['sponsorship_type_id'])) {
            $type = SponsorshipType::find($validated['sponsorship_type_id']);
            if ($type) {
                $amount = (float)$type->amount;
            }
        }

        $sponsor = EventSponsor::create([
            'event_id' => $event->id,
            'sponsorship_type_id' => $validated['sponsorship_type_id'] ?? null,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'amount' => $amount,
            'logo_path' => $logoPath,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'payment_status' => $validated['payment_status'],
            'status' => $validated['status'],
        ]);

        // Dispatch Sponsorship Receipt Email if email is present
        if ($validated['payment_status'] === 'received') {
            Transaction::recordOffice('sponsorship', (float) $amount, $sponsor, $sponsor->name, $sponsor->mobile);
        }

        $st = !empty($validated['sponsorship_type_id']) ? SponsorshipType::find($validated['sponsorship_type_id']) : null;
        PaymentMailer::send($validated['email'] ?? null, new SponsorshipReceiptMail($event, $sponsor, $st, $amount, $validated['payment_status'], null), 'Sponsorship (added by admin)', [
            'Event' => $event->title,
            'Sponsor' => $sponsor->name,
            'Amount' => '₹' . number_format((float) $amount, 2),
            'Mobile' => $sponsor->mobile,
        ]);

        return redirect()->route('admin.events.show', ['event' => $event->id, 'tab' => 'sponsorship', 'subtab' => 'sponsors'])
            ->with('success', __('messages.sponsor_registered_successfully') ?? 'Sponsor registered successfully.');
    }

    /**
     * Update an existing Sponsor
     */
    public function updateSponsor(Request $request, $id)
    {
        $sponsor = EventSponsor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:255',
            'sponsorship_type_id' => 'nullable|exists:sponsorship_types,id',
            'amount' => 'required|numeric|min:0',
            'logo' => 'nullable|image|max:4096',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'payment_status' => 'required|in:pending,received,failed',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($request->hasFile('logo')) {
            if ($sponsor->logo_path && Storage::disk('public')->exists($sponsor->logo_path)) {
                Storage::disk('public')->delete($sponsor->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('sponsors', 'public');
        }

        $sponsor->update($validated);

        // Marked as received at the office for the first time: record the payment
        if ($validated['payment_status'] === 'received' && !$sponsor->transactions()->exists()) {
            Transaction::recordOffice('sponsorship', (float) $sponsor->amount, $sponsor, $sponsor->name, $sponsor->mobile);
        }

        return redirect()->route('admin.events.show', ['event' => $sponsor->event_id, 'tab' => 'sponsorship', 'subtab' => 'sponsors'])
            ->with('success', __('messages.sponsor_updated_successfully') ?? 'Sponsor details updated successfully.');
    }

    /**
     * Approve Sponsor
     */
    public function approveSponsor($id)
    {
        $sponsor = EventSponsor::findOrFail($id);
        $sponsor->status = 'approved';
        $sponsor->save();

        return redirect()->back()->with('success', __('messages.sponsor_approved') ?? 'Sponsor approved successfully.');
    }

    /**
     * Reject Sponsor
     */
    public function rejectSponsor($id)
    {
        $sponsor = EventSponsor::findOrFail($id);
        $sponsor->status = 'rejected';
        $sponsor->save();

        return redirect()->back()->with('success', __('messages.sponsor_rejected') ?? 'Sponsor marked as rejected.');
    }

    /**
     * Delete Sponsor
     */
    public function destroySponsor($id)
    {
        $sponsor = EventSponsor::findOrFail($id);
        $eventId = $sponsor->event_id;

        if ($sponsor->logo_path && Storage::disk('public')->exists($sponsor->logo_path)) {
            Storage::disk('public')->delete($sponsor->logo_path);
        }

        $sponsor->delete();

        return redirect()->route('admin.events.show', ['event' => $eventId, 'tab' => 'sponsorship', 'subtab' => 'sponsors'])
            ->with('success', __('messages.sponsor_deleted_successfully') ?? 'Sponsor deleted successfully.');
    }

    /**
     * Export Registered Sponsors to CSV
     */
    public function exportSponsorsCsv($eventId)
    {
        $event = Event::findOrFail($eventId);
        $sponsors = EventSponsor::where('event_id', $event->id)
            ->with(['sponsorshipType', 'user', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => $event->exportDisposition('sponsors'),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($sponsors) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Sr. No.',
                'Sponsor / Organization Name',
                'Contact Person',
                'Mobile Number',
                'Email',
                'Sponsorship Type',
                'Amount (INR)',
                'City / Area',
                'Address',
                'Payment Status',
                'Transaction ID',
                'Approval Status',
                'Notes',
                'Registered Date',
            ]);

            $sr = 0;
            foreach ($sponsors as $s) {
                fputcsv($handle, [
                    ++$sr,
                    $s->name,
                    $s->contact_person ?? '-',
                    $s->mobile,
                    $s->email ?? '-',
                    $s->sponsorshipType ? $s->sponsorshipType->title : 'General Sponsor',
                    number_format($s->amount, 2, '.', ''),
                    $s->city ?? '-',
                    $s->address ?? '-',
                    ucfirst($s->payment_status),
                    $s->transactions->pluck('transaction_no')->implode(', '),
                    ucfirst($s->status),
                    $s->notes ?? '-',
                    $s->created_at ? $s->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
