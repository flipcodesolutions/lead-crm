@extends('layouts.app')

@section('title', 'Bulk Lead Import')
@section('page_title', 'Bulk Lead Import Wizard')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        
        <!-- Header / Stepper Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="bi bi-cloud-arrow-up text-primary me-2"></i> Bulk Lead Import Wizard
                        </h4>
                        <p class="text-muted text-sm mb-0">
                            Upload your lead dump from Meta Ads (Facebook/Instagram), IndiaMART, Justdial, or Excel CSV.
                        </p>
                    </div>
                </div>

                <!-- Stepper Badges -->
                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 text-xs">
                        <strong>Step 1:</strong> Upload File & Settings
                    </span>
                    <i class="bi bi-chevron-right text-muted text-xs"></i>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 text-xs">
                        <strong>Step 2:</strong> Map Columns & Fields
                    </span>
                    <i class="bi bi-chevron-right text-muted text-xs"></i>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 text-xs">
                        <strong>Step 3:</strong> Review & Complete
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

        <!-- Upload Form Card -->
        <form action="{{ route('leads.import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0">1. Select Excel / CSV Data File</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <!-- Drag & Drop Area -->
                    <div class="p-4 p-md-5 border-2 border-dashed rounded-3 text-center bg-light mb-3" id="dropZone" style="border-color: #cbd5e1 !important; cursor: pointer;">
                        <i class="bi bi-file-earmark-excel-fill text-success" style="font-size: 3rem;"></i>
                        <h6 class="fw-bold text-dark mt-2 mb-1">Click to browse or drag and drop your Excel / CSV file here</h6>
                        <p class="text-xs text-muted mb-3">Supported formats: <strong>.xlsx, .xls, .csv, .txt</strong> (Excel workbooks & delimited sheets up to 20MB)</p>
                        
                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv,.txt" required>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-folder2-open me-1"></i> Choose Excel / CSV File
                        </button>
                        <div id="fileInfo" class="mt-3 text-sm fw-semibold text-success d-none">
                            <i class="bi bi-check-circle-fill me-1"></i> <span id="fileName"></span> (<span id="fileSize"></span>)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Settings & Defaults Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0">2. Default Pipeline & Assignment Rules</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <!-- Default Stage -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Initial Pipeline Stage <span class="text-danger">*</span></label>
                            <select name="stage_id" class="form-select" required>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ $stage->name == 'New' ? 'selected' : '' }}>{{ $stage->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-xs text-muted">Leads will start in this pipeline stage.</div>
                        </div>

                        <!-- Default Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Initial Status</label>
                            <select name="status_id" class="form-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $status->name == 'New' ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Default Source -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Fallback Lead Source</label>
                            <select name="source_id" class="form-select">
                                <option value="">Auto-Detect from File / Default</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-xs text-muted">Used when the file has no source column.</div>
                        </div>

                        <!-- Lead Assignment Mode -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Lead Assignment Strategy <span class="text-danger">*</span></label>
                            <select name="assignment_mode" id="assignmentMode" class="form-select" onchange="toggleUserSelect()" required>
                                <option value="round_robin">Round-Robin (Equally distribute among Sales/Telecallers)</option>
                                <option value="specific">Assign all to a specific Sales Representative / Telecaller</option>
                                <option value="unassigned">Keep Unassigned (Distribute later from Lead Hub)</option>
                            </select>
                        </div>

                        <!-- Specific User -->
                        <div class="col-md-6" id="specificUserCol" style="display: none;">
                            <label class="form-label fw-semibold text-sm">Select Sales Representative</label>
                            <select name="assigned_user_id" class="form-select">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role?->name ?? 'Rep' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Duplicate Handling -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Duplicate Phone/Email Strategy <span class="text-danger">*</span></label>
                            <select name="duplicate_action" class="form-select" required>
                                <option value="skip" selected>Skip Duplicate (Do not import if phone/email exists)</option>
                                <option value="update">Update Existing Lead (Merge latest details & notes)</option>
                                <option value="import">Import Anyway (Allow duplicate records)</option>
                            </select>
                        </div>

                        <!-- Custom Field Auto-Append -->
                        <div class="col-12 mt-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="auto_append_unmapped" value="1" id="autoAppend" checked>
                                    <label class="form-check-label fw-semibold text-sm" for="autoAppend">
                                        <i class="bi bi-card-checklist text-primary me-1"></i> Automatically compile all extra campaign questions into Lead Description
                                    </label>
                                    <div class="text-xs text-muted mt-1 ps-4">
                                        Captures Meta Ads questions (e.g. <em>what_best_describes_you</em>, <em>approximate_area</em>, <em>when_are_you_planning</em>, <em>inbox_url</em>) directly into the lead record so zero data is lost.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('leads.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn" disabled>
                        Next: Map Columns <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
            fileInfo.classList.remove('d-none');
            submitBtn.disabled = false;
        }
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#e0e7ff';
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#f8fafc';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#f8fafc';
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            const file = e.dataTransfer.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
            fileInfo.classList.remove('d-none');
            submitBtn.disabled = false;
        }
    });

    function toggleUserSelect() {
        const mode = document.getElementById('assignmentMode').value;
        const col = document.getElementById('specificUserCol');
        if (mode === 'specific') {
            col.style.display = 'block';
        } else {
            col.style.display = 'none';
        }
    }
</script>
@endsection
