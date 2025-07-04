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
        Schema::create('salary_adjustment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('process_payment');
            $table->string('cut_off')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->double('amount')->default(0);
            $table->string('type')->default('NT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_adjustment');
    }
};
