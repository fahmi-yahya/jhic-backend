<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bkk_lowongan', function (Blueprint $table) {
            $table->id();

            // Data perusahaan/penyedia lowongan — dipakai BK buat verifikasi
            // apakah perusahaannya beneran ada.
            $table->string('nama_perusahaan');
            $table->string('penanggung_jawab'); // nama kontak person
            $table->string('email');
            $table->string('no_telepon');
            $table->text('alamat_perusahaan')->nullable();
            $table->text('deskripsi_perusahaan')->nullable();

            // Data lowongan
            $table->string('posisi_dibutuhkan');
            $table->string('tipe_pekerjaan'); // Tetap/Kontrak/Paruh Waktu/Magang/Freelance
            $table->text('kualifikasi');
            $table->string('gaji')->nullable(); // string, bukan angka — biar bisa "Rp 3-4 juta" / "Negotiable"
            $table->date('batas_lamar')->nullable();

            // Status verifikasi BK. SENGAJA tidak pakai DB check constraint
            // (lihat migration 2026_09_30_000002) — cukup divalidasi di
            // BkkController pakai Rule::in(), biar nambah status baru nanti
            // nggak perlu migration lagi kayak kasus "jurusan" kemarin.
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('catatan_verifikasi')->nullable(); // catatan internal BK, opsional

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkk_lowongan');
    }
};
