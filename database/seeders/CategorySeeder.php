<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'Makanan & Minuman',
                'slug' => 'makanan-minuman',
                'deskripsi' => 'Cita rasa lokal dari para pelaku usaha kuliner Indonesia.',
            ],
            [
                'nama' => 'Fashion Lokal',
                'slug' => 'fashion-lokal',
                'deskripsi' => 'Busana dan aksesori dengan karakter lokal.',
            ],
            [
                'nama' => 'Kerajinan',
                'slug' => 'kerajinan',
                'deskripsi' => 'Karya kreatif yang dibuat dengan keterampilan dan ketelitian.',
            ],
            [
                'nama' => 'Kecantikan',
                'slug' => 'kecantikan',
                'deskripsi' => 'Pilihan perawatan dan produk kecantikan dari usaha lokal.',
            ],
            [
                'nama' => 'Rumah & Dekorasi',
                'slug' => 'rumah-dekorasi',
                'deskripsi' => 'Produk dekorasi dan perlengkapan untuk mempercantik rumah.',
            ],
            [
                'nama' => 'Aksesori',
                'slug' => 'aksesori',
                'deskripsi' => 'Aksesori pilihan untuk melengkapi gaya sehari-hari.',
            ],
            [
                'nama' => 'Pertanian',
                'slug' => 'pertanian',
                'deskripsi' => 'Hasil pertanian dan produk unggulan dari daerah.',
            ],
            [
                'nama' => 'Oleh-Oleh Daerah',
                'slug' => 'oleh-oleh-daerah',
                'deskripsi' => 'Produk khas daerah yang membawa cerita dari tempat asalnya.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'nama' => $category['nama'],
                    'deskripsi' => $category['deskripsi'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info(
            '8 kategori PasarLokal berhasil disiapkan.'
        );
    }
}
