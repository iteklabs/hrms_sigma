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
            //
            $table->string('cut_off')->nullable()->after('year');
            $table->double('sss_share_ee')->default(0);
            $table->double('sss_share_er')->default(0);
            $table->double('sss_mpf_ee')->default(0);
            $table->double('sss_mpf_er')->default(0);
            $table->double('sss_ec_er')->default(0);

            $table->double('pagibig_share_ee')->default(0);
            $table->double('pagibig_share_er')->default(0);

            $table->double('philhealth_share_ee')->default(0);
            $table->double('philhealth_share_er')->default(0);

            $table->double('taxable_income')->default(0);
            $table->double('tax_withheld')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            //
        });
    }
};
