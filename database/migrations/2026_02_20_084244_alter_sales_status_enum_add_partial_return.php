<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSalesStatusEnumAddPartialReturn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
             DB::statement("ALTER TABLE `sales` MODIFY `status` ENUM('completed','returned','partial_return') NOT NULL DEFAULT 'completed'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
             DB::statement("ALTER TABLE `sales` MODIFY `status` ENUM('completed','returned','partial_return') NOT NULL DEFAULT 'completed'");
        });
    }
}
