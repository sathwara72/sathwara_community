<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventPassPaymentEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
    }

    private function createTestEvent(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Test Event',
            'description' => 'Event description',
            'banner_path' => 'events/banner.jpg',
            'venue' => 'Grand Hall',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '10:00:00',
            'event_type' => 'normal',
            'pass_fee' => 0.00,
            'status' => 'published',
        ], $attributes));
    }

    public function test_paid_event_pass_booking_rejected_without_payment(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');

        $event = $this->createTestEvent([
            'title' => 'Paid Festival 2026',
            'pass_fee' => 200.00,
        ]);

        $response = $this->actingAs($user)->post(route('events.public_register', $event->id), [
            'person_count' => 2,
            'full_name' => 'Test User',
            'contact_number' => '9876543210',
            // No razorpay_payment_id provided
        ]);

        $response->assertRedirect(route('event.details', $event->id));
        $response->assertSessionHas('error');

        // Ensure no event registration was created
        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_paid_event_pass_booking_accepted_with_payment(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');

        $event = $this->createTestEvent([
            'title' => 'Paid Festival 2026',
            'pass_fee' => 200.00,
        ]);

        $response = $this->actingAs($user)->post(route('events.public_register', $event->id), [
            'person_count' => 2,
            'full_name' => 'Test User',
            'contact_number' => '9876543210',
            'razorpay_payment_id' => 'pay_test_pass_123',
        ]);

        $response->assertRedirect(route('event.details', $event->id));
        $response->assertSessionHas('success');

        $registration = EventRegistration::where('event_id', $event->id)->where('user_id', $user->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('paid', $registration->payment_status);
        $this->assertEquals('pay_test_pass_123', $registration->payment_id);
        $this->assertEquals(400.00, (float) $registration->payment_amount);
    }

    public function test_unpaid_event_pass_download_is_forbidden(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');

        $event = $this->createTestEvent([
            'title' => 'Paid Concert 2026',
            'pass_fee' => 150.00,
        ]);

        $unpaidRegistration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_type' => 'pass',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_amount' => 150.00,
            'form_data' => ['person_count' => 1, 'full_name' => 'Test User'],
        ]);

        Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $user->assignRole('Administrator');

        $response = $this->actingAs($user)->get(route('admin.receipts.event_pass', $unpaidRegistration->id));
        $response->assertStatus(403);
    }

    public function test_free_event_pass_download_is_allowed(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');

        $event = $this->createTestEvent([
            'title' => 'Free Community Event 2026',
            'pass_fee' => 0.00,
        ]);

        $freeRegistration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'pass_number' => 1,
            'registration_type' => 'pass',
            'status' => 'approved',
            'payment_status' => 'paid',
            'payment_amount' => 0.00,
            'form_data' => ['person_count' => 1, 'full_name' => 'Test User'],
        ]);

        Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $user->assignRole('Administrator');

        $response = $this->actingAs($user)->get(route('admin.receipts.event_pass', $freeRegistration->id));
        $response->assertOk();
    }

    public function test_inam_vitaran_student_submission_does_not_count_as_paid_pass(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');

        $event = $this->createTestEvent([
            'title' => 'Inam Vitaran 2026',
            'event_type' => 'inam_vitaran',
            'pass_fee' => 200.00,
        ]);

        // Student marksheet submission
        $inamRegistration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'inam_number' => 1,
            'registration_type' => 'inam_vitran',
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'payment_amount' => 0.00,
            'form_data' => [
                'student_name' => 'Rahul Sonagra',
                'standard' => '10th',
                'person_count' => 1,
            ],
        ]);

        // Access dashboard
        $response = $this->actingAs($user)->get(route('member.dashboard'));
        $response->assertOk();

        // Should NOT show "View Pass" for this student form submission
        // Since pass_fee is 200 and no pass was purchased, it should show "Book Pass"
        $response->assertDontSee('View Pass (1)');
        $response->assertDontSee('પાસ જુઓ (1)');
    }

    public function test_pass_purchase_generates_receipt_modal_payload_with_download_and_print_options(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('Member');
        $event = $this->createTestEvent([
            'title' => 'Garba Mahotsav 2026',
            'pass_fee' => 150.00,
        ]);

        $postData = [
            'full_name' => 'Ketan Sathwara',
            'contact_number' => '9898000000',
            'email' => 'ketan@test.com',
            'person_count' => 2,
            'razorpay_payment_id' => 'pay_test_receipt_popup_123',
            'redirect_to' => 'dashboard',
        ];

        $response = $this->actingAs($user)->post(route('events.public_register', $event->id), $postData);
        $response->assertRedirect(route('member.dashboard'));
        $response->assertSessionHas('purchase_receipt');

        $receipt = session('purchase_receipt');
        $this->assertNotNull($receipt);
        $this->assertEquals('event_pass', $receipt['type']);
        $this->assertEquals('Garba Mahotsav 2026', $receipt['event_title']);
        $this->assertEquals(2, $receipt['person_count']);
        $this->assertEquals(300.00, (float) $receipt['amount_paid']);
        $this->assertEquals('pay_test_receipt_popup_123', $receipt['payment_id']);
        $this->assertStringContainsString('/receipts/event-pass/', $receipt['download_url']);
        $this->assertNotEmpty($receipt['receipt_no']);
    }
}

