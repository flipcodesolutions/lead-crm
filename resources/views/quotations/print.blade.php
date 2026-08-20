<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Quotation - {{ $quotation->quotation_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        body {
            background-color: #f8fafc;
            color: #334155;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .print-container {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    @php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

    <div class="container text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4"><i class="bi bi-printer me-1"></i> Print Invoice</button>
        <button onclick="window.close()" class="btn btn-light border px-4 ms-2">Close Window</button>
    </div>

    <div class="print-container">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">{{ \App\Models\Setting::get('company_name', config('app.name', 'Odoo CRM')) }}</h3>
                <div class="text-muted text-sm">
                    <div>{{ \App\Models\Setting::get('company_address', '123 Business Avenue, Suite 500') }}</div>
                    <div>Email: {{ \App\Models\Setting::get('company_email', 'contact@company.com') }} | Phone: {{ \App\Models\Setting::get('company_phone', '+1 555-0199') }}</div>
                </div>
            </div>
            <div class="text-end">
                <h2 class="fw-bold text-uppercase text-dark mb-1">QUOTATION</h2>
                <div class="badge bg-light text-dark border fs-6 px-3 py-1 mb-2">#{{ $quotation->quotation_number }}</div>
                <div class="text-xs text-muted"><strong>Date:</strong> {{ $quotation->quotation_date->format('M d, Y') }}</div>
                @if($quotation->valid_until)
                    <div class="text-xs text-muted"><strong>Valid Until:</strong> {{ $quotation->valid_until->format('M d, Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Bill To -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <span class="text-xs text-muted text-uppercase fw-semibold d-block mb-1">Prepared For:</span>
                <h5 class="fw-bold mb-1 text-dark">{{ $quotation->customer_name }}</h5>
                @if($quotation->customer_email)
                    <div class="text-sm text-muted">{{ $quotation->customer_email }}</div>
                @endif
                @if($quotation->customer_phone)
                    <div class="text-sm text-muted">{{ $quotation->customer_phone }}</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                <span class="text-xs text-muted text-uppercase fw-semibold d-block mb-1">Prepared By:</span>
                <div class="fw-bold text-dark">{{ $quotation->creator?->name ?? 'Sales Team' }}</div>
                <span class="text-xs text-muted">Status: {{ $quotation->status }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 45%;">Item / Description</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Unit Price</th>
                        <th class="text-center" style="width: 10%;">Tax</th>
                        <th class="text-end" style="width: 15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                @if($item->service)
                                    <div class="fw-bold text-dark">{{ $item->service->name }}</div>
                                @endif
                                <div class="text-sm text-secondary">{{ $item->description }}</div>
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">@inr($item->price)</td>
                            <td class="text-center">{{ $item->tax_percentage }}%</td>
                            <td class="text-end fw-bold text-dark">@inr($item->total)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="row justify-content-end mb-4">
            <div class="col-md-5">
                <div class="p-3 bg-light rounded-3">
                    <div class="d-flex justify-content-between text-sm py-1">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold text-dark">@inr($quotation->subtotal)</span>
                    </div>
                    @if($quotation->discount_amount > 0)
                        <div class="d-flex justify-content-between text-sm py-1">
                            <span class="text-muted">Discount:</span>
                            <span class="text-danger fw-semibold">-@inr($quotation->discount_amount)</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-sm py-1">
                        <span class="text-muted">GST / Tax:</span>
                        <span class="text-dark fw-semibold">+@inr($quotation->tax_amount)</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <h5 class="fw-bold m-0 text-dark">Grand Total:</h5>
                        <h4 class="fw-bold m-0 text-primary">@inr($quotation->total_amount)</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($quotation->notes || $quotation->terms_conditions)
            <div class="row g-4 pt-3 border-top text-xs text-muted">
                @if($quotation->notes)
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark text-xs text-uppercase mb-1">Customer Notes:</h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $quotation->notes }}</p>
                    </div>
                @endif
                @if($quotation->terms_conditions)
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark text-xs text-uppercase mb-1">Terms & Conditions:</h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $quotation->terms_conditions }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        window.addEventListener('load', () => {
            // Optional auto print if opened in new tab
        });
    </script>
</body>
</html>
