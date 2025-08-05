<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_students', function (Blueprint $table) {
            $table->id();

            $table->timestamps();
            $table->foreignId('folder_id')
                ->constrained('folders')
                ->onDelete('cascade');
            $table->string('student_code');
            $table->string('status');

            $table->foreign('student_code')
                ->references('student_code')
                ->on('students')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_students');
    }
};
