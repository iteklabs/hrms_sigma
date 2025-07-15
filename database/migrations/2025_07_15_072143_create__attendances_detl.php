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
        Schema::create('attendances_detl', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('attendance_id');
            $table->boolean('is_late')->default(false);
            $table->boolean('is_undertime')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_weekend')->default(false);
            $table->boolean('is_leave')->default(false);
            $table->boolean('is_overtime')->default(false);
            $table->boolean('is_present')->default(false);
            $table->string('date');
            $table->double('no_of_hrs');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances_detl');
    }
};
