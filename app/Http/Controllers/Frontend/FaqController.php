<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::active()->get();

        return view('frontend.faq.index', [
            'faqs' => $faqs,
            'categories' => collect(Faq::CATEGORIES)->only($faqs->pluck('category')->unique()->all()),
        ]);
    }
}
