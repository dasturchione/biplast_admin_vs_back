<?php

namespace App\Http\Controllers;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index() {
        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        return BannerResource::collection($banners);
    }
}
