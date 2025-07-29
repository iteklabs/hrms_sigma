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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->double('night_differential_amount')->default(0);
            $table->double('legal_holiday_ot_amount')->default(0);
            $table->double('legal_holiday_amount')->default(0);
            $table->double('rest_day_ot_amount')->default(0);
            $table->double('rest_day_amount')->default(0);
            $table->double('regular_ot_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('night_differential_amount');
            $table->dropColumn('legal_holiday_ot_amount');
            $table->dropColumn('legal_holiday_amount');
            $table->dropColumn('rest_day_ot_amount');
            $table->dropColumn('rest_day_amount');
            $table->dropColumn('regular_ot_amount');
        });
    }
};
