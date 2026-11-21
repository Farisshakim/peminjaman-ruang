<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->unsignedInteger('total_stock')->default(0);
        });

        // Pertahankan alokasi lama: stok awal disamakan
        // dengan jumlah yang sudah ditempatkan di ruangan.
        foreach (DB::table('facilities')->pluck('id') as $id) {
            $allocated = DB::table('room_facilities')
                ->where('facility_id', $id)
                ->sum('quantity');

            DB::table('facilities')
                ->where('id', $id)
                ->update(['total_stock' => $allocated]);
        }
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('total_stock');
        });
    }
};