<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSponsor;
use App\Models\User;
use App\Support\NumberToWords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_number_to_words_converter()
    {
        $this->assertEquals('Twenty-Five Thousand', NumberToWords::convert(25000));
        $this->assertEquals('One Thousand Two Hundred', NumberToWords::convert(1200));
        $this->assertEquals('Five Hundred', NumberToWords::convert(500));
        $this->assertEquals('One Lakh Twenty-Five Thousand', NumberToWords::convert(125000));
    }

    public function test_sponsorship_receipt_view_renders_centered_receipt_and_acknowledgement_box()
    {
        $event = Event::create([
            'title' => 'Shikshan Sanman & Inam Vitaran 2026',
            'description' => 'Annual Function',
            'banner_path' => 'events/banner.jpg',
            'date' => '2026-09-24',
            'time' => '17:00:00',
            'venue' => 'Sathwara Community Hall',
            'event_type' => 'inam_vitaran',
            'status' => 'published',
        ]);

        $sponsor = EventSponsor::create([
            'event_id' => $event->id,
            'name' => 'Shree Ram Enterprises',
            'contact_person' => 'Jigneshbhai Sathwara',
            'mobile' => '9825012345',
            'email' => 'sponsor@example.com',
            'city' => 'Ahmedabad',
            'amount' => 25000.00,
            'payment_status' => 'received',
            'payment_id' => 'pay_SPN_10004',
            'status' => 'approved',
        ]);

        $html = view('emails.receipt_pdf.sponsorship', [
            'event' => $event,
            'sponsor' => $sponsor,
            'sponsorshipType' => null,
            'receiptNo' => '2026-27/00004',
            'amount' => 25000.00,
            'paymentStatus' => 'received',
            'paymentId' => 'pay_SPN_10004',
        ])->render();

        // Check for centered RECEIPT title
        $this->assertStringContainsString('text-align: center;', $html);
        $this->assertStringContainsString('RECEIPT', $html);

        // Check for acknowledgement box and exact format
        $this->assertStringContainsString('ack-box', $html);
        $this->assertStringContainsString('Shree', $html);
        $this->assertStringContainsString('Shree Ram Enterprises', $html);
        $this->assertStringContainsString('Contact Person:', $html);
        $this->assertStringContainsString('Jigneshbhai Sathwara', $html);
        $this->assertStringContainsString('Address', $html);
        $this->assertStringContainsString('Ahmedabad', $html);
        $this->assertStringContainsString('Mobile', $html);
        $this->assertStringContainsString('9825012345', $html);
        $this->assertStringContainsString('we have received a sum of Rupees', $html);
        $this->assertStringContainsString('25,000.00', $html);
        $this->assertStringContainsString('Twenty-Five Thousand INR', $html);
        $this->assertStringContainsString('as per the details below:', $html);
    }

    public function test_membership_receipt_view_renders_centered_receipt_and_ack_box()
    {
        $user = User::factory()->create([
            'name' => 'Rameshbhai Sathwara',
            'email' => 'ramesh@test.com',
        ]);

        $html = view('emails.receipt_pdf.membership', [
            'user' => $user,
            'profile' => null,
            'receiptNo' => '2026-27/00001',
            'amount' => 1000.00,
            'paymentStatus' => 'received',
            'paymentId' => 'pay_MEM_1001',
        ])->render();

        $this->assertStringContainsString('text-align: center;', $html);
        $this->assertStringContainsString('RECEIPT', $html);
        $this->assertStringContainsString('ack-box', $html);
        $this->assertStringContainsString('Shree', $html);
        $this->assertStringContainsString('Rameshbhai Sathwara', $html);
        $this->assertStringContainsString('we have received a sum of Rupees', $html);
        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringContainsString('One Thousand INR', $html);
    }
}
