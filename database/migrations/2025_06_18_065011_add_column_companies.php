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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('total_hrs_per_day')->nullable();
            $table->string('regular_ot_percentage')->nullable();
            $table->string('legal_holiday_percentage')->nullable();
            $table->string('legal_holiday_ot_percentage')->nullable();
            $table->string('rest_day_percentage')->nullable();
            $table->string('rest_day_ot_percentage')->nullable();
            $table->string('special_holiday_percentage')->nullable();
            $table->string('special_holiday_ot_percentage')->nullable();
            $table->string('night_diff_percentage')->nullable();
            $table->string('night_diff_start_time')->nullable();
            $table->string('night_diff_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'total_hrs_per_day',
                'regular_ot_percentage',
                'legal_holiday_percentage',
                'legal_holiday_ot_percentage',
                'rest_day_percentage',
                'rest_day_ot_percentage',
                'special_holiday_percentage',
                'special_holiday_ot_percentage',
                'night_diff_percentage',
                'night_diff_start_time',
                'night_diff_end_time'
            ]);
        });
    }
};
