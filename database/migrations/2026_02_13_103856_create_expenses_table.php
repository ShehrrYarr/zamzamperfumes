<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
             $table->id();
    $table->unsignedBigInteger('shop_id');
    $table->unsignedBigInteger('user_id')->nullable(); // who created
    $table->date('expense_date');
    $table->string('category')->nullable(); // optional
    $table->string('title');
    $table->text('notes')->nullable();
    $table->decimal('amount', 12, 2);
    $table->timestamps();

    $table->index(['shop_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
