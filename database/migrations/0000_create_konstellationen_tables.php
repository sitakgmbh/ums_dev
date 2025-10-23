<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Stammdaten --------------------------------------------------

        Schema::create('funktionen', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('unternehmenseinheiten', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('abteilungen', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('arbeitsorte', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('titel', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('anreden', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();
        });

        // --- Konstellationen --------------------------------------------------

        Schema::create('konstellationen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arbeitsort_id')->constrained('arbeitsorte')->cascadeOnDelete();
            $table->foreignId('unternehmenseinheit_id')->constrained('unternehmenseinheiten')->cascadeOnDelete();
            $table->foreignId('abteilung_id')->constrained('abteilungen')->cascadeOnDelete();
            $table->foreignId('funktion_id')->constrained('funktionen')->cascadeOnDelete();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamps();

            $table->unique(
                ['arbeitsort_id', 'unternehmenseinheit_id', 'abteilung_id', 'funktion_id'],
                'konstellationen_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konstellationen');
        Schema::dropIfExists('anreden');
        Schema::dropIfExists('titel');
        Schema::dropIfExists('arbeitsorte');
        Schema::dropIfExists('abteilungen');
        Schema::dropIfExists('unternehmenseinheiten');
        Schema::dropIfExists('funktionen');
    }
};
