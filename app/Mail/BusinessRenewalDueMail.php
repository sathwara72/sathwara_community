<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once when a paid business membership expires: the listing is hidden until renewed.
 */
class BusinessRenewalDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Business $business, public float $fee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Business Membership Expired — Renew ' . $this->business->business_name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.business_renewal_due');
    }
}
