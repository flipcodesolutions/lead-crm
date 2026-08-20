<?php

namespace App\Mail;

use App\Models\Quotation;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Quotation $quotation;
    public string $customMessage;
    public string $emailSubject;

    public function __construct(Quotation $quotation, string $emailSubject, string $customMessage)
    {
        $this->quotation = $quotation;
        $this->emailSubject = $emailSubject;
        $this->customMessage = $customMessage;
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
            view: 'emails.quotation',
            with: [
                'quotation' => $this->quotation,
                'customMessage' => $this->customMessage,
                'companyName' => Setting::get('company_name', config('app.name', 'My Lead CRM')),
                'currencySymbol' => Setting::get('currency_symbol', '$'),
            ],
        );
    }

    public function attachments(): array
    {
        $quotation = $this->quotation->load('items.service', 'opportunity', 'lead', 'creator');
        $companyName = Setting::get('company_name', config('app.name', 'My Lead CRM'));
        $companyEmail = Setting::get('company_email', 'contact@company.com');
        $companyPhone = Setting::get('company_phone', '+1 (555) 019-2834');
        $companyAddress = Setting::get('company_address', '123 Business Avenue, Suite 500, Tech Park');
        $currencySymbol = Setting::get('currency_symbol', '$');

        $pdf = Pdf::loadView('quotations.pdf', compact(
            'quotation',
            'companyName',
            'companyEmail',
            'companyPhone',
            'companyAddress',
            'currencySymbol'
        ));

        return [
            Attachment::fromData(fn () => $pdf->output(), "Quotation-{$this->quotation->quotation_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
