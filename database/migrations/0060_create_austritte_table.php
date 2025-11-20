<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("austritte", function (Blueprint $table) 
		{
            $table->id();
            $table->foreignId("owner_id")->nullable()->constrained("ad_users")->nullOnDelete();
            $table->date("vertragsende");
            $table->foreignId("ad_user_id")->nullable()->constrained("ad_users")->nullOnDelete();

            $table->boolean("status_pep")->default(1);
			$table->boolean("status_kis")->default(1);
			$table->boolean("status_streamline")->default(1);
			$table->boolean("status_tel")->default(1);
			$table->boolean("status_alarmierung")->default(1);
            $table->boolean("status_logimen")->default(1);

            $table->string("ticket_nr")->nullable();
            $table->boolean("archiviert")->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("austritte");
    }
};
