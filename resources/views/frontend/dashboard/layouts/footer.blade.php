<footer class="relative">

    <!-- Link columns -->
    <div class="bg-ink text-white">

        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-14 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-x-6 gap-y-10">

            <div class="col-span-2 md:col-span-4 lg:col-span-4">

                @php
                    $storeName = $setting('store_name');
                    $storeInitials = \Illuminate\Support\Str::of($storeName)->explode(' ')->filter()->map(fn($word) => mb_substr($word, 0, 1))->take(2)->implode('');
                    $socials = [['facebook', 'Facebook', 'i-fb', 'hover:bg-primary'], ['instagram', 'Instagram', 'i-ig', 'hover:bg-primary'], ['youtube', 'YouTube', 'i-yt', 'hover:bg-accent hover:text-ink'], ['tiktok', 'TikTok', 'i-tt', 'hover:bg-accent hover:text-ink']];
                @endphp

                <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4">
                    @if ($setting('logo_footer'))
                        <img src="{{ asset($setting('logo_footer')) }}" alt="{{ $storeName }}" class="h-14 w-auto">
                    @else
                        <span class="relative grid place-items-center w-11 h-11 rounded-xl bg-primary -rotate-6">
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-accent border border-line"></span>
                            <span class="absolute -bottom-1 -left-1 w-3 h-3 rounded-full bg-primary border border-line"></span>
                            <span class="logo-mark font-display font-extrabold">{{ $storeInitials }}</span>
                        </span>
                        <span class="font-display font-extrabold text-3xl tracking-tight">{{ $storeName }}</span>
                    @endif
                </a>

                @if ($setting('tagline'))
                    <p class="text-white/70 max-w-sm leading-relaxed">{{ $setting('tagline') }}</p>
                @endif

                <div class="flex gap-2.5 mt-6">
                    @foreach ($socials as [$key, $label, $icon, $hover])
                        @if ($setting($key))
                            <a href="{{ $setting($key) }}" target="_blank" rel="noopener" class="w-11 h-11 rounded-xl bg-white/10 {{ $hover }} grid place-items-center transition-colors" aria-label="{{ $label }}">
                                <svg class="w-5 h-5">
                                    <use href="#{{ $icon }}" />
                                </svg>
                            </a>
                        @endif
                    @endforeach
                </div>

            </div>

            <nav class="lg:col-span-2" aria-label="Useful Links">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Useful Links</h3>
                <ul class="grid gap-2.5 text-white/75">
                    <li><a href="{{ route('track.show') }}" class="hover:text-white">Track order</a></li>
                    <li><a href="{{ route('faq.index') }}" class="hover:text-white">Frequently Asked Questions</a></li>
                    <li><a href="{{ route('about.show') }}" class="hover:text-white">About {{ $setting('store_name') }}</a></li>
                    <li><a href="{{ route('contact.show') }}" class="hover:text-white">Contact Store</a></li>
                    <li><a href="{{ route('account.wishlist') }}" class="hover:text-white">My Wishlist</a></li>
                </ul>
            </nav>

            <nav class="lg:col-span-2" aria-label="Policy & Security">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Policy &amp; Security</h3>
                <ul class="grid gap-2.5 text-white/75">
                    <li><a href="{{ route('privacy.show') }}" class="hover:text-white">Privacy policy</a></li>
                    <li><a href="{{ route('returns.show') }}" class="hover:text-white">Refunds &amp; Return</a></li>
                    <li><a href="{{ route('terms.show') }}" class="hover:text-white">Terms &amp; conditions</a></li>
                    <li><a href="{{ route('shipping.show') }}" class="hover:text-white">Shipping</a></li>
                </ul>
            </nav>

            <div class="col-span-2 md:col-span-2 lg:col-span-4">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Contact</h3>
                @php
                    $phoneDigits = preg_replace('/\D+/', '', (string) $setting('phone'));
                    $phoneLink = $phoneDigits === '' ? '' : (str_starts_with($phoneDigits, '0') ? '+88' . $phoneDigits : '+' . $phoneDigits);
                @endphp
                <ul class="grid gap-3.5 text-white/75 text-[15px]">
                    @if ($setting('address'))
                        <li class="flex gap-3"><svg class="w-5 h-5 shrink-0 mt-0.5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><span>{{ $setting('address') }}</span></li>
                    @endif
                    @if ($setting('phone'))
                        <li class="flex gap-3"><svg class="w-5 h-5 shrink-0 mt-0.5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><a href="tel:{{ $phoneLink }}" class="hover:text-white">{{ $setting('phone') }}</a></li>
                    @endif
                    @if ($setting('support_email'))
                        <li class="flex gap-3"><svg class="w-5 h-5 shrink-0 mt-0.5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><a href="mailto:{{ $setting('support_email') }}" class="hover:text-white break-all">{{ $setting('support_email') }}</a></li>
                    @endif
                    @if ($setting('opening_hours'))
                        <li class="flex gap-3"><svg class="w-5 h-5 shrink-0 mt-0.5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>{{ $setting('opening_hours') }}</span></li>
                    @endif
                </ul>
            </div>

        </div>

        <!-- Payment + legal -->
        <div class="border-t border-white/15">

            <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-5">

                <p class="text-sm text-white/60 order-2 md:order-1">© <span id="year">{{ date('Y') }}</span> {{ $setting('store_name') }}. All rights reserved.</p>

                <div class="ft-badges order-1 md:order-2">

                    <ul class="ft-badges__row" aria-label="Accepted payment method">
                        <li class="ft-badge ft-badge--cod">Cash on delivery</li>
                    </ul>

                    <ul class="ft-badges__row" aria-label="Delivery partners">

                        <li class="ft-badge ft-badge--courier" title="Pathao Courier">
                            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                                <path fill="currentColor" d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" />
                                <circle cx="7" cy="17" r="2" fill="currentColor" />
                                <circle cx="17" cy="17" r="2" fill="currentColor" />
                            </svg>
                            Pathao
                        </li>

                        <li class="ft-badge ft-badge--courier" title="Steadfast Courier">
                            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                                <path fill="currentColor" d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" />
                                <circle cx="7" cy="17" r="2" fill="currentColor" />
                                <circle cx="17" cy="17" r="2" fill="currentColor" />
                            </svg>
                            Steadfast
                        </li>

                        <li class="ft-badge ft-badge--courier" title="RedX">
                            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                                <path fill="currentColor" d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" />
                                <circle cx="7" cy="17" r="2" fill="currentColor" />
                                <circle cx="17" cy="17" r="2" fill="currentColor" />
                            </svg>
                            RedX
                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</footer>
