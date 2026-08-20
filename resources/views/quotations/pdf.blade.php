<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .quote-title {
            font-size: 26px;
            font-weight: bold;
            color: #1e293b;
            text-align: right;
            text-transform: uppercase;
        }
        .quote-number {
            font-size: 14px;
            color: #64748b;
            text-align: right;
        }
        .details-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .details-table td {
            vertical-align: top;
        }
        .section-label {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 6px 8px;
        }
        .grand-total-row {
            border-top: 2px solid #4f46e5;
            font-size: 15px;
            font-weight: bold;
            color: #4f46e5;
        }
        .notes-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            font-size: 11px;
            color: #64748b;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="company-title">{{ $companyName }}</div>
                <div style="color: #64748b; font-size: 11px;">
                    {{ $companyAddress }}<br>
                    Email: {{ $companyEmail }} | Phone: {{ $companyPhone }}
                </div>
            </td>
            <td style="width: 40%;" class="text-right">
                <div class="quote-title">Quotation</div>
                <div class="quote-number">#{{ $quotation->quotation_number }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    Date: {{ $quotation->quotation_date->format('M d, Y') }}<br>
                    @if($quotation->valid_until)
                        Valid Until: {{ $quotation->valid_until->format('M d, Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Customer Details -->
    <table class="details-table">
        <tr>
            <td style="width: 55%;">
                <div class="section-label">Prepared For:</div>
                <div style="font-size: 15px; font-weight: bold; color: #1e293b;">{{ $quotation->customer_name }}</div>
                @if($quotation->customer_email)
                    <div>Email: {{ $quotation->customer_email }}</div>
                @endif
                @if($quotation->customer_phone)
                    <div>Phone: {{ $quotation->customer_phone }}</div>
                @endif
            </td>
            <td style="width: 45%;" class="text-right">
                <div class="section-label">Sales Representative:</div>
                <div class="fw-bold">{{ $quotation->creator?->name ?? 'Sales Team' }}</div>
                <div style="color: #64748b;">Status: {{ $quotation->status }}</div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Item & Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Unit Price</th>
                <th class="text-center" style="width: 10%;">Tax</th>
                <th class="text-right" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        @if($item->service)
                            <div class="fw-bold" style="color: #1e293b;">{{ $item->service->name }}</div>
                        @endif
                        <div style="color: #64748b; font-size: 11px;">{{ $item->description }}</div>
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($item->price) }}</td>
                    <td class="text-center">{{ $item->tax_percentage }}%</td>
                    <td class="text-right fw-bold">{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="text-right" style="color: #64748b;">Subtotal:</td>
            <td class="text-right fw-bold">{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($quotation->subtotal) }}</td>
        </tr>
        @if($quotation->discount_amount > 0)
            <tr>
                <td class="text-right" style="color: #dc2626;">Discount:</td>
                <td class="text-right" style="color: #dc2626;">-{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($quotation->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-right" style="color: #64748b;">GST / Tax:</td>
            <td class="text-right fw-bold">{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($quotation->tax_amount) }}</td>
        </tr>
        <tr class="grand-total-row">
            <td class="text-right" style="padding-top: 8px;">Grand Total:</td>
            <td class="text-right" style="padding-top: 8px;">{{ $currencySymbol }}{{ \App\Helpers\CurrencyHelper::formatInr($quotation->total_amount) }}</td>
        </tr>
    </table>

    <!-- Notes & Terms -->
    @if($quotation->notes || $quotation->terms_conditions)
        <div class="notes-section">
            @if($quotation->notes)
                <div style="margin-bottom: 10px;">
                    <strong style="color: #334155;">Customer Notes:</strong><br>
                    {{ $quotation->notes }}
                </div>
            @endif
            @if($quotation->terms_conditions)
                <div>
                    <strong style="color: #334155;">Terms & Conditions:</strong><br>
                    {!! nl2br(e($quotation->terms_conditions)) !!}
                </div>
            @endif
        </div>
    @endif

</body>
</html>
