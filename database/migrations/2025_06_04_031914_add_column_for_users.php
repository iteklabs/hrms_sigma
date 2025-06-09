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
        Schema::table('users', function (Blueprint $table) {
            $table->double('semi_monthly_rate', 8, 4)->nullable();
            $table->double('hourly_rate', 8, 4)->nullable();
            $table->double('daily_rate', 8, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['semi_monthly', 'hourly', 'daily']);
        });
    }
};
