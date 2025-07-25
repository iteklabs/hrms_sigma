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
        Schema::create('attendance_carry_overs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('source_date'); // where the shift started (e.g. June 22)
            $table->date('apply_to_date'); // where the hours apply (e.g. June 23)
            $table->integer('minutes');
            $table->string('type'); // e.g. regular_ot, night_diff
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_carry_overs');
    }
};
