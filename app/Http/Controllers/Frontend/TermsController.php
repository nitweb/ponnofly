<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TermsPage;
use Illuminate\View\View;

class TermsController extends Controller
{
    public function show(): View
    {
        return view('frontend.terms.index', ['page' => TermsPage::instance()]);
    }
}
