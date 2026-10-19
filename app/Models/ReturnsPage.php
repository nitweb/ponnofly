<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnsPage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'win_needs' => 'array',
            'steps' => 'array',
            'nope_items' => 'array',
        ];
    }

    public static function instance(): self
    {
        return self::first() ?? self::create(self::defaults());
    }

    /**
     * Replaces {days} and {refund} with the values saved in Settings → Rules & payment.
     */
    public static function replacePlaceholders(?string $text): string
    {
        return strtr((string) $text, [
            '{days}' => (string) Setting::get('return_days', 7),
            '{refund}' => (string) Setting::get('refund_time', '3–5 working days'),
        ]);
    }

    public static function defaults(): array
    {
        return [
            'hero_title' => 'Returns & refunds',
            'hero_subtitle' => 'Not happy with your order? We make it easy.',
            'win_caption' => 'from the day you receive it',
            'win_title' => '{days}-day return window',
            'win_text' => 'You can return most products within {days} days of delivery. To qualify, your item needs to be:',
            'win_needs' => ['Unused', 'In original packaging', 'With all tags attached'],
            'steps_title' => 'How to return an item',
            'steps_text' => 'Three steps from message to money back.',
            'steps' => [
                ['title' => 'Contact us', 'text' => 'Send us your order number and the reason for the return.'],
                ['title' => 'We pick it up', 'text' => 'We arrange a pickup from your address. No need to go anywhere.'],
                ['title' => 'Get your refund', 'text' => 'Once we receive the item, your refund is processed within {refund}.'],
            ],
            'nope_title' => 'Not returnable',
            'nope_text' => 'For hygiene and safety, these items cannot be returned.',
            'nope_items' => ['Opened beauty and personal care products', 'Innerwear and socks', 'Digital gift cards'],
            'help_title' => 'Not sure if it qualifies?',
            'help_text' => 'Message us with your order number and we will tell you straight away.',
        ];
    }
}
