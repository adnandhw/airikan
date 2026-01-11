<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::create(['image' => 'BannerPromotion.jpg']);
        Banner::create(['image' => 'banner2.jpg']);
        Banner::create(['image' => 'banner3.jpg']);
    }
}
