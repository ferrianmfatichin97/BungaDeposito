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
        Schema::create('payroll_depositos', function (Blueprint $table) {
            $table->id();
            $table->string('norek_deposito')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->string('norek_tujuan')->nullable();
            $table->string('kode_bank')->nullable();
            $table->string('bank_tujuan')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->decimal('nominal',20,0)->nullable();
            $table->string('jatuh_tempo')->nullable();
            $table->string('status')->nullable();
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
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_depositos');
    }
};
