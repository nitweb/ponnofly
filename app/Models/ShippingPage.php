<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPage extends Model
{
    public const ICONS = [
        'home' => 'Home',
        'truck' => 'Truck',
        'pin' => 'Location pin',
        'package' => 'Package',
        'box' => 'Box',
        'clock' => 'Clock',
        'sms' => 'SMS',
        'wallet' => 'Wallet',
        'check' => 'Check',
        'shield' => 'Shield',
        'star' => 'Star',
        'zap' => 'Lightning',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'zone_areas' => 'array',
            'know_items' => 'array',
            'flow_steps' => 'array',
        ];
    }

    public static function instance(): self
    {
        return self::first() ?? self::create(self::defaults());
    }

    /**
     * @return array{areas: string, icon: string}
     */
    public function zoneArea(string $zone): array
    {
        $fallback = self::defaults()['zone_areas'][$zone] ?? ['areas' => '', 'icon' => 'truck'];

        return array_merge($fallback, array_filter($this->zone_areas[$zone] ?? [], fn ($value) => filled($value)));
    }

    public static function defaults(): array
    {
        return [
            'hero_title' => 'Shipping info',
            'hero_subtitle' => 'Delivery charges and times across Bangladesh.',
            'zones_title' => 'Delivery charges and times',
            'zones_text' => 'Your charge depends on where your order is going. You see the exact amount at checkout before you pay.',
            'zone_areas' => [
                'inside_dhaka' => ['icon' => 'home', 'areas' => 'Anywhere within Dhaka city'],
                'nearby_dhaka' => ['icon' => 'truck', 'areas' => 'Gazipur, Narayanganj, Narsingdi, Munshiganj, Manikganj'],
                'outside_dhaka' => ['icon' => 'pin', 'areas' => 'All other districts across Bangladesh'],
            ],
            'know_title' => 'Good to know',
            'know_items' => [
                ['icon' => 'clock', 'title' => 'Same-day dispatch', 'text' => 'Orders placed before 2 PM are dispatched the same day.'],
                ['icon' => 'sms', 'title' => 'SMS when it ships', 'text' => 'You will get an SMS the moment your package leaves our hub.'],
                ['icon' => 'wallet', 'title' => 'Cash on delivery', 'text' => 'Pay when you receive. Available in all 64 districts.'],
            ],
            'flow_title' => 'From checkout to your door',
            'flow_text' => 'Four simple steps, and you are kept informed at every one.',
            'flow_steps' => [
                ['title' => 'Place your order', 'text' => 'Choose your district at checkout to see the exact delivery charge.'],
                ['title' => 'We pack and dispatch', 'text' => 'Order before 2 PM and it leaves the same day.'],
                ['title' => 'Get the SMS', 'text' => 'We text you when your package ships, so you know it is on the way.'],
                ['title' => 'Receive and pay', 'text' => 'Pay by cash on delivery.'],
            ],
            'cta_title' => 'Already placed an order?',
            'cta_text' => 'Enter your order number and see where your package is right now.',
        ];
    }
}
