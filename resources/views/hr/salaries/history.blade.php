@extends('layouts.app')

@section('title', 'Salary History: ' . $employee->full_name)
@section('page_title', 'Compensation History: ' . $employee->full_name)

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle bg-primary text-white" style="width: 48px; height: 48px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">{{ $employee->full_name }}</h5>
                    <span class="text-xs text-muted">{{ $employee->employee_code }} &bull; {{ $employee->designation->name ?? 'Staff' }} ({{ $employee->department->name ?? 'Unassigned' }})</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hr.salaries.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Revision
                </a>
                <a href="{{ route('hr.employees.show', $employee->id) }}" class="btn btn-light border btn-sm rounded-pill px-3">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4">
        <h6 class="fw-bold m-0"><i class="bi bi-journal-text text-primary me-2"></i> Historical Timeline of Salary Revisions</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Effective Period</th>
                        <th>Basic Salary</th>
                        <th>HRA</th>
                        <th>Allowances</th>
                        <th>Other Earnings</th>
                        <th>Monthly Gross</th>
                        <th>PF / Deductions</th>
                        <th>Annual CTC</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $sal)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $sal->effective_from->format('d M Y') }}</div>
                                <span class="text-xs text-muted">{{ $sal->effective_to ? 'to ' . $sal->effective_to->format('d M Y') : 'to Present (Current)' }}</span>
                            </td>
                            <td>@inr($sal->basic_salary)</td>
                            <td>@inr($sal->hra)</td>
                            <td>@inr($sal->allowances)</td>
                            <td>@inr($sal->other_earnings)</td>
                            <td class="fw-bold text-primary">@inr($sal->gross_salary)</td>
                            <td class="text-danger">@inr($sal->pf_deduction + $sal->other_deduction)</td>
                            <td class="fw-bold text-dark">@inr($sal->annual_salary)</td>
                            <td class="text-end pe-4">
                                <span class="badge {{ $sal->status == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} badge-status">
                                    {{ $sal->status == 1 ? 'Active' : 'Archived' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No salary history recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
