<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Receipt for the Yuva Melo candidate form fee.
 */
class YuvaMeloFeeReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public EventRegistration $registration,
        public string $receiptNo,
        public float $amount,
        public ?string $paymentId,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🧾 Yuva Melo Form Fee Receipt #' . $this->receiptNo . ' - ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.yuva_melo_fee_receipt');
    }
}
