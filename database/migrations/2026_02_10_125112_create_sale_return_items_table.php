<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sale_return_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
      $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
      $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();

      $table->unsignedInteger('quantity'); // returned qty
      $table->decimal('unit_price', 12, 2);
      $table->decimal('line_refund', 12, 2);

      $table->timestamps();
      $table->index(['sale_return_id','sale_item_id']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('sale_return_items');
  }
};
