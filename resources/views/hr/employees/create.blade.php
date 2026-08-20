@extends('layouts.app')

@section('title', 'Add Employee')
@section('page_title', 'Create Employee Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-person-plus-fill text-primary me-2"></i> Employee Information Form</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.employees.store') }}" method="POST">
                    @csrf

                    <!-- Section 1: Basic & Identity -->
                    <h6 class="text-uppercase text-xs fw-bold text-primary tracking-wider mb-3">1. Personal & Contact Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror" value="{{ old('employee_code', $suggestedCode) }}" required>
                            @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Last Name</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+91 98765 43210">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-sm">Residential Address</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Street, City, State, PIN Code">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Section 2: Job & Hierarchy -->
                    <h6 class="text-uppercase text-xs fw-bold text-primary tracking-wider mb-3 pt-3 border-top">2. Job Assignment & Reporting Line</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Designation / Role</label>
                            <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                                <option value="">Select Designation</option>
                                @foreach($designations as $desig)
                                    <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                @endforeach
                            </select>
                            @error('designation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Reporting Manager</label>
                            <select name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                                <option value="">None (Top-Level / Self)</option>
                                @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}" {{ old('manager_id') == $mgr->id ? 'selected' : '' }}>{{ $mgr->full_name }} ({{ $mgr->designation->name ?? 'Staff' }})</option>
                                @endforeach
                            </select>
                            @error('manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Date of Joining</label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', date('Y-m-d')) }}">
                            @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Link CRM User Account</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">None (HR Employee Only)</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-xs">Allows this employee to log in to CRM and submit leave requests.</div>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Employee Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="Resigned" {{ old('status') === 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="Terminated" {{ old('status') === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.employees.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Create Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
