<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
             // Only staff will use these, but safe to keep on users table
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('role');
            $table->decimal('daily_salary', 12, 2)->nullable()->after('monthly_salary');
            $table->decimal('hourly_salary', 12, 2)->nullable()->after('daily_salary');

            $table->unsignedInteger('work_hours_per_day')->default(10)->after('hourly_salary'); // default 10 hours
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
              $table->dropColumn(['monthly_salary','daily_salary','hourly_salary','work_hours_per_day']);
        });
    }
}
