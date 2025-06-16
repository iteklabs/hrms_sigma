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
        Schema::create('pagibig', function (Blueprint $table) {
            $table->id();
            $table->double('min_salary');
            $table->double('max_salary');
            $table->double('employer_share');
            $table->double('employee_share');
            $table->double('employer_share_percentage');
            $table->double('employee_share_percentage');
            $table->double('total_share_percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagibig');
    }
};
