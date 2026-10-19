<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    public const ICONS = [
        'shield' => 'Shield',
        'tag' => 'Tag',
        'zap' => 'Lightning',
        'heart' => 'Heart',
        'users' => 'People',
        'gem' => 'Gem',
        'truck' => 'Truck',
        'package' => 'Package',
        'return' => 'Return',
        'star' => 'Star',
        'target' => 'Target',
        'help' => 'Help',
    ];

    public const IMAGE_DIRECTORY = 'uploads/about';

    private const DEFAULT_IMAGES = 'assets/frontend/images/about/';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'story_points' => 'array',
            'values' => 'array',
            'steps' => 'array',
            'gallery' => 'array',
        ];
    }

    public static function instance(): self
    {
        return self::first() ?? self::create(self::defaults());
    }

    public function imageUrl(?string $path): string
    {
        return $path ? asset($path) : asset('assets/frontend/images/placeholder.svg');
    }

    public static function defaults(): array
    {
        return [
            'hero_title' => 'A Bangladeshi marketplace built on honest prices and fast delivery.',
            'hero_subtitle' => 'NEBULA MART brings fashion, electronics, beauty, home and grocery from trusted brands to your door, anywhere in the country.',
            'hero_image' => self::DEFAULT_IMAGES.'photo-1607082348824-0a96f2a4b9da.webp',
            'hero_caption' => 'Made in Dhaka',
            'stats' => [
                ['value' => 50, 'suffix' => 'K+', 'label' => 'Orders delivered'],
                ['value' => 64, 'suffix' => '', 'label' => 'Districts covered'],
                ['value' => 5000, 'suffix' => '+', 'label' => 'Products available'],
            ],
            'story_title' => 'Who we are',
            'story_text' => "NEBULA MART is an online marketplace based in Dhaka. We bring fashion, electronics, beauty, home and grocery products from trusted brands to your door, anywhere in Bangladesh.\n\nWe keep things simple: clear prices in taka, cash on delivery, and an easy return policy.",
            'story_points' => [
                'Honest product descriptions and real photos',
                'Fair prices with no hidden charges',
                'Fast delivery and friendly support in Bangla and English',
            ],
            'story_image' => self::DEFAULT_IMAGES.'photo-1586528116311-ad8dd3c8310d.webp',
            'story_tag_number' => '64',
            'story_tag_text' => 'districts, one marketplace',
            'mission_text' => 'To make online shopping in Bangladesh honest, affordable and reliable, so every customer gets what they ordered, at the price they saw, on time.',
            'vision_text' => 'To become the most trusted marketplace in Bangladesh, where every district is as well served as the capital and every buyer can find what they need.',
            'values_title' => 'The values we work by',
            'values_subtitle' => 'These are the promises behind every order we deliver.',
            'values' => [
                ['icon' => 'shield', 'title' => 'Honesty first', 'text' => 'Real photos, accurate descriptions and clear prices. What you see is what you get.'],
                ['icon' => 'tag', 'title' => 'Fair prices', 'text' => 'Prices in taka with no hidden charges. Delivery fees are shown before you pay.'],
                ['icon' => 'zap', 'title' => 'Speed that counts', 'text' => 'Same-day dispatch before 2 PM and delivery in 1-5 days across the country.'],
                ['icon' => 'heart', 'title' => 'Customers at the center', 'text' => 'Friendly support in Bangla and English, and a return policy that is easy to use.'],
                ['icon' => 'users', 'title' => 'Respect for partners', 'text' => 'We work with verified brands and help local businesses grow online.'],
                ['icon' => 'gem', 'title' => 'Quality you can trust', 'text' => 'Products come from trusted brands, and we act quickly when something is not right.'],
            ],
            'process_title' => 'How an order reaches you',
            'process_subtitle' => 'A simple, careful process from the warehouse to your door.',
            'steps' => [
                ['title' => 'Trusted brands', 'text' => 'We bring on verified brands so you can shop with confidence.'],
                ['title' => 'Careful checks', 'text' => 'Listings are reviewed for honest descriptions and real photos.'],
                ['title' => 'Packed and dispatched', 'text' => 'Orders placed before 2 PM leave the same day.'],
                ['title' => 'Delivered to you', 'text' => 'Pay on delivery or online, and track every step.'],
            ],
            'gallery_title' => 'Built in Dhaka, for all of Bangladesh',
            'gallery' => [
                ['image' => self::DEFAULT_IMAGES.'photo-1522202176988-66273c2fd55f.webp', 'alt' => 'Our team working together'],
                ['image' => self::DEFAULT_IMAGES.'photo-1578575437130-527eed3abbec.webp', 'alt' => 'Packages ready to ship'],
                ['image' => self::DEFAULT_IMAGES.'photo-1556742049-0cfed4f6a45d.webp', 'alt' => 'A customer shopping'],
            ],
            'cta_title' => 'Shop with confidence.',
            'cta_text' => 'Find something you love today.',
        ];
    }
}
