<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('ktp_photo_path');
            $table->enum('resident_status', ['permanent', 'contract']);
            $table->string('phone_number', 20);
            $table->boolean('is_married')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('house_number', 30)->unique();
            $table->enum('occupancy_status', ['occupied', 'vacant'])->default('vacant');
            $table->timestamps();
        });

        Schema::create('house_resident_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->index(['house_id', 'ended_at']);
            $table->index(['resident_id', 'ended_at']);
        });

        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->unsignedBigInteger('amount');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('monthly_dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->restrictOnDelete();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('house_resident_history_id')->constrained()->restrictOnDelete();
            $table->foreignId('fee_type_id')->constrained()->restrictOnDelete();
            $table->date('billing_month');
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['house_id', 'resident_id', 'fee_type_id', 'billing_month'],
                'monthly_dues_house_resident_fee_month_unique'
            );
            $table->index(['billing_month', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('house_id')->constrained()->restrictOnDelete();
            $table->date('paid_at');
            $table->unsignedBigInteger('total_amount');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_due_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payment_details');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('monthly_dues');
        Schema::dropIfExists('fee_types');
        Schema::dropIfExists('house_resident_histories');
        Schema::dropIfExists('houses');
        Schema::dropIfExists('residents');
    }
};
