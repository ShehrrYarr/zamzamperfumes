<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
             $table->id();

            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // staff user

            $table->date('work_date'); // one record per day

            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();

            $table->unsignedInteger('worked_minutes')->default(0);

            // salary snapshot for that day (so later changes won't affect past records)
            $table->decimal('daily_salary_snapshot', 12, 2)->default(0);
            $table->decimal('hourly_salary_snapshot', 12, 2)->default(0);

            // earned amount for that day
            $table->decimal('earned_amount', 12, 2)->default(0);

            // status: present/partial/absent (absent when no checkout by end-of-day)
            $table->enum('status', ['present','partial','absent'])->default('partial');

            $table->timestamps();

            $table->unique(['user_id','work_date']); // one attendance per staff per day
            $table->index(['shop_id','work_date']);
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
