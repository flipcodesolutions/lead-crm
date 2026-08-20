<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Summary - {{ $lead->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 28px 30px;
            color: #ffffff;
        }
        .header h2 {
            margin: 0 0 4px 0;
            font-size: 22px;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .meta-table td {
            padding: 5px 0;
        }
        .meta-label {
            color: #64748b;
            width: 35%;
            font-weight: 500;
        }
        .meta-value {
            color: #0f172a;
            font-weight: 600;
        }
        .discussion-box {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 4px;
            padding: 16px 20px;
            margin: 20px 0;
        }
        .discussion-title {
            color: #15803d;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .discussion-text {
            color: #1e293b;
            font-size: 14px;
            white-space: pre-line;
            margin: 0;
        }
        .next-steps-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
            padding: 14px 18px;
            margin: 18px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .footer {
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>{{ $companyName }}</h2>
            <p>Summary of Discussion & Call Notes</p>
        </div>

        <!-- Body -->
        <div class="content">
            <p style="font-size: 15px; margin-top: 0;">
                Dear <strong>{{ $lead->name }}</strong>,
            </p>
            <p style="font-size: 14px; color: #475569;">
                Thank you for taking the time to speak with our representative today. Below is a recap of our conversation and the key points discussed for your reference:
            </p>

            <!-- Call Meta Details -->
            <div class="meta-card">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Reference ID:</td>
                        <td class="meta-value">{{ $lead->lead_number }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Interaction Type:</td>
                        <td class="meta-value">{{ $followUp->type ?? 'Phone Call' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Discussion Subject:</td>
                        <td class="meta-value">{{ $followUp->subject }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date & Time:</td>
                        <td class="meta-value">
                            {{ $followUp->follow_up_date ? $followUp->follow_up_date->format('M d, Y') : date('M d, Y') }} 
                            @if($followUp->follow_up_time) &bull; {{ $followUp->follow_up_time }} @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Representative:</td>
                        <td class="meta-value">{{ $rep?->name ?? 'Sales Support Team' }} @if($rep?->phone) ({{ \App\Helpers\CurrencyHelper::formatPhone($rep->phone) }}) @endif</td>
                    </tr>
                </table>
            </div>

            <!-- Discussion Notes & Talk Summary -->
            <div class="discussion-box">
                <div class="discussion-title">📝 Summary of Discussion / Talk Notes:</div>
                <div class="discussion-text">{{ $discussionNotes }}</div>
            </div>

            <!-- Next Scheduled Steps (if applicable) -->
            @if($nextActionDate)
                <div class="next-steps-box">
                    <strong>📅 Next Action / Follow-up:</strong><br>
                    Our representative will connect back with you on <strong>{{ $nextActionDate }}</strong>.
                </div>
            @endif

            <p style="font-size: 14px; color: #475569; margin-top: 24px;">
                If you have any questions, require additional clarifications, or would like to add any notes to this discussion, please feel free to reply directly to this email or call us at <strong>{{ \App\Helpers\CurrencyHelper::formatPhone($companyPhone) }}</strong>.
            </p>

            <p style="margin-top: 24px; font-size: 14px;">
                Best regards,<br>
                <strong>{{ $rep?->name ?? 'Client Support Team' }}</strong><br>
                <span style="color: #64748b; font-size: 13px;">{{ $companyName }}</span>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>{{ $companyName }} &bull; {{ $companyAddress }}</div>
            <div style="margin-top: 4px;">Email: {{ $companyEmail }} | Phone: {{ $companyPhone }}</div>
            <div style="margin-top: 8px; color: #cbd5e1;">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</div>
        </div>
    </div>
</body>
</html>
