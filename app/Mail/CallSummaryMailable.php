<?php

namespace App\Mail;

use App\Models\FollowUp;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CallSummaryMailable extends Mailable
{
    use Queueable, SerializesModels;

    public FollowUp $followUp;
    public string $emailSubject;
    public string $discussionNotes;
    public ?string $nextActionDate;

    public function __construct(FollowUp $followUp, ?string $emailSubject = null, ?string $discussionNotes = null, ?string $nextActionDate = null)
    {
        $this->followUp = $followUp->load('lead', 'user');
        $this->emailSubject = $emailSubject ?: "Call Summary & Discussion Notes - " . ($this->followUp->lead->company_name ?: $this->followUp->lead->name);
        $this->discussionNotes = $discussionNotes ?: ($this->followUp->result ?: $this->followUp->description ?: 'Thank you for speaking with us today.');
        $this->nextActionDate = $nextActionDate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.call_summary',
            with: [
                'followUp' => $this->followUp,
                'lead' => $this->followUp->lead,
                'rep' => $this->followUp->user,
                'discussionNotes' => $this->discussionNotes,
                'nextActionDate' => $this->nextActionDate,
                'companyName' => Setting::get('company_name', config('app.name', 'My Lead CRM')),
                'companyEmail' => Setting::get('company_email', 'contact@company.com'),
                'companyPhone' => Setting::get('company_phone', '+1 (555) 019-2834'),
                'companyAddress' => Setting::get('company_address', '123 Business Avenue, Suite 500'),
            ],
        );
    }
}
