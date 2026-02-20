<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalTransferFieldsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
              if (!Schema::hasColumn('sales', 'sale_type')) {
                $table->string('sale_type', 50)->default('customer')->index();
            }

            // branch shop that received transfer (for internal_transfer sales)
            if (!Schema::hasColumn('sales', 'related_shop_id')) {
                $table->unsignedBigInteger('related_shop_id')->nullable()->index();
            }

            // link to batch_transfers if you want
            if (!Schema::hasColumn('sales', 'transfer_id')) {
                $table->unsignedBigInteger('transfer_id')->nullable()->index();
            }

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
            if (!Schema::hasColumn('sales', 'sale_type')) {
                $table->string('sale_type', 50)->default('customer')->index();
            }

            // branch shop that received transfer (for internal_transfer sales)
            if (!Schema::hasColumn('sales', 'related_shop_id')) {
                $table->unsignedBigInteger('related_shop_id')->nullable()->index();
            }

            // link to batch_transfers if you want
            if (!Schema::hasColumn('sales', 'transfer_id')) {
                $table->unsignedBigInteger('transfer_id')->nullable()->index();
            }
        });
    }
}
