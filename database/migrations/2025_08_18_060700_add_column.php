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
        Schema::table('loan_deduction', function (Blueprint $table) {
            $table->integer('no_deductions')->default(0)->after('type_of_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_deduction', function (Blueprint $table) {
            $table->dropColumn('no_deductions');
        });
    }
};
