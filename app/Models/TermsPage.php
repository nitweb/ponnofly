<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsPage extends Model
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
            'hero_title' => 'Terms of service',
            'hero_subtitle' => 'Please read these terms before using NEBULA MART.',
            'last_updated' => '2026-01-01',
            'keys' => [
                ['icon' => 'check', 'title' => 'Be 18 or have permission', 'text' => 'Guardian consent is fine for younger shoppers.'],
                ['icon' => 'wallet', 'title' => 'Prices in taka', 'text' => 'Shown in BDT and may change without notice.'],
                ['icon' => 'return', 'title' => 'Cancel before shipping', 'text' => 'After shipping, our return policy applies.'],
            ],
            'clauses' => [
                ['title' => 'Using the site', 'body' => "By placing an order you agree that the information you provide is accurate and that you are at least 18 years old or have a guardian's permission."],
                ['title' => 'Your account', 'body' => "Keep your password private. You are responsible for activity on your account, so tell us right away if you suspect someone else is using it."],
                ['title' => 'Prices and availability', 'body' => "Prices are in Bangladeshi taka and may change without notice. If a product is out of stock we will contact you and offer a refund or an alternative."],
                ['title' => 'Orders and cancellation', 'body' => "You can cancel an order before it is shipped by contacting support. Once shipped, our [return policy](/returns) applies."],
                ['title' => 'Delivery', 'body' => "Delivery times and charges are listed on our [shipping page](/shipping). Please make sure someone can receive the parcel at the address you give us."],
                ['title' => 'Fair use', 'body' => "- Do not misuse coupons or create fake orders\n- Do not try to disrupt or break into the website\n- Do not copy our content without permission"],
                ['title' => 'Changes', 'body' => "We may update these terms from time to time. Continued use of the site means you accept the updated terms."],
            ],
            'cta_title' => 'Questions about these terms?',
            'cta_text' => 'Our team replies to every message within one working day.',
        ];
    }
}
