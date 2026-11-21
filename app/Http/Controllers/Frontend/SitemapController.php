<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const STATIC_PAGES = [
        ['path' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['path' => '/shop', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['path' => '/flash-deals', 'priority' => '0.7', 'changefreq' => 'daily'],
        ['path' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['path' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['path' => '/faq', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['path' => '/shipping', 'priority' => '0.4', 'changefreq' => 'monthly'],
        ['path' => '/returns', 'priority' => '0.4', 'changefreq' => 'monthly'],
        ['path' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['path' => '/terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];

    public function index(): Response
    {
        $base = rtrim((string) (Setting::get('canonical_domain') ?: url('/')), '/');

        $entries = [];

        foreach (self::STATIC_PAGES as $page) {
            $entries[] = [
                'loc' => $base.($page['path'] === '/' ? '/' : $page['path']),
                'lastmod' => null,
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        foreach (Category::query()->where('is_active', true)->orderBy('id')->cursor() as $category) {
            $entries[] = [
                'loc' => $base.'/category/'.$category->slug,
                'lastmod' => $category->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach (Product::active()->orderBy('id')->cursor() as $product) {
            $entries[] = [
                'loc' => $base.'/product/'.$product->slug,
                'lastmod' => $product->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return response()
            ->view('frontend.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
