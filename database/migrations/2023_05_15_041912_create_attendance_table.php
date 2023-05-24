<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->timestamp('sys_in');
            $table->timestamp('sys_out');
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->double('delay')->default(0);
            $table->boolean('sus')->default(false);
            $table->string('ip')->nullable();
            $table->unsignedBigInteger('attachment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
