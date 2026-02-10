<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batch_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_shop_id')->constrained('shops')->cascadeOnDelete(); // main
            $table->foreignId('to_shop_id')->constrained('shops')->cascadeOnDelete();   // branch

            $table->string('code')->unique(); // secret
            $table->enum('status', ['pending', 'claimed', 'cancelled'])->default('pending');

            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->index(['from_shop_id','to_shop_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_transfers');
    }
};
