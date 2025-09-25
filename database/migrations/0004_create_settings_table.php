<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();         // z. B. "mail_host"
            $table->string('name');                  // Anzeigename
            $table->text('description')->nullable(); // Erklärung für UI
            $table->text('value')->nullable();       // gespeicherter Wert
            $table->string('type')->default('string'); // string, bool, int, float, json usw.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
