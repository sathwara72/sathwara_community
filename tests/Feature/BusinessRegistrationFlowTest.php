<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Business;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Mail::fake();
        Setting::set('business_registration_fee', '500');
        $this->area = Area::create(['name' => 'Naroda']);
    }

    private function form(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Sathwara Traders',
            'owner_name' => 'Suresh',
            'email' => 'Shop@Example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => 'Station Road',
            'area_id' => $this->area->id,
            'phone' => '9876543210',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ], $overrides);
    }

    private function existingBusiness(array $attributes = []): Business
    {
        return Business::create(array_merge([
            'business_name' => 'Old Shop', 'owner_name' => 'Owner', 'phone' => '9898989898',
            'address' => 'Road', 'logo_path' => 'x.jpg', 'area_id' => $this->area->id, 'status' => 'approved',
        ], $attributes));
    }

    public function test_registration_page_renders(): void
    {
        $this->get(route('register.business'))
            ->assertOk()
            ->assertSee(route('register.business.pre_validate'), false);
    }

    public function test_pre_validation_passes_for_a_valid_form_without_any_email_otp(): void
    {
        $this->postJson(route('register.business.pre_validate'), $this->form())
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_pre_validation_rejects_a_taken_email_before_payment(): void
    {
        $this->existingBusiness(['email' => 'shop@example.com']);

        $this->postJson(route('register.business.pre_validate'), $this->form())
            ->assertStatus(422)
            ->assertJsonFragment(['errors' => ['This email address is already registered with another business account.']]);
    }

    public function test_pre_validation_rejects_unknown_member_and_second_business_for_a_member(): void
    {
        $this->postJson(route('register.business.pre_validate'), $this->form(['member_id' => 'NOPE999999']))
            ->assertStatus(422);

        $member = User::factory()->create(['member_code' => 'SSAM0777']);
        $this->existingBusiness(['user_id' => $member->id, 'email' => 'old@example.com']);

        $this->postJson(route('register.business.pre_validate'), $this->form(['member_id' => 'SSAM0777']))
            ->assertStatus(422);
    }

    public function test_member_code_links_the_business_to_that_member_not_to_the_user_with_the_same_number(): void
    {
        // User #777 exists, but the member who owns code SSAM0777 is someone else
        $decoy = User::factory()->create();
        $decoy->forceFill(['id' => 777])->save();
        $member = User::factory()->create(['member_code' => 'SSAM0777']);

        $this->post(route('register.business.submit'), $this->form(['member_id' => 'SSAM0777', 'razorpay_payment_id' => 'pay_flow_1']))
            ->assertSessionHasNoErrors();

        $business = Business::firstWhere('business_name', 'Sathwara Traders');
        $this->assertSame($member->id, $business->user_id);
        $this->assertSame('shop@example.com', $business->email);
        $this->assertSame('pending', $business->status);
        $this->assertSame('paid', $business->payment_status);
    }

    public function test_redirect_to_another_site_is_ignored(): void
    {
        $this->post(route('register.business.submit'), $this->form([
            'razorpay_payment_id' => 'pay_flow_2',
            'redirect_to' => 'https://evil.example.net/phish',
        ]))->assertRedirect(route('register.business'));
    }
}
