<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    public const ZONES = [
        'inside_dhaka' => 'Inside Dhaka',
        'nearby_dhaka' => 'Nearby Dhaka',
        'outside_dhaka' => 'Outside Dhaka',
    ];

    public const COURIERS = [
        'pathao' => 'Pathao',
        'steadfast' => 'Steadfast',
        'redx' => 'RedX',
    ];

    public const IMAGE_KEYS = ['logo_header', 'logo_footer', 'favicon', 'og_image'];

    public const TIMEZONES = [
        'Asia/Dhaka' => 'Asia/Dhaka (UTC+6)',
        'Asia/Kolkata' => 'Asia/Kolkata (UTC+5:30)',
        'Asia/Karachi' => 'Asia/Karachi (UTC+5)',
        'Asia/Dubai' => 'Asia/Dubai (UTC+4)',
        'Asia/Riyadh' => 'Asia/Riyadh (UTC+3)',
        'Asia/Singapore' => 'Asia/Singapore (UTC+8)',
        'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur (UTC+8)',
        'Asia/Tokyo' => 'Asia/Tokyo (UTC+9)',
        'Europe/London' => 'Europe/London (UTC+0/+1)',
        'Europe/Paris' => 'Europe/Paris (UTC+1/+2)',
        'America/New_York' => 'America/New_York (UTC-5/-4)',
        'America/Los_Angeles' => 'America/Los_Angeles (UTC-8/-7)',
        'Australia/Sydney' => 'Australia/Sydney (UTC+10/+11)',
        'UTC' => 'UTC',
    ];

    /** The store timezone used to show dates and times. Stored timestamps stay in UTC. */
    public static function tz(): string
    {
        $zone = (string) static::get('timezone', 'Asia/Dhaka');

        return in_array($zone, timezone_identifiers_list(), true) ? $zone : 'Asia/Dhaka';
    }

    /**
     * @return array<int, string>
     */
    public static function textKeys(): array
    {
        $keys = ['store_name', 'tagline', 'currency', 'timezone', 'announcement', 'support_email', 'phone', 'opening_hours', 'address', 'facebook', 'instagram', 'youtube', 'tiktok', 'free_delivery_above', 'min_order', 'return_days', 'refund_time', 'chat_call', 'chat_whatsapp', 'chat_messenger', 'meta_title', 'meta_description', 'canonical_domain', 'ga_id', 'pixel_id', 'gsc_code', 'custom_header_script', 'custom_footer_script', 'color_primary', 'color_accent', 'color_text', 'color_background', 'font_heading', 'font_body'];

        foreach (array_keys(self::ZONES) as $zone) {
            $keys[] = "{$zone}_charge";
            $keys[] = "{$zone}_time";
        }

        foreach (array_keys(self::COURIERS) as $courier) {
            $keys[] = "{$courier}_store_id";
            $keys[] = "{$courier}_mode";
        }

        return $keys;
    }

    /**
     * @return array<int, string>
     */
    public static function booleanKeys(): array
    {
        $keys = ['store_open', 'guest_checkout', 'show_out_of_stock', 'cod', 'mfs', 'allow_returns', 'chat_show'];

        foreach (array_keys(self::ZONES) as $zone) {
            $keys[] = "{$zone}_on";
        }

        foreach (array_keys(self::COURIERS) as $courier) {
            $keys[] = "{$courier}_enabled";
        }

        return $keys;
    }

    /**
     * @return array<int, string>
     */
    public static function secretKeys(): array
    {
        $keys = [];

        foreach (array_keys(self::COURIERS) as $courier) {
            $keys[] = "{$courier}_client_id";
            $keys[] = "{$courier}_client_secret";
        }

        return $keys;
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'store_name' => 'NEBULA MART',
            'tagline' => 'Fashion, gadgets, home, beauty and groceries from trusted brands, delivered across Bangladesh.',
            'currency' => 'BDT',
            'timezone' => 'Asia/Dhaka',
            'announcement' => "Pay with cash on delivery\n7-day easy returns on everything",
            'store_open' => '1',
            'guest_checkout' => '1',
            'show_out_of_stock' => '1',

            'support_email' => 'hello@nebulamart.com',
            'phone' => '01711-927829',
            'opening_hours' => 'Sat–Thu · 10:00 AM – 8:00 PM',
            'address' => '4th floor, Razia Plaza, 184, Senpara Parbata, Dhaka 1216',
            'facebook' => 'https://facebook.com/nebulamart',
            'instagram' => 'https://instagram.com/nebulamart',
            'youtube' => '',
            'tiktok' => '',

            'inside_dhaka_charge' => '70',
            'inside_dhaka_time' => '1–2 days',
            'inside_dhaka_on' => '1',
            'nearby_dhaka_charge' => '100',
            'nearby_dhaka_time' => '2–3 days',
            'nearby_dhaka_on' => '1',
            'outside_dhaka_charge' => '130',
            'outside_dhaka_time' => '3–5 days',
            'outside_dhaka_on' => '1',
            'free_delivery_above' => '5000',
            'min_order' => '300',
            'return_days' => '7',
            'refund_time' => '3–5 working days',
            'cod' => '1',
            'mfs' => '0',
            'allow_returns' => '1',

            'pathao_enabled' => '0',
            'pathao_mode' => 'Live',
            'steadfast_enabled' => '0',
            'steadfast_mode' => 'Live',
            'redx_enabled' => '0',
            'redx_mode' => 'Live',

            'chat_show' => '1',
            'chat_call' => '01711927829',
            'chat_whatsapp' => '8801711927829',
            'chat_messenger' => 'nebulamart',

            'meta_title' => 'NEBULA MART | Online Shopping in Bangladesh: Fashion, Electronics, Beauty',
            'meta_description' => 'Shop fashion, electronics, home, beauty and grocery online in Bangladesh. Pay with cash on delivery. Fast delivery and 7-day returns.',
            'canonical_domain' => '',
            'ga_id' => '',
            'pixel_id' => '',
            'gsc_code' => '',
            'custom_header_script' => '',
            'custom_footer_script' => '',
            'og_image' => '',

            'color_primary' => '#0B3A8C',
            'color_accent' => '#F5A800',
            'color_text' => '#1E293B',
            'color_background' => '#FAFAF7',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private static function values(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn() => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = static::values();

        if (!array_key_exists($key, $values)) {
            return static::defaults()[$key] ?? $default;
        }

        $value = $values[$key] ?? '';

        if ($value !== '' && in_array($key, static::secretKeys(), true)) {
            try {
                return decrypt($value);
            } catch (DecryptException) {
                return '';
            }
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $pairs
     */
    public static function putMany(array $pairs): void
    {
        $now = now();
        $secretKeys = static::secretKeys();
        $rows = [];

        foreach ($pairs as $key => $value) {
            if ($value !== '' && in_array($key, $secretKeys, true)) {
                $value = encrypt($value);
            }

            $rows[] = ['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now];
        }

        static::upsert($rows, ['key'], ['value', 'updated_at']);

        Cache::forget(self::CACHE_KEY);
    }
}
