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
        Schema::table('overide_shifts', function (Blueprint $table) {
            $table->string('rest_day')->after('time_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overide_shifts', function (Blueprint $table) {
            $table->dropColumn('rest_day');
        });
    }
};
