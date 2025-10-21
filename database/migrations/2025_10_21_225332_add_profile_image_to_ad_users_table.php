<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('ad_users', function (Blueprint $table) {
        $table->longText('profile_image_base64')->nullable()->after('last_synced_at');
    });
}

public function down(): void
{
    Schema::table('ad_users', function (Blueprint $table) {
        $table->dropColumn('profile_image_base64');
    });
}

};
