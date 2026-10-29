<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cabang
        Branch::create(['name' => 'Clean Shoes Jejak Suci Jonggol (Pusat)', 'address' => 'Jl. Citra Indah, Jonggol', 'is_main' => true]);
        Branch::create(['name' => 'Clean Shoes Jejak Suci Cikarang', 'address' => 'Cikarang']);

        // User
        foreach ([
            ['Owner Jejak Suci', 'owner@jejaksuci.com', 'owner', '08111111111'],
            ['Admin Jejak Suci', 'admin@jejaksuci.com', 'admin', '08122222222'],
            ['Kasir Jejak Suci', 'kasir@jejaksuci.com', 'kasir', '08133333333'],
        ] as [$name, $email, $role, $phone]) {
            User::create([
                'name' => $name, 'email' => $email, 'password' => Hash::make('password'),
                'role' => $role, 'phone' => $phone, 'is_active' => true, 'email_verified_at' => now(),
            ]);
        }

        // Layanan
        foreach ([
            ['Depp Clean', 'sepatu', 35000, 2],
            ['White Clean', 'sepatu', 40000, 3],
            ['Unyellowing',   'sepatu', 40000, 4],
            ['Reguler Half Face',          'helm',    40000, 3],
            ['Reguler Full Face',         'helm',   45000, 2],
        ] as [$n, $c, $p, $d]) {
            Service::create(['name' => $n, 'category' => $c, 'price' => $p, 'duration_days' => $d]);
        }

        // Keunggulan
        foreach ([
            ['🧼', 'Bahan Aman', 'Cairan khusus sesuai material, tidak merusak warna dan lem.'],
            ['📲', 'Update via WhatsApp', 'Status cucian dikabari di setiap tahap proses.'],
            ['🔎', 'Cek Pesanan Online', 'Pantau cucian kapan saja dengan nomor invoice.'],
            ['🏅', 'Poin & Diskon Member', 'Makin sering cuci, makin hemat.'],
        ] as $i => [$icon, $title, $desc]) {
            Feature::create(['icon' => $icon, 'title' => $title, 'description' => $desc, 'sort_order' => $i + 1]);
        }

        // FAQ
        foreach ([
            ['Berapa lama proses pencucian?', 'Tergantung treatment yang dipilih, umumnya 2 sampai 4 hari kerja. Estimasi pasti tertera di nota dan bisa dicek lewat menu Cek Pesanan.'],
            ['Apakah sepatu yang sudah menguning bisa kembali putih?', 'Bisa. Treatment Unyellowing Sol khusus untuk sol dan midsole yang menguning. Hasil bergantung pada tingkat oksidasi dan material.'],
            ['Bagaimana cara tahu cucian saya sudah selesai?', 'Admin mengirim kabar lewat WhatsApp di setiap tahap. Anda juga bisa cek sendiri dengan nomor invoice dan nomor HP.'],
            ['Apakah ada garansi jika hasil kurang bersih?', 'Hubungi kami maksimal 1x24 jam setelah pengambilan. Kami akan bantu proses ulang sesuai kondisi barang.'],
            ['Bagaimana cara mendapat diskon member?', 'Daftar gratis lewat website. Setiap transaksi lunas menambah total belanja, lalu tier naik otomatis ke Silver dan Gold.'],
            ['Metode pembayaran apa saja yang diterima?', 'Tunai, transfer bank, dan QRIS. Pembayaran dicatat oleh kasir dan tampil di riwayat pesanan Anda.'],
        ] as $i => [$q, $a]) {
            Faq::create(['question' => $q, 'answer' => $a, 'sort_order' => $i + 1]);
        }
    }
}