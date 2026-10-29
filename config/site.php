<?php

// Format: 'key' => ['Label', 'tipe', 'nilai default']
// Tipe: text, textarea, number, toggle
return [
    'groups' => [

        'Identitas & Kontak' => [
            'brand'      => ['Nama brand', 'text', 'Clean Shoes Jejak Suci'],
            'wa_number'  => ['Nomor WhatsApp (format 62..., tanpa + atau spasi)', 'text', '6281234567890'],
            'instagram'  => ['Username Instagram (tanpa @)', 'text', 'cleanshoesjejaksuci'],
            'open_hours' => ['Jam operasional', 'text', 'Setiap hari, 09.00 - 20.00 WIB'],
        ],

        'Banner Promo (bar merah paling atas)' => [
            'promo_active' => ['Tampilkan banner promo', 'toggle', '0'],
            'promo_text'   => ['Teks promo', 'text', '🔥 Promo spesial bulan ini, tanya admin kami!'],
        ],

        'Hero (bagian utama)' => [
            'hero_badge'     => ['Teks badge kecil', 'text', 'Buka di Jonggol & Cikarang'],
            'hero_title_1'   => ['Judul baris 1 (putih)', 'text', 'Sepatu, Tas & Helm'],
            'hero_title_2'   => ['Judul baris 2 (merah)', 'text', 'Bersih Seperti Baru.'],
            'hero_subtitle'  => ['Deskripsi singkat', 'textarea', 'Perawatan profesional dengan bahan aman, proses terpantau lewat WhatsApp, dan diskon eksklusif untuk member setia.'],
            'stat_customers' => ['Statistik: jumlah pelanggan', 'number', 1500],
            'stat_items'     => ['Statistik: jumlah item dicuci', 'number', 5000],
        ],

        'Judul Section' => [
            'services_title'    => ['Layanan: judul', 'text', 'Perawatan Tepat untuk Setiap Barang'],
            'services_subtitle' => ['Layanan: sub judul', 'text', 'Pilih kategori untuk melihat treatment dan harga terbaru.'],
            'member_title'      => ['Membership: judul', 'text', 'Makin Sering Cuci, Makin Untung'],
            'member_subtitle'   => ['Membership: sub judul', 'text', 'Daftar gratis. Tier naik otomatis berdasarkan total belanja Anda.'],
            'contact_title'     => ['Kontak: judul', 'text', 'Ada Pertanyaan? Kami Siap Membantu.'],
            'contact_text'      => ['Kontak: deskripsi', 'textarea', 'Kirim pertanyaan lewat formulir, admin kami membalas via WhatsApp. Atau langsung chat jika butuh cepat.'],
        ],

        'SEO & Footer' => [
            'meta_description' => ['Deskripsi Google (meta description)', 'textarea', 'Jasa cuci sepatu, tas, dan helm profesional di Jonggol dan Cikarang. Hasil bersih seperti baru dan update status via WhatsApp.'],
            'footer_text'      => ['Teks footer', 'text', 'Clean Shoes Jejak Suci · Jonggol & Cikarang'],
        ],
    ],
];