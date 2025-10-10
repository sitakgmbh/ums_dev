<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();      // eigene Kategorie, frei wählbar
            $table->string('level');                     // INFO, ERROR, WARNING
            $table->text('message');                     // eigentliche Log-Nachricht
            $table->json('context')->nullable();         // optionale Zusatzdaten
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
