<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposito_reminders', function (Blueprint $table) {
            $table->id();
            $table->char('kode_cabang', 2)->index()->comment('Kode cabang sesuai data_kantor_master.kantor_kode');
            $table->string('email_tujuan', 150)->nullable()->comment('Alamat email tujuan reminder');
            $table->string('wa_tujuan', 20)->nullable()->comment('Nomor WA tujuan reminder (format internasional)');
            $table->unsignedInteger('hari_sebelum_jt')->default(7)->comment('Berapa hari sebelum jatuh tempo dikirim reminder');
            $table->boolean('aktif')->default(true)->comment('1 = aktif, 0 = nonaktif');
            $table->text('message_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposito_reminders');
    }
};
