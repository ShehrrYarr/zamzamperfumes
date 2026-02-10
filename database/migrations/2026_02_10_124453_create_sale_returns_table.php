<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sale_returns', function (Blueprint $table) {
      $table->id();
      $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
      $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

      $table->decimal('refund_amount', 12, 2)->default(0);
      $table->enum('method', ['counter','bank'])->default('counter');
      $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();

      $table->timestamps();
      $table->index(['shop_id','sale_id','created_at']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('sale_returns');
  }
};
