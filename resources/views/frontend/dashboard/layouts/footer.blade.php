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
                        <img src="{{ asset($setting('logo_footer')) }}" alt="{{ $storeName }}" class="h-11 w-auto">
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

            <nav class="lg:col-span-2" aria-label="Shop">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Shop</h3>
                <ul class="grid gap-2.5 text-white/75">
                    @foreach ($menuCategories as $category)
                        <li><a href="{{ $category->url }}" class="hover:text-white">{{ $category->name }}</a></li>
                    @endforeach
                    <li><a href="shop.html" class="hover:text-white">Flash deals</a></li>
                </ul>
            </nav>

            <nav class="lg:col-span-2" aria-label="Help">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Help</h3>
                <ul class="grid gap-2.5 text-white/75">
                    <li><a href="{{ route('track.show') }}" class="hover:text-white">Track order</a></li>
                    <li><a href="{{ route('shipping.show') }}" class="hover:text-white">Shipping info</a></li>
                    <li><a href="{{ route('returns.show') }}" class="hover:text-white">Returns &amp; refunds</a></li>
                    <li><a href="{{ route('faq.index') }}" class="hover:text-white">FAQ</a></li>
                    <li><a href="{{ route('contact.show') }}" class="hover:text-white">Contact us</a></li>
                </ul>
            </nav>

            <nav class="lg:col-span-2" aria-label="Company">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Company</h3>
                <ul class="grid gap-2.5 text-white/75">
                    <li><a href="{{ route('about.show') }}" class="hover:text-white">About us</a></li>
                    <li><a href="{{ route('privacy.show') }}" class="hover:text-white">Privacy policy</a></li>
                    <li><a href="{{ route('terms.show') }}" class="hover:text-white">Terms of service</a></li>
                </ul>
            </nav>

            <div class="col-span-2 md:col-span-2 lg:col-span-2">
                <h3 class="font-display font-extrabold text-lg text-accent mb-4">Contact</h3>
                @php
                    $phoneDigits = preg_replace('/\D+/', '', (string) $setting('phone'));
                    $phoneLink = $phoneDigits === '' ? '' : (str_starts_with($phoneDigits, '0') ? '+88' . $phoneDigits : '+' . $phoneDigits);
                @endphp
                <ul class="grid gap-2.5 text-white/75 text-[15px]">
                    @if ($setting('phone'))
                        <li>Hotline: <a href="tel:{{ $phoneLink }}" class="text-white font-bold">{{ $setting('phone') }}</a></li>
                    @endif
                    @if ($setting('support_email'))
                        <li><a href="mailto:{{ $setting('support_email') }}" class="hover:text-white">{{ $setting('support_email') }}</a></li>
                    @endif
                    @if ($setting('address'))
                        <li>{{ $setting('address') }}</li>
                    @endif
                    @if ($setting('opening_hours'))
                        <li>{{ $setting('opening_hours') }}</li>
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
