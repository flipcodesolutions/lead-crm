@extends('layouts.app')

@section('title', 'Map Lead Columns')
@section('page_title', 'Step 2: Map Columns to CRM Fields')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11 col-lg-12">
        
        <!-- Header / Stepper Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="bi bi-diagram-3 text-primary me-2"></i> Column Mapping Wizard
                        </h4>
                        <p class="text-muted text-sm mb-0">
                            Match your file's columns to CRM database fields. Unmapped campaign questions will be neatly saved into the lead notes.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <div class="text-xs text-muted text-uppercase fw-semibold">File Details</div>
                            <div class="fw-bold text-dark text-sm">{{ $settings['original_filename'] }}</div>
                        </div>
                        <span class="badge bg-primary fs-6 rounded-pill px-3 py-2">
                            {{ $totalRows }} Leads Detected
                        </span>
                    </div>
                </div>

                <!-- Stepper Badges -->
                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 text-xs">
                        <i class="bi bi-check-circle me-1"></i> Step 1: Upload Complete
                    </span>
                    <i class="bi bi-chevron-right text-muted text-xs"></i>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 text-xs">
                        <strong>Step 2:</strong> Map Columns & Fields
                    </span>
                    <i class="bi bi-chevron-right text-muted text-xs"></i>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 text-xs">
                        <strong>Step 3:</strong> Finalize
                    </span>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Mapping Form -->
        <form action="{{ route('leads.import.execute') }}" method="POST" id="mappingForm">
            @csrf

            <!-- Hidden settings from Step 1 -->
            <input type="hidden" name="temp_file_path" value="{{ $settings['temp_file_path'] }}">
            <input type="hidden" name="stage_id" value="{{ $settings['stage_id'] }}">
            <input type="hidden" name="status_id" value="{{ $settings['status_id'] }}">
            <input type="hidden" name="source_id" value="{{ $settings['source_id'] }}">
            <input type="hidden" name="assignment_mode" value="{{ $settings['assignment_mode'] }}">
            <input type="hidden" name="assigned_user_id" value="{{ $settings['assigned_user_id'] }}">
            <input type="hidden" name="duplicate_action" value="{{ $settings['duplicate_action'] }}">
            <input type="hidden" name="auto_append_unmapped" value="{{ $settings['auto_append_unmapped'] ? '1' : '0' }}">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-table text-primary me-2"></i> {{ count($headers) }} Columns Found in Uploaded File
                    </h6>
                    <span class="text-xs text-muted">
                        💡 Our system automatically matched standard columns. Adjust mappings below if needed.
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-xs text-uppercase">
                                <tr>
                                    <th class="ps-4" style="width: 25%;">File Column Header</th>
                                    <th style="width: 35%;">Sample Values from Your File</th>
                                    <th style="width: 30%;">Map to CRM Target Field</th>
                                    <th class="text-end pe-4" style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($headers as $header)
                                    @php
                                        $suggested = $suggestedMapping[$header] ?? 'append_to_notes';
                                    @endphp
                                    <tr>
                                        <!-- Column Name -->
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark text-sm">
                                                <code>{{ $header }}</code>
                                            </div>
                                            <span class="text-xs text-muted">
                                                {{ ucwords(str_replace(['_', '-'], ' ', $header)) }}
                                            </span>
                                        </td>

                                        <!-- Sample Values -->
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @forelse($previewRows as $rowIdx => $row)
                                                    @if(!empty($row[$header]))
                                                        <div class="text-xs text-secondary text-truncate" style="max-width: 320px;">
                                                            <span class="badge bg-light text-dark border me-1">#{{ $rowIdx + 1 }}</span>
                                                            {{ $row[$header] }}
                                                        </div>
                                                    @endif
                                                @empty
                                                    <span class="text-muted text-xs">No sample data</span>
                                                @endforelse
                                            </div>
                                        </td>

                                        <!-- Target Field Selector -->
                                        <td>
                                            <select name="mapping[{{ $header }}]" class="form-select form-select-sm mapping-select" onchange="updateRowStatus(this)">
                                                <option value="name" {{ $suggested == 'name' ? 'selected' : '' }}>
                                                    👤 Full Name / Contact Name (Required)
                                                </option>
                                                <option value="phone" {{ $suggested == 'phone' ? 'selected' : '' }}>
                                                    📞 Mobile / Phone Number
                                                </option>
                                                <option value="email" {{ $suggested == 'email' ? 'selected' : '' }}>
                                                    ✉️ Email Address
                                                </option>
                                                <option value="company_name" {{ $suggested == 'company_name' ? 'selected' : '' }}>
                                                    🏢 Company / Organization Name
                                                </option>
                                                <option value="alternate_phone" {{ $suggested == 'alternate_phone' ? 'selected' : '' }}>
                                                    📱 Alternate Phone Number
                                                </option>
                                                <option value="city" {{ $suggested == 'city' ? 'selected' : '' }}>
                                                    🏙️ City
                                                </option>
                                                <option value="state" {{ $suggested == 'state' ? 'selected' : '' }}>
                                                    📍 State
                                                </option>
                                                <option value="address" {{ $suggested == 'address' ? 'selected' : '' }}>
                                                    🏠 Street / Full Address
                                                </option>
                                                <option value="expected_value" {{ $suggested == 'expected_value' ? 'selected' : '' }}>
                                                    💰 Expected Deal Value (₹)
                                                </option>
                                                <option value="source" {{ $suggested == 'source' ? 'selected' : '' }}>
                                                    🌐 Lead Source / Platform
                                                </option>
                                                <option value="description" {{ $suggested == 'description' ? 'selected' : '' }}>
                                                    📝 Main Description / Inquiry Notes
                                                </option>
                                                <option value="append_to_notes" {{ $suggested == 'append_to_notes' ? 'selected' : '' }}>
                                                    📋 Append to Campaign Inquiry Notes
                                                </option>
                                                <option value="ignore" {{ $suggested == 'ignore' ? 'selected' : '' }}>
                                                    ❌ Do Not Import (Skip Column)
                                                </option>
                                            </select>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="text-end pe-4">
                                            <span class="badge {{ $suggested == 'ignore' ? 'bg-secondary-subtle text-secondary' : 'bg-success-subtle text-success' }} row-status-badge">
                                                {{ $suggested == 'ignore' ? 'Skipped' : 'Mapped' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Bottom Action Footer -->
                <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('leads.import') }}" class="btn btn-light rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back to Step 1
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Complete & Import {{ $totalRows }} Leads
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function updateRowStatus(select) {
        const tr = select.closest('tr');
        const badge = tr.querySelector('.row-status-badge');
        if (select.value === 'ignore') {
            badge.className = 'badge bg-secondary-subtle text-secondary row-status-badge';
            badge.textContent = 'Skipped';
        } else {
            badge.className = 'badge bg-success-subtle text-success row-status-badge';
            badge.textContent = 'Mapped';
        }
    }
</script>
@endsection
