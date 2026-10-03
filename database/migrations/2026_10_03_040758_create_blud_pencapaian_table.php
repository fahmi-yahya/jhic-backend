<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blud_pencapaian', function (Blueprint $table) {
            $table->id();
            $table->string('jurusan_terkait'); // mis. "RPL"
            $table->string('nama_mitra'); // mis. "UBIG", "Kominfo"
            $table->text('deskripsi');
            $table->date('tanggal_kerjasama')->nullable();
            $table->string('logo_mitra')->nullable(); // path storage, sama pola img_jurusan dkk.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blud_pencapaian');
    }
};
