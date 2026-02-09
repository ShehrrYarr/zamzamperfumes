<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchIdToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
           $table->foreignId('batch_id')
                  ->nullable()
                  ->after('vendor_id')
                  ->constrained('accessory_batches')
                  ->cascadeOnDelete(); // when a batch is deleted, related account rows go too

            $table->index(['vendor_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
              $table->dropConstrainedForeignId('batch_id');
            $table->dropIndex(['vendor_id', 'batch_id']);
        });
    }
}
