<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a member that the office asked them to correct an event submission, or rejected it, and why.
 */
class SubmissionReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $outcome 'correction_required' or 'rejected'
     */
    public function __construct(
        public string $outcome,
        public string $recipientName,
        public string $eventTitle,
        public string $studentName,
        public string $note,
        public string $editUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        $what = $this->outcome === 'rejected' ? 'Submission Rejected' : 'Correction Required';

        return new Envelope(
            subject: "Shree Satwara Gnati Mandal, Ahmedabad — {$what}: {$this->studentName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.submission_review');
    }
}
