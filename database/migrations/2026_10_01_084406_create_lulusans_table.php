<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lulusans', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 30)->nullable()->index();
            $table->string('nama');
            $table->unsignedSmallInteger('tahun_lulus')->index();
            $table->string('komp_keahlian')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();


            $table->string('status_aktifitas')->nullable();
            $table->string('keterangan')->nullable();

            $table->string('jabatan_bekerja')->nullable();
            $table->string('nama_tempat_kerja')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->string('nama_perguruan_tinggi')->nullable();
            $table->string('nama_prodi')->nullable();

            $table->timestamps();
            $table->unique(['nisn', 'tahun_lulus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lulusans');
    }
};
