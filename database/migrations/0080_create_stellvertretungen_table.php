<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("stellvertretungen", function (Blueprint $table) 
		{
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("ad_user_id")->constrained("ad_users")->onDelete("cascade");
            $table->timestamps();

            $table->unique(["user_id", "ad_user_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("stellvertretungen");
    }
};
