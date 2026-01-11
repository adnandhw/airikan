<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all()->map(function ($banner) {
            $banner->image_url = asset('storage/' . $banner->image);
            return $banner;
        });

        return response()->json($banners);
    }
}
