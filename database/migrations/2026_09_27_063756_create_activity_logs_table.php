<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // FK ke users, tapi tetap nullOnDelete + simpan snapshot nama/role
            // supaya histori tetap kebaca walau akun pelakunya sudah dihapus.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('role')->nullable(); // snapshot role SAAT aksi dilakukan

            $table->string('action'); // created | updated | deleted
            $table->string('subject_type'); // mis. "Lingkungan", "User", "Berita"
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description'); // ringkasan human-readable, mis. "Menambahkan Lingkungan \"Taman Sekolah\""
            $table->json('changes')->nullable(); // { before: {...}, after: {...} } khusus untuk update

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('role');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
