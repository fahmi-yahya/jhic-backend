<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blud_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->string('kategori'); // "Produk" | "Jasa" — divalidasi di controller, bukan DB check
            $table->text('deskripsi');
            $table->string('harga')->nullable(); // string biar fleksibel, "Rp 150.000" / "Mulai Rp 500rb" / "Hubungi kami"
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blud_produk');
    }
};
