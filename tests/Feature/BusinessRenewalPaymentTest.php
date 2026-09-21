<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessPaymentLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessRenewalPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
    }

    public function test_business_not_due_for_renewal_cannot_generate_link()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $area = Area::first() ?? Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);
        $category = BusinessCategory::first() ?? BusinessCategory::create(['name' => 'Retail', 'slug' => 'retail']);

        $business = Business::create([
            'business_name' => 'Active Shop',
            'owner_name' => 'Owner',
            'address' => 'Market',
            'phone' => '9898989898',
            'area_id' => $area->id,
            'category_id' => $category->id,
            'logo_path' => 'businesses/logos/test.jpg',
            'status' => 'approved',
            'approved_at' => now(), // Approved today - 1 year has not completed
        ]);

        $this->assertFalse($business->isRenewalDue());

        $response = $this->actingAs($admin)->post(route('admin.businesses.paymentLinks.generate', $business->id), [
            'amount' => 500,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_business_due_for_renewal_can_be_marked_paid_and_renewed()
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $area = Area::first() ?? Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);
        $category = BusinessCategory::first() ?? BusinessCategory::create(['name' => 'Retail', 'slug' => 'retail']);

        // Approved 2 years ago - renewal is due!
        $business = Business::create([
            'business_name' => 'Old Enterprise',
            'owner_name' => 'Old Owner',
            'address' => 'Old Road',
            'phone' => '9898989898',
            'email' => 'oldowner@test.com',
            'area_id' => $area->id,
            'category_id' => $category->id,
            'logo_path' => 'businesses/logos/test.jpg',
            'status' => 'approved',
            'approved_at' => now()->subYears(2),
        ]);

        $this->assertTrue($business->isRenewalDue());

        // Create a payment link manually
        $link = BusinessPaymentLink::create([
            'business_id' => $business->id,
            'amount' => 500,
            'razorpay_link_id' => 'plink_test_renewal_123',
            'razorpay_link_url' => 'https://rzp.io/i/test123',
            'status' => 'created',
            'created_by' => $admin->id,
            'expires_at' => now()->addDay(),
        ]);

        // Admin marks payment link as paid
        $response = $this->actingAs($admin)->post(route('admin.businesses.paymentLinks.markPaid', [$business->id, $link->id]), [
            'razorpay_payment_id' => 'pay_renewal_confirmed_777',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $link->refresh();
        $this->assertEquals('paid', $link->status);
        $this->assertEquals('pay_renewal_confirmed_777', $link->razorpay_payment_id);

        $business->refresh();
        $this->assertEquals('approved', $business->status);
        $this->assertEquals('paid', $business->payment_status);
        $this->assertEquals('pay_renewal_confirmed_777', $business->payment_id);
        // approved_at is now reset to today, so it is no longer due for renewal
        $this->assertFalse($business->isRenewalDue());
    }

    public function test_razorpay_webhook_marks_payment_link_paid_and_renews_business()
    {
        Mail::fake();

        $secret = 'test_webhook_secret_123';
        \App\Models\Setting::set('razorpay_webhook_secret', $secret);

        $admin = User::factory()->create();
        $area = Area::first() ?? Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);
        $category = BusinessCategory::first() ?? BusinessCategory::create(['name' => 'Retail', 'slug' => 'retail']);

        $business = Business::create([
            'business_name' => 'Webhook Shop',
            'owner_name' => 'Shop Owner',
            'address' => 'Station Road',
            'phone' => '9898989898',
            'email' => 'webhookowner@test.com',
            'area_id' => $area->id,
            'category_id' => $category->id,
            'logo_path' => 'businesses/logos/test.jpg',
            'status' => 'approved',
            'approved_at' => now()->subYears(2),
        ]);

        $link = BusinessPaymentLink::create([
            'business_id' => $business->id,
            'amount' => 500,
            'razorpay_link_id' => 'plink_webhook_999',
            'razorpay_link_url' => 'https://rzp.io/i/webhook999',
            'status' => 'created',
            'created_by' => $admin->id,
            'expires_at' => now()->addDay(),
        ]);

        $payload = [
            'event' => 'payment_link.paid',
            'payload' => [
                'payment_link' => [
                    'entity' => [
                        'id' => 'plink_webhook_999',
                        'amount' => 50000,
                        'status' => 'paid',
                    ],
                ],
                'payment' => [
                    'entity' => [
                        'id' => 'pay_webhook_confirmed_888',
                        'status' => 'captured',
                    ],
                ],
            ],
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $secret);

        $response = $this->call(
            'POST',
            route('api.webhooks.razorpay'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            ],
            $payloadJson
        );

        $response->assertStatus(200);

        $link->refresh();
        $this->assertEquals('paid', $link->status);
        $this->assertEquals('pay_webhook_confirmed_888', $link->razorpay_payment_id);

        $business->refresh();
        $this->assertEquals('approved', $business->status);
        $this->assertEquals('paid', $business->payment_status);
        $this->assertEquals('pay_webhook_confirmed_888', $business->payment_id);
        $this->assertFalse($business->isRenewalDue());
    }
}
