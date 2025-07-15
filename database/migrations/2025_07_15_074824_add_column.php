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
            $table->double('no_of_hrs_late')->nullable()->after('date_out');
            $table->double('no_of_hrs_undertime')->nullable()->after('date_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('no_of_hrs_late');
            $table->dropColumn('no_of_hrs_undertime');
        });
    }
};
