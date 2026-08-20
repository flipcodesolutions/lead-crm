<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Departments Table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
        });

        // 2. Designations Table
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
        });

        // 3. Employees Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->unique();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('joining_date')->nullable();
            $table->text('address')->nullable();
            $table->string('status', 30)->default('Active')->comment('Active, Inactive, Resigned, Terminated');
            $table->timestamps();
        });

        // 4. Employee Salaries Table
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('hra', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('other_earnings', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('pf_deduction', 15, 2)->default(0);
            $table->decimal('other_deduction', 15, 2)->default(0);
            $table->decimal('annual_salary', 15, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
        });

        // 5. Leave Types Table
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('total_days')->default(12);
            $table->boolean('is_paid')->default(true);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
        });

        // 6. Leave Allocations Table
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('allocated_days', 8, 2)->default(0);
            $table->decimal('used_days', 8, 2)->default(0);
            $table->decimal('remaining_days', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });

        // 7. Leave Requests Table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('total_days', 8, 2);
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Rejected, Cancelled');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();
        });

        // 8. Tax Slabs Table
        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('min_income', 15, 2)->default(0);
            $table->decimal('max_income', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Percentage rate, e.g. 5, 10, 20');
            $table->decimal('fixed_tax', 15, 2)->default(0);
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
        });

        // 9. Employee Taxes Table
        Schema::create('employee_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('financial_year', 20)->comment('e.g. 2026-2027');
            $table->decimal('annual_income', 15, 2)->default(0);
            $table->decimal('taxable_income', 15, 2)->default(0);
            $table->decimal('calculated_tax', 15, 2)->default(0);
            $table->decimal('paid_tax', 15, 2)->default(0);
            $table->decimal('remaining_tax', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'financial_year'], 'emp_tax_fy_unique');
        });

        // 10. Payrolls Table
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('hra', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('other_earnings', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('pf_deduction', 15, 2)->default(0);
            $table->decimal('other_deduction', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->string('status', 30)->default('Generated')->comment('Draft, Generated, Paid, Cancelled');
            $table->dateTime('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year'], 'emp_payroll_month_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('employee_taxes');
        Schema::dropIfExists('tax_slabs');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_allocations');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
