<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Menambahkan informasi pada tabel users bawaan Laravel.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'mahasiswa',
                'petugas',
                'admin',
            ])->default('mahasiswa');

            $table->string('nim', 30)->nullable()->unique();
            $table->boolean('is_active')->default(true);
        });

        // 2. Data ruangan.
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('location', 150);
            $table->unsignedInteger('capacity');
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->time('opening_time')->default('07:00:00');
            $table->time('closing_time')->default('21:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Jenis fasilitas.
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // 4. Penghubung ruang dengan fasilitas.
        Schema::create('room_facilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->foreignId('facility_id')
                ->constrained('facilities')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // Satu jenis fasilitas hanya dicatat sekali per ruang.
            $table->unique(['room_id', 'facility_id']);
        });

        // 5. Pengajuan peminjaman.
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 50)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->restrictOnDelete();

            $table->string('activity_name', 150);
            $table->text('purpose');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('participant_count');

            $table->enum('status', [
                'menunggu',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai',
            ])->default('menunggu');

            $table->timestamps();

            // Membantu pencarian jadwal ruang.
            $table->index(['room_id', 'booking_date', 'status']);
        });

        // 6. Riwayat perubahan status peminjaman.
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->restrictOnDelete();

            $table->foreignId('changed_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('previous_status', [
                'menunggu',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai',
            ])->nullable();

            $table->enum('new_status', [
                'menunggu',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai',
            ]);

            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // Hapus tabel anak sebelum tabel induk.
        Schema::dropIfExists('booking_histories');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('room_facilities');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('rooms');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim']);
            $table->dropColumn(['role', 'nim', 'is_active']);
        });
    }
};