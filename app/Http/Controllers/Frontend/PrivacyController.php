<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPage;
use Illuminate\View\View;

class PrivacyController extends Controller
{
    public function show(): View
    {
        return view('frontend.privacy.index', ['page' => PrivacyPage::instance()]);
    }
}
