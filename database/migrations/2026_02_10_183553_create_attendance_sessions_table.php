<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
             $table->id();

            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();

            $table->dateTime('check_in_at');
            $table->dateTime('check_out_at')->nullable();

            $table->unsignedInteger('worked_minutes')->default(0);

            $table->timestamps();

            $table->index(['attendance_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_sessions');
    }
}
