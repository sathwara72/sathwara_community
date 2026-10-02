<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberEventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_event_registration_form_shows_newly_added_family_members(): void
    {
        Role::create(['name' => 'Member', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'name' => 'Member One',
            'email' => 'member@example.com',
            'status' => 'approved',
        ]);
        $user->assignRole('Member');

        $event = Event::create([
            'title' => 'Inam Vitaran Event',
            'event_type' => 'inam_vitaran',
            'description' => 'Test event',
            'venue' => 'Test Venue',
            'date' => now()->addDay(),
            'time' => '10:00:00',
            'banner_path' => 'test-banner.jpg',
            'registration_option' => true,
            'has_registration_form' => true,
            'status' => 'published',
        ]);

        $user->familyMembers;

        $user->familyMembers()->create([
            'name' => 'John Doe',
            'relationship' => 'Son',
            'gender' => 'Male',
            'dob' => '2010-01-01',
        ]);

        $response = $this->actingAs($user)->get(route('member.events.register_form', $event->id));

        $response->assertOk();
        $response->assertSee('John Doe');
    }

    public function test_member_event_registration_form_shows_all_family_member_options(): void
    {
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'name' => 'Member Two',
            'email' => 'member2@example.com',
            'status' => 'approved',
        ]);
        $user->assignRole('Member');

        $event = Event::create([
            'title' => 'Inam Vitaran Event',
            'event_type' => 'inam_vitaran',
            'description' => 'Test event',
            'venue' => 'Test Venue',
            'date' => now()->addDay(),
            'time' => '10:00:00',
            'banner_path' => 'test-banner.jpg',
            'registration_option' => true,
            'has_registration_form' => true,
            'status' => 'published',
        ]);

        foreach (range(1, 6) as $index) {
            $user->familyMembers()->create([
                'name' => "Family Member {$index}",
                'relationship' => 'Child',
                'gender' => 'Male',
                'dob' => '2010-01-0' . $index,
            ]);
        }

        $response = $this->actingAs($user)->get(route('member.events.register_form', $event->id));
        $response->assertOk();

        $html = $response->getContent();
        preg_match('/<select name="student_name"[^>]*>(.*?)<\/select>/s', $html, $selectMatch);
        preg_match_all('/<option\b[^>]*>/', $selectMatch[1] ?? '', $matches);

        // first option is the placeholder, so expect 7 options total for 6 family members
        $this->assertCount(7, $matches[0]);
    }

    public function test_normal_event_direct_registration_with_person_count(): void
    {
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'name' => 'Normal Event Member',
            'email' => 'normalmember@example.com',
            'status' => 'approved',
        ]);
        $user->assignRole('Member');

        $event = Event::create([
            'title' => 'Normal Community Event',
            'event_type' => 'normal',
            'description' => 'A normal community gathering',
            'venue' => 'Community Hall',
            'date' => now()->addDays(5),
            'time' => '17:00:00',
            'banner_path' => 'normal-banner.jpg',
            'registration_option' => true,
            'has_registration_form' => true,
            'status' => 'published',
        ]);

        // Direct registration with 3 persons
        $response = $this->actingAs($user)->post(route('events.public_register', $event->id), [
            'person_count' => 3,
            'full_name' => 'Normal Event Member',
            'contact_number' => '9876543210',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $reg = \App\Models\EventRegistration::where('event_id', $event->id)->where('user_id', $user->id)->first();
        $this->assertEquals(3, $reg->form_data['person_count']);

        // Test updating person count to 5
        $updateResponse = $this->actingAs($user)->post(route('events.public_register', $event->id), [
            'person_count' => 5,
            'full_name' => 'Normal Event Member',
            'contact_number' => '9876543210',
        ]);

        $updateResponse->assertRedirect();

        $reg->refresh();
        $this->assertEquals(8, $reg->form_data['person_count']);
    }

    public function test_event_registration_stays_on_same_page(): void
    {
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'status' => 'approved',
        ]);
        $user->assignRole('Member');

        $event = Event::create([
            'title' => 'Community Gathering',
            'description' => 'Community Gathering Description',
            'date' => now()->addDays(5)->toDateString(),
            'time' => '10:00:00',
            'venue' => 'Community Hall',
            'banner_path' => 'test-banner.jpg',
            'registration_option' => true,
            'status' => 'published',
            'pass_fee' => 0,
        ]);

        $registerPageUrl = route('events.public_register_form', $event->id);

        // 1. When submitting with redirect_to, it should redirect to that same registration page
        $response = $this->actingAs($user)->post(route('events.public_register', $event->id), [
            'full_name' => 'Registered Member',
            'contact_number' => '9876543210',
            'person_count' => 1,
            'redirect_to' => $registerPageUrl,
        ]);

        $response->assertRedirect($registerPageUrl);
        $response->assertSessionHas('success');

        // 2. When submitting from the registration page (Referer header), it redirects back to the registration page
        $responseFromPage = $this->actingAs($user)
            ->from($registerPageUrl)
            ->post(route('events.public_register', $event->id), [
                'full_name' => 'Registered Member',
                'contact_number' => '9876543210',
                'person_count' => 1,
            ]);

        $responseFromPage->assertRedirect($registerPageUrl);

        // 3. When registering from member registration form, stay on member registration form
        $memberRegisterUrl = route('member.events.register_form', $event->id);
        $responseFromMemberPage = $this->actingAs($user)
            ->from($memberRegisterUrl)
            ->post(route('events.public_register', $event->id), [
                'full_name' => 'Registered Member',
                'contact_number' => '9876543210',
                'person_count' => 1,
                'redirect_to' => $memberRegisterUrl,
            ]);

        $responseFromMemberPage->assertRedirect($memberRegisterUrl);
    }
}

