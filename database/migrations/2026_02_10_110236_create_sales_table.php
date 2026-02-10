<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
      $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

      $table->string('customer_name')->nullable();
      $table->string('customer_phone')->nullable();

      $table->decimal('subtotal', 12, 2)->default(0);
      $table->enum('discount_type', ['none','flat','percent'])->default('none');
      $table->decimal('discount_value', 12, 2)->default(0);
      $table->decimal('discount_amount', 12, 2)->default(0);
      $table->decimal('grand_total', 12, 2)->default(0);

      $table->enum('status', ['completed','returned'])->default('completed'); // returns later
      $table->timestamps();

      $table->index(['shop_id','created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
