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
        Schema::create('rekening_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('norek_deposito');
            $table->char('nama_deposan')->nullable();
            $table->char('norek_tujuan')->nullable();
            $table->char('bank_tujuan')->nullable();
            $table->char('kode_bank')->nullable();
            $table->char('nama_rekening')->nullable();
            $table->char('nominal')->nullable();
            $table->char('tgl_bayar')->nullable();
            $table->char('status')->nullable();
            $table->string('currency')->nullable();
            $table->string('emailcorporate')->nullable();
            $table->string('ibuobu')->nullable();
            $table->string('remark1')->nullable();
            $table->string('remark2')->nullable();
            $table->string('remark3')->nullable();
            $table->string('adjust1')->nullable();
            $table->string('adjust2')->nullable();
            $table->string('adjust3')->nullable();
            $table->string('adjust4')->nullable();
            $table->string('adjust5')->nullable();
            $table->string('adjust6')->nullable();
            $table->string('adjust7')->nullable();
            $table->string('adjust8')->nullable();
            $table->string('adjust9')->nullable();
            $table->string('adjust10')->nullable();
            $table->string('adjust11')->nullable();
            $table->string('adjust12')->nullable();
            $table->string('adjust13')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekening_transfers');
    }
};
