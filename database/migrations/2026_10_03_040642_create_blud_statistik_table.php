<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    // Singleton — cuma ada 1 baris (id=1) yang terus di-update, bukan
    // daftar banyak baris. Sesuai tampilan di landing page: satu set
    // angka statistik yang tampil sekaligus (Jumlah Client, Siswa
    // Terlibat, Produk & Jasa, Jurusan, Project).
    public function up(): void
    {
        Schema::create('blud_statistik', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('jumlah_client')->default(0);
            $table->unsignedInteger('siswa_terlibat')->default(0);
            $table->unsignedInteger('produk_jasa')->default(0);
            $table->unsignedInteger('jurusan_terlibat')->default(0);
            $table->unsignedInteger('project')->default(0);
            $table->timestamps();
        });

        // Seed satu baris default supaya GET /api/statistik tidak perlu
        // menangani kasus "belum ada data" di controller.
        DB::table('blud_statistik')->insert([
            'jumlah_client' => 0,
            'siswa_terlibat' => 0,
            'produk_jasa' => 0,
            'jurusan_terlibat' => 0,
            'project' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blud_statistik');
    }
};
