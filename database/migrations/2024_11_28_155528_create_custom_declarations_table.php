<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('custom_declarations', function (Blueprint $table) {
            $table->id();
            $table->string('declaration_number');
            $table->string('declaration_type', 50);
            $table->year('year');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['declaration_number', 'declaration_type', 'year'], 'unique_declaration_idx');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('custom_declarations');
    }
};
