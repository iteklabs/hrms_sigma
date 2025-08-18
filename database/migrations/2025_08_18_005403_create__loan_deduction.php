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
        Schema::create('loan_deduction', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->unique();
            $table->string('loan_name')->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('location_id')->index();
            $table->string('type_of_loan');
            $table->double('total_amount_loan', 15, 4)->default(0);
            $table->double('amount_per_payroll', 15, 4)->default(0);
            $table->double('payroll_deduction', 15, 4)->default(0);
            $table->string('type_of_deduction')->default('monthly');
            $table->string('sched_of_deduction')->default('A');
            $table->string('start_year_specific')->nullable();
            $table->string('start_month_specific')->nullable();
            $table->string('start_batch_specific')->nullable();
            $table->boolean('start_pause')->default(false);
            $table->string('end_year_specific')->nullable();
            $table->string('end_month_specific')->nullable();
            $table->string('end_batch_specific')->nullable();
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_deduction');
    }
};
