<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_updated' => 'date',
            'keys' => 'array',
            'clauses' => 'array',
        ];
    }

    public static function instance(): self
    {
        return self::first() ?? self::create(self::defaults());
    }

    public static function defaults(): array
    {
        return [
            'hero_title' => 'Privacy policy',
            'hero_subtitle' => 'How we collect, use and protect your information.',
            'last_updated' => '2026-01-01',
            'keys' => [
                ['icon' => 'lock', 'title' => 'Your data stays safe', 'text' => 'We protect it with industry-standard measures.'],
                ['icon' => 'wallet', 'title' => 'No card numbers stored', 'text' => 'Payments are handled by our partners.'],
                ['icon' => 'user', 'title' => 'You are in control', 'text' => 'Ask us to correct or delete your data.'],
            ],
            'clauses' => [
                ['title' => 'Information we collect', 'body' => "We collect your name, phone number, email and delivery address when you place an order or create an account."],
                ['title' => 'How we use it', 'body' => "- To process and deliver your orders\n- To send order updates by SMS or email\n- To improve our website and services"],
                ['title' => 'Who we share it with', 'body' => "We share only what is needed with delivery partners and payment providers to complete your order. We never sell your personal data."],
                ['title' => 'Payments', 'body' => "We accept cash on delivery only, so we do not collect or store any card or mobile banking details."],
                ['title' => 'Cookies', 'body' => "We use small files to remember your cart, wishlist and preferences. You can clear them anytime in your browser settings."],
                ['title' => 'Your choices', 'body' => "You may ask us to correct or delete your personal data at any time by writing to [hello@nebulamart.com](mailto:hello@nebulamart.com)."],
            ],
            'cta_title' => 'Need help with your data?',
            'cta_text' => 'Write to us and we will respond within one working day.',
        ];
    }
}
