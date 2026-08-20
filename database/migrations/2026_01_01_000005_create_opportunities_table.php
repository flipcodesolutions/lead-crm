<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('opportunity_number')->unique();
            $table->string('name');
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->decimal('expected_revenue', 12, 2)->default(0.00);
            $table->integer('probability')->default(50);
            $table->date('expected_closing_date')->nullable();
            $table->foreignId('stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('Open'); // Open, Won, Lost
            $table->dateTime('won_at')->nullable();
            $table->dateTime('lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
