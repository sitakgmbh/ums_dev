<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("sap_export", function (Blueprint $table) 
		{
            $table->id();
            $table->string("d_pernr")->nullable();
            $table->string("d_anrlt")->nullable();
            $table->string("d_titel")->nullable();
            $table->string("d_name")->nullable();
            $table->string("d_vname")->nullable();
            $table->string("d_rufnm")->nullable();
            $table->string("d_gbdat")->nullable();
            $table->string("d_empct")->nullable();
            $table->string("d_bort")->nullable();
            $table->string("d_natio")->nullable();
            $table->string("d_arbortx")->nullable();
            $table->string("d_0032_batchbez")->nullable();
            $table->string("d_einri")->nullable();
            $table->string("d_ptext")->nullable();
            $table->string("d_email")->nullable();
            $table->string("d_pers_txt")->nullable();
            $table->string("d_abt_nr")->nullable();
            $table->string("d_abt_txt")->nullable();
            $table->string("d_0032_batchid")->nullable();
            $table->string("d_tel01")->nullable();
            $table->string("d_zzbereit")->nullable();
            $table->string("d_personid_ext")->nullable();
            $table->string("d_zzkader")->nullable();
            $table->string("d_adr1_name2")->nullable();
            $table->string("d_adr1_stras")->nullable();
            $table->string("d_adr1_pstlz")->nullable();
            $table->string("d_adr1_ort01")->nullable();
            $table->string("d_adr1_land1")->nullable();
            $table->string("d_adr1_telnr")->nullable();
            $table->string("d_adr5_name2")->nullable();
            $table->string("d_adr5_stras")->nullable();
            $table->string("d_adr5_pstlz")->nullable();
            $table->string("d_adr5_ort01")->nullable();
            $table->string("d_adr5_land1")->nullable();
            $table->string("d_email_privat")->nullable();
            $table->string("d_nebenamt")->nullable();
            $table->string("d_nebenbesch")->nullable();
            $table->string("d_einda")->nullable();
            $table->string("d_endda")->nullable();
            $table->string("d_fmht1")->nullable();
            $table->string("d_fmht1zus")->nullable();
            $table->string("d_fmht2")->nullable();
            $table->string("d_fmht2zus")->nullable();
            $table->string("d_fmht3")->nullable();
            $table->string("d_fmht3zus")->nullable();
            $table->string("d_fmht4")->nullable();
            $table->string("d_fmht4zus")->nullable();
            $table->string("d_kbcod")->nullable();
            $table->string("d_leader")->nullable();
            $table->foreignId("ad_user_id")->nullable()->constrained("ad_users")->nullOnDelete();
            $table->boolean("alarm_enabled")->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sap_exports");
    }
};