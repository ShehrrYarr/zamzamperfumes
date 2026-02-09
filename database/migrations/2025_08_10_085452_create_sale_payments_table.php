<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();

            // 'counter' or 'bank'
            $table->enum('method', ['counter', 'bank'])->index();

            // required only when method = bank
            $table->foreignId('bank_id')
                  ->nullable()
                  ->constrained('banks')
                  ->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('reference_no')->nullable(); // slip/txn id
            $table->text('notes')->nullable();

            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();

            $table->index(['sale_id', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_payments');
    }
}
