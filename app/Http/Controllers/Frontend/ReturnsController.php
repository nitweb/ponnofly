<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReturnsPage;
use Illuminate\View\View;

class ReturnsController extends Controller
{
    public function show(): View
    {
        return view('frontend.returns.index', ['page' => ReturnsPage::instance()]);
    }
}
