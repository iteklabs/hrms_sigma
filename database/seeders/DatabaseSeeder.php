<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') != 'envato') {
            $this->call(LangTableSeeder::class);
            $this->call(CompanyTableSeeder::class);
            $this->call(CurrencyTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(UsersTableSeeder::class);
            $this->call(SettingTableSeeder::class);
            $this->call(LetterHeadSeeder::class);
            $this->call(CompanyPolicySeeder::class);
            $this->call(ExpenseSeeder::class);
            $this->call(DepositeSeeder::class);
            $this->call(HolidaySeeder::class);
            $this->call(SurveyFormsSeeder::class);
            $this->call(AssetSeeder::class);
            $this->call(AppreciationSeeder::class);
            $this->call(LeaveSeeder::class);
            $this->call(NewsSeeder::class);
            $this->call(PayrollSeeder::class);
        }
    }
}
