<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use App\Models\Area;
use App\Models\EventSponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Member']);
        Area::create(['name' => 'Satellite', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'pincode' => '380015']);
    }

    public function test_admin_can_update_payment_settings()
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'active_tab' => 'payment',
            'member_signup_fee' => '1200',
            'business_registration_fee' => '600',
            'razorpay_key_id' => 'rzp_test_123456',
            'razorpay_key_secret' => 'secret_123456',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1200', Setting::get('member_signup_fee'));
        $this->assertEquals('600', Setting::get('business_registration_fee'));
        $this->assertEquals('rzp_test_123456', Setting::get('razorpay_key_id'));
    }

    public function test_member_signup_stores_payment_id_and_pending_status()
    {
        Setting::set('member_signup_fee', '1000');
        $area = Area::first();

        $response = $this->withSession(['reg_email_verified' => 'ramesh@test.com'])->post(route('register.member.submit'), [
            'first_name' => 'Ramesh',
            'middle_name' => 'K',
            'last_name' => 'Sathwara',
            'phone' => '9876543210',
            'email' => 'ramesh@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'address' => '123 Main Road',
            'area_id' => $area->id,
            'razorpay_payment_id' => 'pay_member_98765',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('purchase_receipt');
        $receipt = session('purchase_receipt');
        $this->assertEquals('membership', $receipt['type']);
        $this->assertEquals('pay_member_98765', $receipt['payment_id']);
        $this->assertNotEmpty($receipt['download_url']);

        $user = User::where('email', 'ramesh@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);
        $this->assertEquals('pay_member_98765', $user->payment_id);
        $this->assertEquals('paid', $user->payment_status);
        $this->assertEquals(1000.00, (float)$user->payment_amount);
    }

    public function test_business_registration_stores_payment_id_and_pending_status()
    {
        Setting::set('business_registration_fee', '500');
        $area = Area::first();

        $response = $this->post(route('register.business.submit'), [
            'business_name' => 'Sathwara Enterprise',
            'owner_name' => 'Suresh Sathwara',
            'address' => '45 Commercial Complex',
            'area_id' => $area->id,
            'phone' => '9876543211',
            'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.jpg'),
            'razorpay_payment_id' => 'pay_biz_12345',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('purchase_receipt');
        $receipt = session('purchase_receipt');
        $this->assertEquals('business', $receipt['type']);
        $this->assertEquals('pay_biz_12345', $receipt['payment_id']);
        $this->assertNotEmpty($receipt['download_url']);

        $business = Business::where('business_name', 'Sathwara Enterprise')->first();
        $this->assertNotNull($business);
        $this->assertEquals('pending', $business->status);
        $this->assertEquals('pay_biz_12345', $business->payment_id);
        $this->assertEquals('paid', $business->payment_status);
        $this->assertEquals(500.00, (float)$business->payment_amount);
    }

    public function test_normal_event_pass_purchase_stores_payment()
    {
        $member = User::factory()->create(['status' => 'approved']);
        $member->assignRole('Member');

        $event = Event::create([
            'title' => 'Community Garba Night 2026',
            'description' => 'Garba Pass',
            'banner_path' => 'events/banner.jpg',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '19:00:00',
            'venue' => 'Sathwara Hall',
            'event_type' => 'normal',
            'pass_fee' => 150.00,
            'status' => 'published',
        ]);

        $response = $this->actingAs($member)->post(route('events.public_register', $event->id), [
            'person_count' => 3,
            'razorpay_payment_id' => 'pay_event_pass_777',
        ]);

        $response->assertRedirect();

        $registration = EventRegistration::where('event_id', $event->id)->where('user_id', $member->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('pay_event_pass_777', $registration->payment_id);
        $this->assertEquals('paid', $registration->payment_status);
        $this->assertEquals(450.00, (float)$registration->payment_amount);
    }

    public function test_event_sponsor_registration_rejected_if_payment_missing_when_amount_required()
    {
        $event = Event::create([
            'title' => 'Annual Sammelan 2026',
            'description' => 'Sponsorship Event',
            'banner_path' => 'events/banner.jpg',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '10:00:00',
            'venue' => 'Sathwara Hall',
            'event_type' => 'normal',
            'status' => 'published',
        ]);

        $response = $this->from(route('event.details', $event->id))
            ->post(route('events.sponsor.register', $event->id), [
                'name' => 'Patel Enterprise',
                'mobile' => '9876543210',
                'amount' => 25000,
                'razorpay_payment_id' => '',
            ]);

        $response->assertRedirect(route('event.details', $event->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('event_sponsors', [
            'name' => 'Patel Enterprise',
            'event_id' => $event->id,
        ]);
    }

    public function test_event_sponsor_registration_accepted_when_paid()
    {
        $event = Event::create([
            'title' => 'Annual Sammelan 2026',
            'description' => 'Sponsorship Event',
            'banner_path' => 'events/banner.jpg',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '10:00:00',
            'venue' => 'Sathwara Hall',
            'event_type' => 'normal',
            'status' => 'published',
        ]);

        $response = $this->from(route('event.details', $event->id))
            ->post(route('events.sponsor.register', $event->id), [
                'name' => 'Patel Enterprise Paid',
                'mobile' => '9876543210',
                'amount' => 25000,
                'razorpay_payment_id' => 'pay_sponsor_99999',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_sponsors', [
            'name' => 'Patel Enterprise Paid',
            'event_id' => $event->id,
            'payment_id' => 'pay_sponsor_99999',
            'payment_status' => 'received',
        ]);
    }

    public function test_owner_can_view_pending_business_details_without_404()
    {
        $member = User::factory()->create(['status' => 'approved']);
        $member->assignRole('Member');
        $area = Area::first();

        $business = Business::create([
            'user_id' => $member->id,
            'business_name' => 'Pending Cafe',
            'owner_name' => 'Cafe Owner',
            'address' => 'Near Lake',
            'area_id' => $area->id,
            'phone' => '9988776655',
            'logo_path' => 'businesses/logos/default.jpg',
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        // Guest / anonymous should get 404 on pending business
        $guestResponse = $this->get(route('business.details', $business->id));
        $guestResponse->assertStatus(404);

        // Owner should see their business details with 200 OK
        $ownerResponse = $this->actingAs($member)->get(route('business.details', $business->id));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('Pending Cafe');
        $ownerResponse->assertSee('PENDING');
    }
}
