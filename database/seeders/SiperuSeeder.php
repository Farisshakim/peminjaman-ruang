<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiperuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Akun contoh untuk pengembangan lokal.
            $accounts = [
                [
                    'name' => 'Admin SIPERU',
                    'email' => 'admin@siperu.test',
                    'role' => 'admin',
                    'nim' => null,
                ],
                [
                    'name' => 'Petugas SIPERU',
                    'email' => 'petugas@siperu.test',
                    'role' => 'petugas',
                    'nim' => null,
                ],
                [
                    'name' => 'Mahasiswa Demo',
                    'email' => 'mahasiswa@siperu.test',
                    'role' => 'mahasiswa',
                    'nim' => '20260001',
                ],
            ];

            foreach ($accounts as $account) {
                User::firstOrCreate(
                    ['email' => $account['email']],
                    [
                        'name' => $account['name'],
                        'role' => $account['role'],
                        'nim' => $account['nim'],
                        'password' => Hash::make('Siperu123!'),
                        'is_active' => true,
                    ]
                );
            }

            // Jenis fasilitas.
            $facilityIds = [];

            foreach (['Proyektor', 'AC', 'Papan Tulis', 'Kursi'] as $name) {
                $facility = Facility::firstOrCreate([
                    'name' => $name,
                ]);

                $facilityIds[$name] = $facility->id;
            }

            // Ruang beserta jumlah fasilitas.
            $rooms = [
                [
                    'name' => 'Ruang Rapat A',
                    'location' => 'Gedung A, Lantai 1',
                    'capacity' => 20,
                    'description' => 'Ruang untuk rapat dan diskusi kelompok.',
                    'facilities' => [
                        'Proyektor' => 1,
                        'AC' => 1,
                        'Papan Tulis' => 1,
                        'Kursi' => 20,
                    ],
                ],
                [
                    'name' => 'Ruang Seminar',
                    'location' => 'Gedung B, Lantai 2',
                    'capacity' => 100,
                    'description' => 'Ruang untuk seminar dan presentasi.',
                    'facilities' => [
                        'Proyektor' => 1,
                        'AC' => 4,
                        'Papan Tulis' => 1,
                        'Kursi' => 100,
                    ],
                ],
                [
                    'name' => 'Ruang Diskusi',
                    'location' => 'Gedung A, Lantai 2',
                    'capacity' => 10,
                    'description' => 'Ruang untuk diskusi kelompok kecil.',
                    'facilities' => [
                        'AC' => 1,
                        'Papan Tulis' => 1,
                        'Kursi' => 10,
                    ],
                ],
            ];

            foreach ($rooms as $data) {
                $room = Room::firstOrCreate(
                    [
                        'name' => $data['name'],
                        'location' => $data['location'],
                    ],
                    [
                        'capacity' => $data['capacity'],
                        'description' => $data['description'],
                        'opening_time' => '07:00:00',
                        'closing_time' => '21:00:00',
                        'is_active' => true,
                    ]
                );

                // Fasilitas contoh hanya dipasang ketika ruang baru dibuat.
                if ($room->wasRecentlyCreated) {
                    $pivotData = [];

                    foreach ($data['facilities'] as $name => $quantity) {
                        $pivotData[$facilityIds[$name]] = [
                            'quantity' => $quantity,
                        ];
                    }

                    $room->facilities()->attach($pivotData);
                }
            }
        });
    }
}