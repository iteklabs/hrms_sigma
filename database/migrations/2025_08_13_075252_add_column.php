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
        Schema::table('salary_adjustment', function (Blueprint $table) {
            $table->string('start_cut_off_specific')->nullable();
            $table->string('start_month_specific')->nullable();
            $table->string('start_year_specific')->nullable();

            $table->string('end_cut_off_specific')->nullable();
            $table->string('end_month_specific')->nullable();
            $table->string('end_year_specific')->nullable();

            $table->string('cut_off_specific')->nullable();
            $table->string('month_specific')->nullable();
            $table->string('year_specific')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_adjustment', function (Blueprint $table) {
            $table->dropColumn([
                'start_cut_off_specific',
                'start_month_specific',
                'start_year_specific',
                'end_cut_off_specific',
                'end_month_specific',
                'end_year_specific',
                'cut_off_specific',
                'month_specific',
                'year_specific'
            ]); 
        });
    }
};
