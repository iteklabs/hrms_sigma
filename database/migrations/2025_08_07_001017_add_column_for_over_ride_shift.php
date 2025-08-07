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
            $table->date('date_to')->after('date');
            $table->boolean('is_uploading')->default(false);
            $table->string('cteated_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overide_shifts', function (Blueprint $table) {
            
        });
    }
};
