<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
      $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
      $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();

      $table->string('barcode');
      $table->string('item_name');
      $table->decimal('unit_price', 12, 2);
      $table->unsignedInteger('quantity');
      $table->decimal('line_total', 12, 2);

      $table->timestamps();
      $table->index(['sale_id','batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_items');
    }
}
