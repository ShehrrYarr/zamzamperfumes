<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
           $table->id();
      $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
      $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();

      $table->enum('method', ['counter','bank']);
      $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();

      $table->decimal('amount', 12, 2);
      $table->timestamp('paid_at')->nullable();

      $table->timestamps();
      $table->index(['shop_id','method','paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
