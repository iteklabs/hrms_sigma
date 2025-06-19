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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('regular_ot')->nulllable();
            $table->string('legal_holiday')->nullable();
            $table->string('legal_holiday_ot')->nullable();
            $table->string('special_holiday')->nullable();
            $table->string('special_holiday_ot')->nullable();
            $table->string('rest_day')->nullable();
            $table->string('rest_day_ot')->nullable();
            $table->string('rest_day_special_holiday')->nullable();
            $table->string('rest_day_special_holiday_ot')->nullable();
            $table->string('night_differential')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
