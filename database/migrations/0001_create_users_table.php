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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint(20) unsigned, Primärschlüssel
            $table->string('username')->index();    // varchar(255), Index
            $table->string('auth_type')->default('local'); // varchar(255)
			$table->foreignId('ad_sid')->nullable()->constrained('ad_users')->nullOnDelete();
            $table->string('firstname')->nullable(); // varchar(255)
            $table->string('lastname')->nullable();  // varchar(255)
            $table->string('email')->unique();       // varchar(255), Unique Index
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();  // varchar(255)
            $table->boolean('is_enabled')->nullable(); // tinyint(1)
            $table->longText('settings')->nullable();     // longtext
            $table->rememberToken();                      // varchar(100), nullable
            $table->timestamps();                         // created_at, updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
