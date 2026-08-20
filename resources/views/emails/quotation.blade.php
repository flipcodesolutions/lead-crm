<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 30px;
            color: #ffffff;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .quotation-box {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">{{ $companyName }}</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Quotation #{{ $quotation->quotation_number }}</p>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $quotation->customer_name }}</strong>,</p>
            
            <p>{{ $customMessage ?? 'Thank you for your interest. Please find attached the quotation as per your requirements.' }}</p>

            <div class="quotation-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Quotation Number:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold;">{{ $quotation->quotation_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Quotation Date:</td>
                        <td style="padding: 6px 0; text-align: right;">{{ $quotation->quotation_date->format('M d, Y') }}</td>
                    </tr>
                    @if($quotation->valid_until)
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Valid Until:</td>
                        <td style="padding: 6px 0; text-align: right;">{{ $quotation->valid_until->format('M d, Y') }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #cbd5e1;">
                        <td style="padding: 12px 0 0 0; font-size: 16px; font-weight: bold;">Total Amount:</td>
                        <td style="padding: 12px 0 0 0; text-align: right;" class="amount">{{ \App\Helpers\CurrencyHelper::format($quotation->total_amount) }}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 13px; color: #64748b;">
                A PDF copy of this quotation has been attached to this email for your review and approval.
            </p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>{{ $quotation->creator?->name ?? $companyName }}</strong>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
        </div>
    </div>
</body>
</html>
