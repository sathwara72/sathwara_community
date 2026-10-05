<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a member or business owner their application was rejected, and why.
 */
class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $kind 'membership' or 'business'
     */
    public function __construct(
        public string $kind,
        public string $recipientName,
        public string $applicationName,
        public string $reason,
        public ?string $contactEmail = null,
        public ?string $contactPhone = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $what = $this->kind === 'business' ? 'Business Directory Application' : 'Membership Application';

        return new Envelope(
            subject: "Shree Satwara Gnati Mandal, Ahmedabad — {$what} Not Approved",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.application_rejected');
    }
}
