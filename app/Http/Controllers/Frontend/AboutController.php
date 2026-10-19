<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function show(): View
    {
        return view('frontend.about.index', ['about' => AboutPage::instance()]);
    }
}
