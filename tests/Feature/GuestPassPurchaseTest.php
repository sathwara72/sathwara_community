<?php

namespace Tests\Feature;

use App\Mail\EventPassPurchasedMail;
use App\Models\Area;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestPassPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['guest_pass.check_dns' => false]);
        Setting::set('razorpay_key_id', 'rzp_test_key');
        Setting::set('razorpay_key_secret', self::SECRET);
        Setting::set('razorpay_webhook_secret', 'whsec');
        Mail::fake();
    }

    private function event(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Open Garba Night',
            'description' => 'desc',
            'banner_path' => 'events/banner.jpg',
            'venue' => 'Grand Hall',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '19:00:00',
            'registration_end_date' => now()->addDays(5)->toDateString(),
            'event_type' => 'normal',
            'pass_fee' => 200,
            'pass_purchase_access' => 'anyone',
            'status' => 'published',
        ], $attributes));
    }

    private function enterDetails(Event $event, string $email = 'guest@gmail.com', string $mobile = '98765 43210')
    {
        $areaId = Area::firstOrCreate(['name' => 'Naroda'])->id;

        return $this->postJson(route('events.guest_pass.details', $event->id), ['email' => $email, 'mobile' => $mobile, 'area_id' => $areaId]);
    }

    private function fakeOrder(Event $event, array $overrides = [], string $orderId = 'order_ABC123'): array
    {
        $order = array_replace_recursive([
            'id' => $orderId,
            'status' => 'paid',
            'amount' => 40000,
            'amount_paid' => 40000,
            'currency' => 'INR',
            'notes' => [
                'purpose' => 'guest_event_pass',
                'event_id' => (string) $event->id,
                'email' => 'guest@gmail.com',
                'mobile' => '9876543210',
                'name' => 'Ravi Patel',
                'person_count' => '2',
            ],
        ], $overrides);

        // Http::fake() stubs accumulate and the first match wins, so start each fake from a clean factory
        Http::swap(new HttpFactory());
        Http::fake([
            'api.razorpay.com/v1/orders/*' => Http::response($order),
            'api.razorpay.com/v1/orders' => Http::response($order),
        ]);

        return $order;
    }

    private function completePayload(string $orderId = 'order_ABC123', string $paymentId = 'pay_XYZ789', ?string $signature = null): array
    {
        return [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature ?? hash_hmac('sha256', $orderId . '|' . $paymentId, self::SECRET),
        ];
    }

    public function test_members_only_event_rejects_guest_purchase(): void
    {
        $event = $this->event(['pass_purchase_access' => 'members_only']);

        $this->enterDetails($event)->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_dummy_and_disposable_emails_are_rejected(): void
    {
        $event = $this->event();

        foreach (['someone@mailinator.com', 'x@example.com', 'dummy123@gmail.com', 'not-an-email'] as $email) {
            $this->enterDetails($event, $email)->assertStatus(422);
        }

        $this->enterDetails($event, 'guest@gmail.com', '12345')->assertStatus(422);
        Mail::assertNothingSent();
    }

    public function test_details_step_sends_no_otp_email(): void
    {
        $event = $this->event();

        $this->enterDetails($event)->assertOk()->assertJson(['email' => 'guest@gmail.com']);
        Mail::assertNothingSent();
    }

    public function test_area_is_required_and_saved_on_the_pass(): void
    {
        $event = $this->event(['pass_fee' => 0]);

        $this->postJson(route('events.guest_pass.details', $event->id), ['email' => 'guest@gmail.com', 'mobile' => '9876543210'])
            ->assertStatus(422);
        $this->postJson(route('events.guest_pass.details', $event->id), ['email' => 'guest@gmail.com', 'mobile' => '9876543210', 'area_id' => 99999])
            ->assertStatus(422);

        $this->enterDetails($event)->assertOk();
        $this->postJson(route('events.guest_pass.complete', $event->id), ['person_count' => 1])->assertOk();

        $registration = EventRegistration::firstOrFail();
        $this->assertSame('Naroda', $registration->form_data['area']);
        $this->assertSame(Area::where('name', 'Naroda')->value('id'), $registration->form_data['area_id']);
    }

    public function test_member_can_buy_without_login_and_pass_is_linked_to_their_account(): void
    {
        $member = User::factory()->create(['name' => 'Karan Sathwara', 'email' => 'Guest@Gmail.com', 'status' => 'approved']);
        $event = $this->event();
        $this->enterDetails($event)->assertOk();
        $this->fakeOrder($event, ['notes' => ['name' => '']]);

        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertOk();

        $registration = EventRegistration::firstOrFail();
        $this->assertSame($member->id, $registration->user_id);
        $this->assertFalse($registration->form_data['is_guest']);
        $this->assertSame('Karan Sathwara', $registration->form_data['full_name']);
        $this->assertTrue($member->eventRegistrations()->where('event_id', $event->id)->exists());
        Mail::assertSent(EventPassPurchasedMail::class, fn ($m) => $m->hasTo('guest@gmail.com'));
    }

    public function test_free_pass_for_member_email_is_linked_too(): void
    {
        $member = User::factory()->create(['email' => 'guest@gmail.com', 'status' => 'approved']);
        $event = $this->event(['pass_fee' => 0]);
        $this->enterDetails($event)->assertOk();

        $this->postJson(route('events.guest_pass.complete', $event->id), ['person_count' => 2])->assertOk();

        $this->assertSame($member->id, EventRegistration::firstOrFail()->user_id);
    }

    public function test_cannot_create_order_without_entering_details(): void
    {
        $event = $this->event();
        $this->fakeOrder($event);

        $this->postJson(route('events.guest_pass.order', $event->id), ['person_count' => 2])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_paid_purchase_creates_guest_registration_and_emails_pass(): void
    {
        $event = $this->event();
        $this->enterDetails($event);
        $this->fakeOrder($event);

        $this->postJson(route('events.guest_pass.order', $event->id), ['person_count' => 2, 'name' => 'Ravi Patel'])
            ->assertOk()
            ->assertJson(['free' => false, 'order_id' => 'order_ABC123']);

        // The amount and notes sent to Razorpay come from the server, not the browser
        Http::assertSent(fn ($r) => $r->url() === 'https://api.razorpay.com/v1/orders'
            && $r['amount'] === 40000
            && $r['notes']['email'] === 'guest@gmail.com'
            && $r['notes']['mobile'] === '9876543210');

        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())
            ->assertOk()
            ->assertJsonStructure(['redirect']);

        $registration = EventRegistration::firstOrFail();
        $this->assertNull($registration->user_id);
        $this->assertSame('pass', $registration->registration_type);
        $this->assertSame('paid', $registration->payment_status);
        $this->assertSame('pay_XYZ789', $registration->payment_id);
        $this->assertSame('order_ABC123', $registration->razorpay_order_id);
        $this->assertEquals(400, $registration->payment_amount);
        $this->assertSame(2, $registration->form_data['person_count']);
        $this->assertSame('guest@gmail.com', $registration->form_data['email']);
        $this->assertSame('9876543210', $registration->form_data['contact_number']);
        $this->assertCount(2, $registration->passTokens);

        Mail::assertSent(EventPassPurchasedMail::class, fn ($m) => $m->hasTo('guest@gmail.com') && $m->personCount === 2);
    }

    public function test_one_details_entry_can_buy_passes_multiple_times(): void
    {
        $event = $this->event();
        $this->enterDetails($event);

        foreach (['order_ONE' => 'pay_1', 'order_TWO' => 'pay_2'] as $orderId => $paymentId) {
            $this->fakeOrder($event, [], $orderId);
            $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload($orderId, $paymentId))->assertOk();
        }

        $this->assertSame(2, EventRegistration::where('event_id', $event->id)->whereNull('user_id')->count());
        Mail::assertSent(EventPassPurchasedMail::class, 2);
    }

    public function test_replaying_a_paid_order_does_not_create_a_second_registration(): void
    {
        $event = $this->event();
        $this->enterDetails($event);
        $this->fakeOrder($event);

        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertOk();
        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertOk();

        $this->assertSame(1, EventRegistration::count());
        Mail::assertSent(EventPassPurchasedMail::class, 1);
    }

    public function test_forged_payment_signature_is_rejected(): void
    {
        $event = $this->event();
        $this->enterDetails($event);
        $this->fakeOrder($event);

        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload(signature: 'forged'))
            ->assertStatus(422);

        $this->assertSame(0, EventRegistration::count());
        Mail::assertNotSent(EventPassPurchasedMail::class);
    }

    public function test_order_belonging_to_another_email_or_event_is_rejected(): void
    {
        $event = $this->event();
        $other = $this->event(['title' => 'Other']);
        $this->enterDetails($event);

        $this->fakeOrder($event, ['notes' => ['email' => 'someone.else@gmail.com']]);
        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertStatus(422);

        $this->fakeOrder($event, ['notes' => ['event_id' => (string) $other->id]]);
        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertStatus(422);

        $this->assertSame(0, EventRegistration::count());
    }

    public function test_unsettled_payment_defers_to_webhook_and_webhook_issues_the_pass_once(): void
    {
        $event = $this->event();
        $this->enterDetails($event);
        $this->fakeOrder($event, ['status' => 'attempted', 'amount_paid' => 0]);

        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())
            ->assertOk()->assertJson(['pending' => true]);
        $this->assertSame(0, EventRegistration::count());

        $paidOrder = $this->fakeOrder($event);
        $body = json_encode([
            'event' => 'order.paid',
            'payload' => [
                'order' => ['entity' => $paidOrder],
                'payment' => ['entity' => ['id' => 'pay_XYZ789']],
            ],
        ]);
        $signature = hash_hmac('sha256', $body, 'whsec');

        foreach ([1, 2] as $_) {
            $this->call('POST', route('api.webhooks.razorpay'), [], [], [], [
                'HTTP_X-Razorpay-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ], $body)->assertOk();
        }

        $registration = EventRegistration::firstOrFail();
        $this->assertSame('pay_XYZ789', $registration->payment_id);
        $this->assertSame(1, EventRegistration::count());
        Mail::assertSent(EventPassPurchasedMail::class, 1);
    }

    public function test_free_event_needs_no_payment(): void
    {
        $event = $this->event(['pass_fee' => 0]);
        $this->enterDetails($event);

        $this->postJson(route('events.guest_pass.order', $event->id), ['person_count' => 3])
            ->assertOk()->assertJson(['free' => true]);
        $this->postJson(route('events.guest_pass.complete', $event->id), ['person_count' => 3, 'name' => 'Sita'])
            ->assertOk();

        $registration = EventRegistration::firstOrFail();
        $this->assertNull($registration->user_id);
        $this->assertSame(3, $registration->form_data['person_count']);
        $this->assertEquals(0, $registration->payment_amount);
        Mail::assertSent(EventPassPurchasedMail::class, fn ($m) => $m->hasTo('guest@gmail.com') && $m->personCount === 3);
    }

    public function test_pass_limit_and_deadline_are_enforced(): void
    {
        $event = $this->event(['total_pass_limit' => 2]);
        $this->enterDetails($event);

        $this->postJson(route('events.guest_pass.order', $event->id), ['person_count' => 3])->assertStatus(422);

        $event->update(['registration_end_date' => now()->subDay()->toDateString()]);
        $this->postJson(route('events.guest_pass.order', $event->id), ['person_count' => 1])->assertStatus(422);
    }

    public function test_pass_email_contains_a_qr_code_per_pass(): void
    {
        $event = $this->event();
        $this->enterDetails($event);
        $this->fakeOrder($event);
        $this->postJson(route('events.guest_pass.complete', $event->id), $this->completePayload())->assertOk();

        $registration = EventRegistration::firstOrFail();
        $mail = new EventPassPurchasedMail($event, $registration, null, ['001', '002'], 2);
        $html = $mail->render();

        $this->assertSame(2, substr_count($html, 'api.qrserver.com'));
    }

    public function test_event_page_shows_guest_purchase_only_when_enabled(): void
    {
        $open = $this->event();
        $closed = $this->event(['title' => 'Members Gala', 'pass_purchase_access' => 'members_only']);

        $this->get(route('event.details', $open->id))->assertOk()->assertSee(__('messages.guest_buy_pass'));
        $this->get(route('event.details', $closed->id))->assertOk()->assertDontSee(__('messages.guest_buy_pass'));
    }

}
