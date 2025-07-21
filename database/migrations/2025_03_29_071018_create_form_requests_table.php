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
        Schema::create('form_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_of_form_id')
                  ->constrained('type_of_forms')
                  ->onDelete('cascade');
            $table->longText("url_docx")->nullable();      
            $table->longText("file_docx")->nullable();      
            $table->string("status")->nullable();      
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_requests');
    }
};
