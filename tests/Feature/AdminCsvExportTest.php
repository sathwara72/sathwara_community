<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCsvExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['status' => 'approved']);
        $this->admin->assignRole('Administrator');
    }

    /** @return array<int, array<int, string>> */
    private function csv(string $url): array
    {
        $response = $this->actingAs($this->admin)->get($url);
        $response->assertOk();

        $body = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());
        $rows = array_map('str_getcsv', array_filter(explode("\n", trim($body))));

        return $rows;
    }

    private function event(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'નવરાત્રી Garba 2026',
            'description' => 'desc',
            'banner_path' => 'events/banner.jpg',
            'venue' => 'Hall',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '19:00:00',
            'event_type' => 'normal',
            'pass_fee' => 100,
            'status' => 'published',
        ], $attributes));
    }

    public function test_business_export_has_area_and_membership_dates_and_serial_numbers(): void
    {
        $area = Area::create(['name' => 'Naroda']);
        foreach (['Alpha', 'Beta'] as $name) {
            Business::create([
                'business_name' => $name, 'owner_name' => 'Owner', 'phone' => '9898989898',
                'address' => 'Road', 'logo_path' => 'x.jpg', 'area_id' => $area->id,
                'status' => 'approved', 'approved_at' => '2026-01-15 10:00:00',
            ]);
        }

        $rows = $this->csv(route('admin.businesses.export'));

        $expected = ['sr_no', 'business_name', 'owner_name', 'category', 'phone', 'email', 'area', 'city', 'state', 'status', 'membership_started', 'membership_expires'];
        $this->assertSame(array_map(fn ($k) => __('messages.csv_' . $k), $expected), $rows[0]);
        $this->assertSame(['1', '2'], [$rows[1][0], $rows[2][0]]);
        $this->assertSame('Naroda', $rows[1][6]);
        $this->assertSame('15-Jan-2026', $rows[1][10]);
        $this->assertSame('15-Jan-2027', $rows[1][11]);
    }

    public function test_pass_export_is_numbered_from_one_and_named_after_the_event(): void
    {
        $event = $this->event(['event_type' => 'yuva_melo']);
        // A candidate submission sits between the passes and must not break the numbering
        foreach ([['full_name' => 'A'], ['first_name' => 'Cand', 'surname' => 'X'], ['full_name' => 'B']] as $i => $fd) {
            EventRegistration::create(['event_id' => $event->id, 'registration_type' => 'pass', 'status' => 'approved', 'form_data' => $fd, 'pass_number' => $i + 1]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.events.registrations.export', $event->id));
        $this->assertStringContainsString("filename*=utf-8''" . $event->id . '_' . rawurlencode('નવરાત્રી_Garba_2026') . '_passes_', $response->headers->get('Content-Disposition'));

        $rows = $this->csv(route('admin.events.registrations.export', $event->id));
        $this->assertSame(['Sr. No.', 'Pass No.', 'Member Code'], array_slice($rows[0], 0, 3));
        $this->assertNotContains('Member ID', $rows[0]);
        $this->assertSame(['1', '2'], [$rows[1][0], $rows[2][0]]);

        $yuva = $this->csv(route('admin.events.yuva_submissions.export', $event->id));
        $this->assertSame(['Sr. No.', 'Yuva Melo No.', 'Status', 'Member Code'], array_slice($yuva[0], 0, 4));
        $this->assertSame('Submission Date', end($yuva[0]));
        $this->assertSame('1', $yuva[1][0]);
        foreach (['Event ID', 'Event Name', 'Member ID'] as $removed) {
            $this->assertNotContains($removed, $yuva[0]);
        }
    }

    public function test_every_list_export_starts_with_serial_number(): void
    {
        $event = $this->event();

        foreach ([
            'admin.areas.export', 'admin.events.export', 'admin.awards.export',
            'admin.gallery.export', 'admin.content.sliders.export', 'admin.content.agendas.export',
            'admin.content.desk.export', 'admin.content.committee.export', 'admin.content.timelines.export',
            'admin.content.updates.export',
        ] as $route) {
            $header = $this->csv(route($route))[0];
            $this->assertSame(__('messages.csv_sr_no'), $header[0], $route);
        }

        $inam = $this->csv(route('admin.events.inam_submissions.export', $event->id));
        $this->assertSame(['Sr. No.', 'Member Code', 'Student Name'], array_slice($inam[0], 0, 3));
        $this->assertSame('Sr. No.', $this->csv(route('admin.events.sponsors.export', $event->id))[0][0]);
    }
}
