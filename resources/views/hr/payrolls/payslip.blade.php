<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee->full_name }} - {{ $payroll->month_name }} {{ $payroll->year }}</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 13px;
        }
        .payslip-container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            padding: 40px;
        }
        .table-payslip th, .table-payslip td {
            padding: 10px 14px;
            border-color: #e2e8f0;
        }
        .net-pay-banner {
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 8px;
            padding: 16px;
        }
        @media print {
            body {
                background: #ffffff;
                margin: 0;
            }
            .payslip-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center max-w-800 mx-auto my-3 no-print" style="max-width: 800px;">
        <a href="{{ route('hr.payrolls.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Payrolls
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="bi bi-printer me-1"></i> Print Payslip / Save PDF
        </button>
    </div>

    <!-- Official Payslip Box -->
    <div class="payslip-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    <i class="bi bi-building me-1"></i> {{ config('app.name', 'Lead CRM') }}
                </h4>
                <p class="text-xs text-muted mb-0">Human Resources & Payroll Department</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary-subtle text-primary text-uppercase px-3 py-1.5 fw-bold tracking-wider">Salary Payslip</span>
                <div class="fw-bold text-dark mt-2">{{ $payroll->month_name }} {{ $payroll->year }}</div>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="row g-3 mb-4 pb-3 border-bottom text-sm">
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Employee Name</div>
                <div class="fw-bold text-dark">{{ $payroll->employee->full_name }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Employee Code</div>
                <div class="fw-semibold text-dark">{{ $payroll->employee->employee_code }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Department</div>
                <div class="fw-semibold text-dark">{{ $payroll->employee->department->name ?? 'General Staff' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Designation</div>
                <div class="fw-semibold text-dark">{{ $payroll->employee->designation->name ?? 'Staff' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Email</div>
                <div class="text-dark">{{ $payroll->employee->email }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Phone</div>
                <div class="text-dark">@phone($payroll->employee->phone)</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Date of Joining</div>
                <div class="text-dark">{{ $payroll->employee->joining_date ? $payroll->employee->joining_date->format('d M Y') : '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-xs text-muted text-uppercase">Payment Status</div>
                <span class="badge {{ $payroll->status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                    {{ $payroll->status }}
                </span>
            </div>
        </div>

        <!-- 2-Column Earnings & Deductions -->
        <div class="row g-4 mb-4">
            <!-- Earnings -->
            <div class="col-md-6">
                <table class="table table-bordered table-payslip mb-0">
                    <thead class="table-light text-xs text-uppercase">
                        <tr>
                            <th>Earnings Component</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-end">@inr($payroll->basic_salary)</td>
                        </tr>
                        <tr>
                            <td>House Rent Allowance (HRA)</td>
                            <td class="text-end">@inr($payroll->hra)</td>
                        </tr>
                        <tr>
                            <td>Special Allowances</td>
                            <td class="text-end">@inr($payroll->allowances)</td>
                        </tr>
                        <tr>
                            <td>Other Earnings / Variable</td>
                            <td class="text-end">@inr($payroll->other_earnings)</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Gross Earnings (A)</td>
                            <td class="text-end text-primary">@inr($payroll->gross_salary)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Deductions -->
            <div class="col-md-6">
                <table class="table table-bordered table-payslip mb-0">
                    <thead class="table-light text-xs text-uppercase">
                        <tr>
                            <th>Deductions Component</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Income Tax Withheld (TDS)</td>
                            <td class="text-end text-danger">@inr($payroll->tax)</td>
                        </tr>
                        <tr>
                            <td>Provident Fund (PF)</td>
                            <td class="text-end text-danger">@inr($payroll->pf_deduction)</td>
                        </tr>
                        <tr>
                            <td>Professional Tax / Other</td>
                            <td class="text-end text-danger">@inr($payroll->other_deduction)</td>
                        </tr>
                        <tr>
                            <td class="text-muted">&mdash;</td>
                            <td class="text-end text-muted">&mdash;</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Deductions (B)</td>
                            <td class="text-end text-danger">@inr($payroll->tax + $payroll->pf_deduction + $payroll->other_deduction)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Net Take Home Banner -->
        <div class="net-pay-banner text-center mb-4">
            <span class="text-xs text-uppercase fw-bold text-success-emphasis d-block mb-1">
                Net Take-Home Salary Payable (A &minus; B)
            </span>
            <div class="fs-2 fw-bold text-success">
                @inr($payroll->net_salary)
            </div>
            <div class="text-xs text-muted mt-1">
                Generated at: {{ $payroll->generated_at?->format('d M Y, h:i A') }}
            </div>
        </div>

        <!-- Signatures & Disclaimer -->
        <div class="row g-4 pt-4 mt-2 border-top text-xs text-muted">
            <div class="col-6">
                <div style="height: 40px;"></div>
                <div class="border-top pt-1 text-center" style="max-width: 180px;">
                    Employee Signature
                </div>
            </div>
            <div class="col-6 text-end">
                <div style="height: 40px;"></div>
                <div class="border-top pt-1 text-center ms-auto" style="max-width: 180px;">
                    Authorized HR Signature
                </div>
            </div>
            <div class="col-12 text-center text-muted text-xs pt-2">
                <small>* This is a computer-generated document and does not require a physical seal if verified digitally.</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
