<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'title', 'starts_at', 'ends_at', 'repeat_daily', 'show_on_home', 'show_countdown', 'is_active',
])]
class FlashDeal extends Model
{
    /** Deal times are entered and stored as Dhaka wall-clock time (UTC+6, no DST). */
    public const TIMEZONE = 'Asia/Dhaka'; // fallback only; see Setting::tz()

    private static ?Collection $liveMemo = null;

    private static ?Collection $itemMemo = null;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'repeat_daily' => 'boolean',
            'show_on_home' => 'boolean',
            'show_countdown' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushLiveCache());
        static::deleted(fn () => static::flushLiveCache());
    }

    public function items(): HasMany
    {
        return $this->hasMany(FlashDealProduct::class)->orderBy('sort_order')->orderBy('id');
    }

    public static function flushLiveCache(): void
    {
        self::$liveMemo = null;
        self::$itemMemo = null;
    }

    /** Active deals that are running right now, the one ending first at the front. */
    public static function live(): Collection
    {
        return self::$liveMemo ??= static::query()
            ->where('is_active', true)
            ->with('items')
            ->get()
            ->filter(fn (self $deal) => $deal->isLive())
            ->sortBy(fn (self $deal) => $deal->windowAt()[1]->getTimestamp())
            ->values();
    }

    /** @return Collection<int, FlashDealProduct> keyed by product id, the biggest live discount wins. */
    public static function liveItemMap(): Collection
    {
        if (self::$itemMemo !== null) {
            return self::$itemMemo;
        }

        $map = collect();

        foreach (static::live() as $deal) {
            foreach ($deal->items as $item) {
                $current = $map->get($item->product_id);

                if (! $current || $item->discount_percent > $current->discount_percent) {
                    $item->setRelation('deal', $deal);
                    $map->put($item->product_id, $item);
                }
            }
        }

        return self::$itemMemo = $map;
    }

    /** @return list<int> */
    public static function liveProductIds(): array
    {
        return static::liveItemMap()->keys()->map(fn ($id) => (int) $id)->all();
    }

    /** Active deals that are not running yet, soonest first. */
    public static function upcoming(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->with('items')
            ->get()
            ->reject(fn (self $deal) => $deal->isLive())
            ->filter(fn (self $deal) => $deal->nextStart() !== null)
            ->sortBy(fn (self $deal) => $deal->nextStart()->getTimestamp())
            ->values();
    }

    public function startsAtLocal(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->starts_at->format('Y-m-d H:i:s'), Setting::tz());
    }

    public function endsAtLocal(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->ends_at->format('Y-m-d H:i:s'), Setting::tz());
    }

    /**
     * The running window [from, to] at the given moment, or null when the deal is not running.
     * A repeating deal reuses the start and end time of day every day, from the start date onwards.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    public function windowAt(?CarbonInterface $now = null): ?array
    {
        $now = $this->localNow($now);
        $start = $this->startsAtLocal();
        $end = $this->endsAtLocal();

        if (! $this->repeat_daily) {
            $to = $this->windowEnd($end, $start, false);

            return $now->gte($start) && $now->lt($to) ? [$start, $to] : null;
        }

        foreach ([0, -1] as $offset) {
            $day = $now->startOfDay()->addDays($offset);

            if ($day->lt($start->startOfDay())) {
                continue;
            }

            $from = $day->setTimeFrom($start);
            $to = $this->windowEnd($day->setTimeFrom($end), $from, true);

            if ($now->gte($from) && $now->lt($to)) {
                return [$from, $to];
            }
        }

        return null;
    }

    public function isLive(?CarbonInterface $now = null): bool
    {
        return $this->is_active && $this->windowAt($now) !== null;
    }

    public function nextStart(?CarbonInterface $now = null): ?CarbonImmutable
    {
        $now = $this->localNow($now);
        $start = $this->startsAtLocal();

        if (! $this->repeat_daily) {
            return $now->lt($start) ? $start : null;
        }

        $candidate = $now->startOfDay()->max($start->startOfDay())->setTimeFrom($start);

        return $candidate->lte($now) ? $candidate->addDay() : $candidate;
    }

    /** @return 'live'|'scheduled'|'ended'|'inactive' */
    public function phase(?CarbonInterface $now = null): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->windowAt($now) !== null) {
            return 'live';
        }

        if ($this->repeat_daily || $this->localNow($now)->lt($this->startsAtLocal())) {
            return 'scheduled';
        }

        return 'ended';
    }

    /** Products of this deal that can be sold, in the admin's order. */
    public function activeProducts(?int $limit = null): Collection
    {
        $products = Product::active()
            ->whereIn('id', $this->items->pluck('product_id'))
            ->with(['images', 'variants', 'category', 'brand'])
            ->get()
            ->keyBy('id');

        $list = $this->items->map(fn (FlashDealProduct $item) => $products->get($item->product_id))->filter()->values();

        return $limit ? $list->take($limit)->values() : $list;
    }

    private function localNow(?CarbonInterface $now): CarbonImmutable
    {
        return CarbonImmutable::instance($now ?? now())->setTimezone(Setting::tz());
    }

    /** An end time of 23:59 means "until midnight"; an end before the start time rolls over to the next day. */
    private function windowEnd(CarbonImmutable $end, CarbonImmutable $from, bool $rollOver): CarbonImmutable
    {
        if ($end->format('H:i') === '23:59') {
            return $end->addMinute();
        }

        return $rollOver && $end->lte($from) ? $end->addDay() : $end;
    }
}
