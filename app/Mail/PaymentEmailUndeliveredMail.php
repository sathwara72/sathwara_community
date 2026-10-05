<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Office notice: a payment was received but its receipt could not be emailed to the buyer.
 */
class PaymentEmailUndeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string, scalar|null> $details
     */
    public function __construct(
        public string $purpose,
        public string $intendedEmail,
        public string $problem,
        public array $details,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Payment Received — Receipt Not Delivered ({$this->purpose})");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment_email_undelivered');
    }
}
