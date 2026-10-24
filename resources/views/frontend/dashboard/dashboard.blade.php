<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteTitle = trim($__env->yieldContent('frontend_title')) ?: $setting('meta_title');
        $siteDescription = trim($__env->yieldContent('frontend_description')) ?: $setting('meta_description');
        $siteBase = rtrim($setting('canonical_domain') ?: url('/'), '/');
        $canonicalUrl = $siteBase . request()->getPathInfo();
        $storeName = $setting('store_name');
        $logoUrl = $setting('logo_header') ? asset($setting('logo_header')) : null;
        $ogImageUrl = $setting('og_image') ? asset($setting('og_image')) : null;
        $shareImage = trim($__env->yieldContent('frontend_image')) ?: ($ogImageUrl ?: (isset($banners) && $banners->isNotEmpty() ? $banners->first()->image_url : $logoUrl));
        $sameAs = array_values(array_filter([$setting('facebook'), $setting('instagram'), $setting('youtube'), $setting('tiktok')]));
        $organization = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $storeName,
            'url' => $siteBase . '/',
            'logo' => $logoUrl,
            'sameAs' => $sameAs ?: null,
        ]);
        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $storeName,
            'url' => $siteBase . '/',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $siteBase . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
    @endphp
    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $siteDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:site_name" content="{{ $storeName }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_BD">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $siteDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
        @if ($ogImageUrl && $shareImage === $ogImageUrl)
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
        @endif
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="{{ $setting('color_primary') }}">
    @hasSection('frontend_keywords')
        <meta name="keywords" content="{{ trim($__env->yieldContent('frontend_keywords')) }}">
    @endif
    @if ($setting('gsc_code'))
        <meta name="google-site-verification" content="{{ $setting('gsc_code') }}">
    @endif

    <script type="application/ld+json">{!! json_encode($organization, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($website, $jsonFlags) !!}</script>
    @stack('jsonld')

    @if ($setting('favicon'))
        <link rel="icon" href="{{ asset($setting('favicon')) }}">
        <link rel="shortcut icon" href="{{ asset($setting('favicon')) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/frontend/images/favicon.svg') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/variables.css') }}">
    @include('frontend.dashboard.layouts.theme')
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/tailwind.css') }}">
    <link rel="preload" href="{{ asset('assets/frontend/fonts/inter-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('assets/frontend/fonts/plus-jakarta-sans-latin-800-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @isset($banners)
        @if ($banners->isNotEmpty())
            <link rel="preload" href="{{ $banners->first()->image_url }}" as="image" type="image/webp" fetchpriority="high">
        @endif
    @endisset
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/common.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/about.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/checkout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/contact.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/faq.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/info.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/product.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/returns.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/shipping.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/shop.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/track.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/404.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/refresh.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/extras.css') }}?v={{ filemtime(public_path('assets/frontend/css/pages/extras.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pages/customer.css') }}?v={{ filemtime(public_path('assets/frontend/css/pages/customer.css')) }}">
    @if ($setting('ga_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $setting('ga_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', @json($setting('ga_id')));
        </script>
    @endif
    @if ($setting('pixel_id'))
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($setting('pixel_id')));
            fbq('track', 'PageView');
        </script>
    @endif
    @if (trim((string) $setting('custom_header_script')) !== '')
        {!! $setting('custom_header_script') !!}
    @endif
</head>

<body class="antialiased" data-page="@yield('page', 'home')">

    <!-- SVG ICON SPRITE -->
    <svg xmlns="http://www.w3.org/2000/svg" class="hidden">

        <symbol id="i-search" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2.4" />
            <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
        </symbol>

        <symbol id="i-bag" viewBox="0 0 24 24">
            <path d="M5 8h14l-1 12H6L5 8Z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
            <path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
        </symbol>

        <symbol id="i-user" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2.2" />
            <path d="M4 21c0-4 3.6-6 8-6s8 2 8 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
        </symbol>

        <symbol id="i-eye" viewBox="0 0 24 24">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8" />
        </symbol>

        <symbol id="i-heart" viewBox="0 0 24 24">
            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z" style="fill: var(--heart-fill, none)" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
        </symbol>

        <symbol id="i-chev" viewBox="0 0 24 24">
            <path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        </symbol>

        <symbol id="i-menu" viewBox="0 0 24 24">
            <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
        </symbol>

        <symbol id="i-x" viewBox="0 0 24 24">
            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
        </symbol>

        <symbol id="i-truck" viewBox="0 0 24 24">
            <path d="M2 6h12v10H2zM14 10h4l3 3v3h-7z" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linejoin="round" />
            <circle cx="7" cy="18" r="1.8" fill="currentColor" />
            <circle cx="17" cy="18" r="1.8" fill="currentColor" />
        </symbol>

        <symbol id="i-return" viewBox="0 0 24 24">
            <path d="M4 12a8 8 0 1 0 3-6.2M4 4v4h4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </symbol>

        <symbol id="i-shield" viewBox="0 0 24 24">
            <path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
            <path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </symbol>

        <symbol id="i-chat" viewBox="0 0 24 24">
            <path d="M4 5h16v11H9l-5 4V5Z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" />
        </symbol>

        <symbol id="i-fb" viewBox="0 0 24 24">
            <path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H7v4h3v6h4v-6h3l1-4h-4V8.5c0-.3.2-.5.5-.5Z" fill="currentColor" />
        </symbol>

        <symbol id="i-ig" viewBox="0 0 24 24">
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="2.2" />
            <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2.2" />
            <circle cx="17.2" cy="6.8" r="1.2" fill="currentColor" />
        </symbol>

        <symbol id="i-yt" viewBox="0 0 24 24">
            <rect x="2.5" y="5.5" width="19" height="13" rx="4" fill="none" stroke="currentColor" stroke-width="2.2" />
            <path d="m10 9.5 5 2.5-5 2.5v-5Z" fill="currentColor" />
        </symbol>

        <symbol id="i-tt" viewBox="0 0 24 24">
            <path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5M14 3c.3 2.5 2 4.2 4.5 4.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
        </symbol>

    </svg>

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] bg-primary text-white font-bold px-4 py-2 rounded-lg">
        Skip to content
    </a>

    <!-- ============================= HEADER ============================= -->
    @include('frontend.dashboard.layouts.header')

    <!-- ============================= MOBILE MENU ============================= -->
    @include('frontend.dashboard.layouts.mobile_menu')

    <!-- ============================= CART DRAWER ============================= -->
    <div id="cart-drawer" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-label="Shopping cart">

        <div class="absolute inset-0 bg-ink/60" data-close-cart></div>

        <aside class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white border-l-4 border-line flex flex-col">

            <div class="flex items-center justify-between p-4 bg-accent border-b-4 border-line">

                <h2 class="font-display font-extrabold text-2xl text-white">Your cart <span class="text-base font-bold" data-cart-count>({{ $cartSummary['count'] }})</span></h2>

                <button class="p-2 rounded-lg hover:bg-ink/10" data-close-cart aria-label="Close cart">
                    <svg class="w-6 h-6">
                        <use href="#i-x" />
                    </svg>
                </button>

            </div>

            <ul class="flex-1 overflow-y-auto p-4 grid gap-4 content-start" data-cart-list>
                @if (! $cartSummary['items'])
                    <li class="text-center py-10 font-bold text-ink/60">Your cart is empty.</li>
                @endif
            </ul>

            <div class="p-4 border-t-4 border-line bg-paper">
                <div class="flex justify-between font-bold text-lg mb-3"><span>Subtotal</span><span data-cart-subtotal>৳{{ number_format($cartSummary['subtotal'], floor($cartSummary['subtotal']) == $cartSummary['subtotal'] ? 0 : 2) }}</span></div>
                <a href="{{ $cartSummary['checkout_url'] }}" data-cart-checkout class="block text-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-3.5 shadow-sm">Checkout</a>
                <a href="{{ route('cart.index') }}" class="block text-center mt-2.5 font-bold underline underline-offset-4">View full cart</a>
            </div>

        </aside>

    </div>

    <!-- ============================= MAIN (page content goes here) ============================= -->
    <main id="main">
        @yield('frontend_contents')
        <div class="pb-14"></div>
    </main>

    <!-- ============================= FOOTER ============================= -->
    @include('frontend.dashboard.layouts.footer')


    <div id="qv" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-label="Quick view">
        <div class="absolute inset-0 bg-ink/60" data-close-qv></div>
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[92%] max-w-3xl bg-white border-4 border-line rounded-3xl shadow-sm overflow-hidden grid sm:grid-cols-2">
            <img id="qv-img" referrerpolicy="no-referrer" src="" alt="" width="600" height="600" class="w-full h-full object-cover aspect-square">
            <div class="p-6 flex flex-col">
                <button type="button" class="self-end p-1 -mt-2 -mr-2" data-close-qv aria-label="Close quick view">
                    <svg class="w-6 h-6">
                        <use href="#i-x" />
                    </svg>
                </button>
                <p id="qv-cat" class="qv-cat" hidden></p>
                <h2 id="qv-name" class="font-display font-extrabold text-2xl leading-tight"></h2>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-display font-extrabold text-primary">
                        ৳
                        <span id="qv-price"></span>
                    </span>
                    <s class="text-ink/50">
                        ৳
                        <span id="qv-old"></span>
                    </s>
                </div>
                <p id="qv-desc" class="qv-desc" hidden></p>
                <ul class="qv-trust" aria-label="Availability and returns">
                    <li>In stock</li>
                    <li>7-day returns</li>
                </ul>
                <div class="mt-auto pt-6 grid gap-2">
                    <button type="button" class="addcart bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-3 border border-line">
                        Add to cart
                    </button>
                    <a id="qv-link" href="product.html" class="text-center font-bold underline underline-offset-4">View full details</a>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[90] bg-ink text-white font-bold px-5 py-3 rounded-xl border border-accent opacity-0 pointer-events-none transition-opacity" role="status" aria-live="polite"></div>


    <script src="{{ asset('assets/frontend/js/electronics-data.js') }}" defer></script>
    <script>
        window.NEBULA_CART = {
            initial: @json($cartSummary),
            routes: {
                summary: @json(route('cart.summary')),
                add: @json(route('cart.items.store')),
                item: @json(url('/cart/items')),
                coupon: @json(route('cart.coupon.apply')),
                zone: @json(route('cart.zone')),
                cart: @json(route('cart.index'))
            }
        };
    </script>
    <script src="{{ asset('assets/frontend/js/main.js') }}" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
    <script>
        window.notify = function (message, type) {
            if (!message) {
                return;
            }

            if (typeof Toastify === 'undefined') {
                var fallback = document.getElementById('toast');

                if (fallback) {
                    fallback.textContent = message;
                    fallback.style.opacity = 1;
                    setTimeout(function () { fallback.style.opacity = 0; }, 2600);
                }

                return;
            }

            Toastify({
                text: message,
                duration: 4000,
                gravity: 'top',
                position: 'right',
                close: true,
                stopOnFocus: true,
                style: {
                    background: type === 'error' ? '#DC2626' : '#0A8F6B',
                    borderRadius: '12px',
                    fontWeight: '600',
                    boxShadow: '0 14px 30px -10px rgba(0,0,0,.4)'
                }
            }).showToast();
        };
    </script>
    <script>
        window.NEBULA_WISH = {
            toggle: @json(route('account.wishlist.toggle')),
            login: @json(route('login')),
            guest: @json(! auth()->check()),
            ids: @json($wishlistIds)
        };
    </script>
    <script src="{{ asset('assets/frontend/js/cart.js') }}?v={{ filemtime(public_path('assets/frontend/js/cart.js')) }}" defer></script>
    <script src="{{ asset('assets/frontend/js/wishlist.js') }}?v={{ filemtime(public_path('assets/frontend/js/wishlist.js')) }}" defer></script>
    <script src="{{ asset('assets/frontend/js/loop-slider.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/electronics.js') }}" defer></script>
    <script>
        window.NEBULA_CHAT = {
            show: @json($setting('chat_show') === '1'),
            phone: @json(preg_replace('/\D+/', '', (string) $setting('chat_call'))),
            whatsapp: @json(preg_replace('/\D+/', '', (string) $setting('chat_whatsapp'))),
            messenger: @json($setting('chat_messenger')),
            name: @json($storeName)
        };
    </script>
    <script src="{{ asset('assets/frontend/js/chat.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/countdown.js') }}?v={{ filemtime(public_path('assets/frontend/js/pages/countdown.js')) }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/about.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/auth.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/checkout.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/contact.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/faq.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/info.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/success.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/track.js') }}" defer></script>
    <script src="{{ asset('assets/frontend/js/pages/zoom.js') }}" defer></script>
    @stack('scripts')
    @if (trim((string) $setting('custom_footer_script')) !== '')
        {!! $setting('custom_footer_script') !!}
    @endif
</body>

</html>
