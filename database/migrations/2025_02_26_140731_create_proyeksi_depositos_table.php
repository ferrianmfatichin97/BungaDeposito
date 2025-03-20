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
        Schema::create('proyeksi_depositos', function (Blueprint $table) {
            $table->id();
            $table->string('rek_deposito');
            $table->string('nama_nasabah');
            $table->integer('jangka_waktu');
            $table->decimal('nilai_bunga', 8, 2);
            $table->decimal('saldo_valuta_awal', 15, 2);
            $table->decimal('bunga', 15, 2);
            $table->decimal('total_bunga', 15, 2);
            $table->decimal('total_pajak', 15, 2);
            $table->decimal('total_bayar', 15, 2);
            $table->string('tujuan_penggunaan');
            $table->string('tanggal_bayar');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyeksi_depositos');
    }
};
