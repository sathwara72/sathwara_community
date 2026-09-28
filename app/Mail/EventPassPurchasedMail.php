<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Attachment;
use App\Services\ReceiptPdfService;

class EventPassPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public EventRegistration $registration;
    public ?User $user;
    public array $passes;
    public int $personCount;
    public string $receiptNo;
    public float $amount;
    public string $paymentStatus;
    public ?string $paymentId;
    /** @var array<int, string> QR image URLs keyed by zero-based pass index */
    public array $qrUrls = [];

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, EventRegistration $registration, ?User $user = null, array $passes = [], int $personCount = 1)
    {
        $this->event = $event;
        $this->registration = $registration;
        $this->user = $user;
        $this->passes = $passes;
        $this->personCount = $personCount;
        $this->amount = (float)($registration->payment_amount ?? 0);
        $this->paymentStatus = $registration->payment_status ?? 'paid';
        $this->paymentId = $registration->payment_id;
        $this->receiptNo = \App\Services\ReceiptNumberService::assign($registration, 'receipt_no');

        foreach (\App\Services\PassTokenService::getOrGenerateTokens($registration) as $token) {
            $this->qrUrls[$token->pass_index - 1] = \App\Services\PassTokenService::getQrCodeImageUrl($token->token_hash);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟️ Entry Pass & Payment Receipt #' . $this->receiptNo . ' - ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.event_pass',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = ReceiptPdfService::make('emails.receipt_pdf.event_pass', [
            'event' => $this->event,
            'registration' => $this->registration,
            'user' => $this->user,
            'passes' => $this->passes,
            'personCount' => $this->personCount,
            'receiptNo' => $this->receiptNo,
            'amount' => $this->amount,
            'paymentStatus' => $this->paymentStatus,
            'paymentId' => $this->paymentId,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Event_Pass_Receipt_' . str_replace('/', '-', $this->receiptNo) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
