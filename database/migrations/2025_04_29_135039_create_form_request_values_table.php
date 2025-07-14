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
        Schema::create('form_request_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_id')
                ->constrained('form_requests')
                ->onDelete('cascade');

            $table->foreignId('field_form_id')
                ->constrained('field_forms')
                ->onDelete('cascade');


            $table->string('student_code');
            // Nếu muốn tạo liên kết thủ công (không bắt buộc nhưng tốt để đảm bảo toàn vẹn)
            $table->foreign('student_code')
                ->references('student_code')
                ->on('students')
                ->onDelete('cascade');

            $table->json('value')->nullable(); // Dữ liệu nhập từ người dùng
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_request_values');
    }
};
