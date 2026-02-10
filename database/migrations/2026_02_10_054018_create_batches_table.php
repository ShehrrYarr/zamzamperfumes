<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perfume_id')->constrained('perfumes')->cascadeOnDelete();

            // Which shop currently owns this batch stock
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // barcode must be unique globally (because you want same barcode to move to branch)
            $table->string('barcode')->unique();

            $table->string('batch_no')->nullable(); // supplier batch number (optional)

            $table->unsignedInteger('quantity')->default(0);

            // for later POS rules
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('sell_price', 12, 2)->nullable();

            $table->date('mfg_date')->nullable();
            $table->date('exp_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['shop_id', 'perfume_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batches');
    }
}
