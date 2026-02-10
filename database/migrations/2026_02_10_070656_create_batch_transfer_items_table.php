<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batch_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('batch_transfer_id')->constrained('batch_transfers')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();

            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->unique(['batch_transfer_id','batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_transfer_items');
    }
};
