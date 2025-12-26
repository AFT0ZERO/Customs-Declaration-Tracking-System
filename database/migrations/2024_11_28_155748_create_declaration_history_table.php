<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('declaration_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('declaration_id');
            $table->text('action');
            $table->text('description')->default('لا يوجد');
            $table->timestamps();

            $table->foreign('user_id', 'declaration_history_user_id_foreign')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            $table->foreign('declaration_id', 'declaration_history_declaration_id_foreign')
                  ->references('id')
                  ->on('custom_declarations')
                  ->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('declaration_history');
    }
};
