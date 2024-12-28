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

        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        $table->foreign('declaration_id')->references('id')->on('custom_declarations')->onDelete('cascade');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('declaration_history');
    }
};
