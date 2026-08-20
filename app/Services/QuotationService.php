<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuotationService
{
    /**
     * Calculate item totals and create quotation with items.
     */
    public function createQuotation(array $data, array $items, int $userId): Quotation
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $data['quotation_number'] = Quotation::generateQuotationNumber();
            $data['created_by'] = $userId;
            $data['status'] = $data['status'] ?? 'Draft';

            // Calculate item lines
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            $processedItems = [];
            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $itemDiscount = (float)($item['discount'] ?? 0);
                $taxPercent = (float)($item['tax_percentage'] ?? 0);

                $lineSubtotal = ($qty * $price) - $itemDiscount;
                if ($lineSubtotal < 0) $lineSubtotal = 0;

                $lineTax = ($lineSubtotal * $taxPercent) / 100;
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += ($qty * $price);
                $discountAmount += $itemDiscount;
                $taxAmount += $lineTax;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'discount' => $itemDiscount,
                    'tax_percentage' => $taxPercent,
                    'total' => $lineTotal,
                ];
            }

            $overallDiscount = (float)($data['discount_amount'] ?? $discountAmount);
            $totalAmount = ($subtotal - $overallDiscount) + $taxAmount;
            if ($totalAmount < 0) $totalAmount = 0;

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['discount_amount'] = $overallDiscount;
            $data['total_amount'] = $totalAmount;

            $quotation = Quotation::create($data);

            foreach ($processedItems as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                QuotationItem::create($itemData);
            }

            return $quotation->load('items.service', 'opportunity', 'lead');
        });
    }

    /**
     * Update quotation and its items.
     */
    public function updateQuotation(Quotation $quotation, array $data, array $items): Quotation
    {
        return DB::transaction(function () use ($quotation, $data, $items) {
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            $processedItems = [];
            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $itemDiscount = (float)($item['discount'] ?? 0);
                $taxPercent = (float)($item['tax_percentage'] ?? 0);

                $lineSubtotal = ($qty * $price) - $itemDiscount;
                if ($lineSubtotal < 0) $lineSubtotal = 0;

                $lineTax = ($lineSubtotal * $taxPercent) / 100;
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += ($qty * $price);
                $discountAmount += $itemDiscount;
                $taxAmount += $lineTax;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'discount' => $itemDiscount,
                    'tax_percentage' => $taxPercent,
                    'total' => $lineTotal,
                ];
            }

            $overallDiscount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : $discountAmount;
            $totalAmount = ($subtotal - $overallDiscount) + $taxAmount;
            if ($totalAmount < 0) $totalAmount = 0;

            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $taxAmount;
            $data['discount_amount'] = $overallDiscount;
            $data['total_amount'] = $totalAmount;

            $quotation->update($data);

            // Recreate items
            $quotation->items()->delete();
            foreach ($processedItems as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                QuotationItem::create($itemData);
            }

            return $quotation->load('items.service', 'opportunity', 'lead');
        });
    }

    /**
     * Update quotation status.
     */
    public function updateStatus(Quotation $quotation, string $status): Quotation
    {
        $quotation->update(['status' => $status]);

        // If Accepted and linked to opportunity, can optionally mark opportunity as Won
        if ($status === 'Accepted' && $quotation->opportunity) {
            $opportunityService = app(OpportunityService::class);
            $opportunityService->markWon($quotation->opportunity);
        }

        return $quotation;
    }

    /**
     * Generate PDF for quotation.
     */
    public function generatePdf(Quotation $quotation)
    {
        $quotation->load('items.service', 'opportunity', 'lead', 'creator');
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

        return $pdf;
    }

    /**
     * Send email quotation and log to database.
     */
    public function sendEmail(Quotation $quotation, string $toEmail, string $subject, string $message): Email
    {
        $status = 'Sent';
        try {
            \Illuminate\Support\Facades\Mail::to($toEmail)
                ->send(new \App\Mail\QuotationMailable($quotation, $subject, $message));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending quotation email: " . $e->getMessage());
            $status = 'Failed';
        }

        $emailRecord = Email::create([
            'lead_id' => $quotation->lead_id,
            'user_id' => auth()->id(),
            'to_email' => $toEmail,
            'subject' => $subject,
            'message' => $message,
            'sent_at' => now(),
            'status' => $status,
        ]);

        // Update quotation status to Sent if it was Draft and email succeeded
        if ($quotation->status === 'Draft' && $status === 'Sent') {
            $quotation->update(['status' => 'Sent']);
        }

        return $emailRecord;
    }
}
