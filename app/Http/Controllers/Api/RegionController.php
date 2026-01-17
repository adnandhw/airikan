<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class RegionController extends Controller
{
    public function provinces()
    {
        return response()->json(Province::select('code as id', 'name')->orderBy('name')->get());
    }

    public function cities(Request $request)
    {
        $provinceCode = $request->query('province_code');
        return response()->json(City::where('province_code', $provinceCode)->select('code as id', 'name')->orderBy('name')->get());
    }

    public function districts(Request $request)
    {
        $cityCode = $request->query('city_code');
        return response()->json(District::where('city_code', $cityCode)->select('code as id', 'name')->orderBy('name')->get());
    }

    public function villages(Request $request)
    {
        $districtCode = $request->query('district_code');
        return response()->json(Village::where('district_code', $districtCode)->select('code as id', 'name', 'meta')->orderBy('name')->get());
    }
}
