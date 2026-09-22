<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_web_response()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_business_login_rate_limiting_locks_out_after_repeated_failures()
    {
        $area = Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);
        $category = BusinessCategory::create(['name' => 'Retail', 'slug' => 'retail']);

        $business = Business::create([
            'business_name' => 'Security Shop',
            'owner_name' => 'Owner',
            'email' => 'secshop@test.com',
            'phone' => '9898989898',
            'password' => bcrypt('correct-password'),
            'address' => 'Station Road',
            'logo_path' => 'businesses/logos/test.jpg',
            'area_id' => $area->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ]);

        // Attempt 5 incorrect passwords
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('business.login.submit'), [
                'login' => 'secshop@test.com',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors('login');
        }

        // 6th attempt should be blocked with throttle error
        $blockedResponse = $this->post(route('business.login.submit'), [
            'login' => 'secshop@test.com',
            'password' => 'wrong-password',
        ]);

        $blockedResponse->assertSessionHasErrors('login');
        $error = session('errors')->first('login');
        $this->assertTrue(str_contains($error, 'ઘણા બધા પ્રયાસો') || str_contains($error, 'Too many login attempts'));
    }

    public function test_business_otp_verification_locks_out_after_too_many_attempts()
    {
        // Set up OTP session
        $this->withSession([
            'biz_reg_otp_email' => 'otpsec@test.com',
            'biz_reg_otp_code' => '123456',
            'biz_reg_otp_expires' => now()->addMinutes(10),
            'biz_reg_otp_attempts' => 0,
        ]);

        // Fail 5 times
        for ($i = 0; $i < 5; $i++) {
            $res = $this->postJson(route('register.business.verify_otp'), [
                'email' => 'otpsec@test.com',
                'otp' => '000000',
            ]);
            $res->assertStatus(400);
        }

        // 6th attempt should fail with 429
        $lockoutRes = $this->postJson(route('register.business.verify_otp'), [
            'email' => 'otpsec@test.com',
            'otp' => '000000',
        ]);

        $lockoutRes->assertStatus(429);
        $this->assertFalse(session()->has('biz_reg_otp_code'));
    }

    public function test_replayed_payment_id_is_rejected_on_member_registration()
    {
        $area = Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);

        // Create an existing user with a used payment ID
        User::create([
            'name' => 'Existing Paid User',
            'email' => 'existingpaid@test.com',
            'password' => bcrypt('password123'),
            'status' => 'approved',
            'payment_id' => 'pay_already_used_999',
            'payment_status' => 'paid',
        ]);

        $this->withSession([
            'reg_email_verified' => 'newuser@test.com',
        ]);

        $response = $this->post(route('register.member.submit'), [
            'first_name' => 'New',
            'middle_name' => 'User',
            'last_name' => 'Test',
            'phone' => '9998887776',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => 'Main Road',
            'area_id' => $area->id,
            'razorpay_payment_id' => 'pay_already_used_999', // Replaying existing payment ID
        ]);

        $response->assertSessionHasErrors('payment');
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@test.com',
        ]);
    }

    public function test_business_login_page_renders_successfully_with_proper_layout()
    {
        $response = $this->get(route('business.login'));

        $response->assertStatus(200);
        $response->assertSee(route('business.login.submit'));
        $response->assertSee(route('business.password.request'));
        $response->assertSee('name="login"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_business_forgot_password_flow_works_properly()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $area = Area::create(['name' => 'Naroda', 'city' => 'Ahmedabad', 'state' => 'Gujarat']);
        $category = BusinessCategory::create(['name' => 'Retail', 'slug' => 'retail']);

        $business = Business::create([
            'business_name' => 'Password Reset Shop',
            'owner_name' => 'Shop Owner',
            'email' => 'resetbiz@test.com',
            'phone' => '9898980000',
            'password' => bcrypt('old-password-123'),
            'address' => 'Shop 1, Main Road',
            'logo_path' => 'businesses/logos/test.jpg',
            'area_id' => $area->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ]);

        // 1. Visit forgot password page
        $this->get(route('business.password.request'))->assertStatus(200);

        // 2. Request OTP
        $response = $this->post(route('business.password.email'), [
            'email' => 'resetbiz@test.com',
        ]);
        $response->assertRedirect(route('business.password.otp.verify.form'));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'resetbiz@test.com']);

        // 3. Verify OTP
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'resetbiz@test.com'],
            ['token' => bcrypt('123456'), 'created_at' => now()]
        );

        $verifyResponse = $this->withSession(['business_reset_email' => 'resetbiz@test.com'])
            ->post(route('business.password.otp.verify.submit'), ['otp' => '123456']);
        $verifyResponse->assertRedirect(route('business.password.reset'));

        // 4. Reset password
        $resetResponse = $this->withSession(['business_otp_verified_email' => 'resetbiz@test.com'])
            ->post(route('business.password.store'), [
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ]);
        $resetResponse->assertRedirect(route('business.login'));

        // Verify updated password works
        $business->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecurePassword123!', $business->password));
    }
}
