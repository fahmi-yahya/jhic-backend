<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kisah_alumnis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('jurusan', 20);
            $table->unsignedSmallInteger('tahun_lulus');
            $table->string('jabatan', 150);
            $table->text('kisah');
            $table->string('foto')->nullable();
            // pending (menunggu admin) | approved (tampil di landing BKK) | rejected
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kisah_alumnis');
    }
};
