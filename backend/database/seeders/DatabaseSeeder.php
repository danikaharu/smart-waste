<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartwaste.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'phone' => '081299887766',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. Create Regular Users
        $citizen1 = User::firstOrCreate(
            ['email' => 'user@smartwaste.com'],
            [
                'name' => 'Warga Peduli Lingkungan',
                'password' => Hash::make('password'),
                'phone' => '081311223344',
                'role' => 'user',
                'is_active' => true,
            ]
        );

        $citizen2 = User::firstOrCreate(
            ['email' => 'budi@smartwaste.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'phone' => '085712345678',
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // 3. Create Sample Reports
        $sampleReports = [
            [
                'user_id' => $citizen1->id,
                'title' => 'Tumpukan Sampah Botol Plastik di Pinggir Selokan',
                'description' => 'Ditemukan banyak botol air mineral dan kemasan plastik sekali pakai menyumbat saluran air.',
                'image_path' => 'reports/plastic_sample.jpg',
                'latitude' => -6.2087634,
                'longitude' => 106.8455990,
                'address_note' => 'Jl. Sudirman Dekat Halte Dukuh Atas, Jakarta Pusat',
                'waste_category' => 'Plastic Waste',
                'ai_confidence' => 0.9854,
                'status' => 'pending',
                'admin_notes' => null,
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $citizen2->id,
                'title' => 'Limbah Sayuran dan Sisa Makanan Pasar',
                'description' => 'Sisa sayur kol, kulit buah, dan daun pisang berserakan di belakang pasar, menimbulkan bau tak sedap.',
                'image_path' => 'reports/organic_sample.jpg',
                'latitude' => -6.1753924,
                'longitude' => 106.8271528,
                'address_note' => 'Area Parkir Timur Pasar Baru, Jakarta Pusat',
                'waste_category' => 'Organic Waste',
                'ai_confidence' => 0.9930,
                'status' => 'in_progress',
                'admin_notes' => 'Petugas kebersihan sektor pasar sedang diarahkan untuk pengangkutan.',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $citizen1->id,
                'title' => 'Tumpukan Kardus dan Kertas Bekas Paket',
                'description' => 'Kardus bekas kemasan dan kertas koran basah menumpuk di dekat trotoar pejalan kaki.',
                'image_path' => 'reports/paper_sample.jpg',
                'latitude' => -6.2255880,
                'longitude' => 106.8003940,
                'address_note' => 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
                'waste_category' => 'Paper Waste',
                'ai_confidence' => 0.9841,
                'status' => 'completed',
                'admin_notes' => 'Telah diangkut oleh truk kebersihan DLH pada pukul 09:30 WIB.',
                'resolved_at' => now()->subHours(5),
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $citizen2->id,
                'title' => 'Gelas Plastik Berserakan di Taman Kota',
                'description' => 'Banyak gelas plastik sisa minuman kekinian dibuang sembarangan di area bangku taman.',
                'image_path' => 'reports/plastic_sample.jpg',
                'latitude' => -6.2415860,
                'longitude' => 106.8521990,
                'address_note' => 'Taman Tebet Eco Park, Jakarta Selatan',
                'waste_category' => 'Plastic Waste',
                'ai_confidence' => 0.9412,
                'status' => 'pending',
                'admin_notes' => null,
                'created_at' => now()->subHours(8),
            ],
            [
                'user_id' => $citizen1->id,
                'title' => 'Dedaunan Kering dan Ranting Pohon',
                'description' => 'Sampah organik dedaunan rontok menumpuk di depan pagar sekolah.',
                'image_path' => 'reports/organic_sample.jpg',
                'latitude' => -6.1683290,
                'longitude' => 106.9023400,
                'address_note' => 'Jl. Boulevard Raya, Kelapa Gading, Jakarta Utara',
                'waste_category' => 'Organic Waste',
                'ai_confidence' => 0.9678,
                'status' => 'in_progress',
                'admin_notes' => 'Tim PPSU Kelurahan Kelapa Gading sedang menyapu dan mengumpulkan ke karung sampah.',
                'created_at' => now()->subHours(12),
            ],
            [
                'user_id' => $citizen2->id,
                'title' => 'Karton Kardus di Gang Pemukiman',
                'description' => 'Kardus besar menghalangi akses jalan motor warga di dalam gang.',
                'image_path' => 'reports/paper_sample.jpg',
                'latitude' => -6.1855000,
                'longitude' => 106.8350000,
                'address_note' => 'Gang Melati RW 04, Cikini, Jakarta Pusat',
                'waste_category' => 'Paper Waste',
                'ai_confidence' => 0.9230,
                'status' => 'completed',
                'admin_notes' => 'Warga bersama petugas sudah membersihkan area tersebut.',
                'resolved_at' => now()->subDays(1),
                'created_at' => now()->subDays(4),
            ],
            [
                'user_id' => $citizen1->id,
                'title' => 'Laporan Palsu / Lokasi Tidak Ada Sampah',
                'description' => 'Foto tidak relevan dan lokasi setelah dicek tim lapangan ternyata bersih.',
                'image_path' => 'reports/plastic_sample.jpg',
                'latitude' => -6.1950000,
                'longitude' => 106.8150000,
                'address_note' => 'Jl. Thamrin Depan Sarinah',
                'waste_category' => 'Plastic Waste',
                'ai_confidence' => 0.8120,
                'status' => 'rejected',
                'admin_notes' => 'Laporan ditolak: Lokasi sudah bersih dan tidak ditemukan tumpukan seperti pada laporan.',
                'created_at' => now()->subDays(5),
            ]
        ];

        foreach ($sampleReports as $data) {
            Report::create($data);
        }
    }
}
