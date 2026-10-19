<header id="site-header" class="sticky top-0 z-50">

    @php
        $announcements = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $setting('announcement')))));
        $storeName = $setting('store_name');
        $storeInitials = \Illuminate\Support\Str::of($storeName)->explode(' ')->filter()->map(fn($word) => mb_substr($word, 0, 1))->take(2)->implode('');
    @endphp

    <!-- Announcement marquee -->
    @if ($announcements)
        <div class="bg-ink text-white overflow-hidden text-[13px] font-medium">
            <div class="flex whitespace-nowrap marquee w-max py-2">
                @foreach ([false, true] as $isCopy)
                    <div class="flex gap-10 pr-10 items-center" @if ($isCopy) aria-hidden="true" @endif>
                        @foreach ($announcements as $message)
                            <span>{{ $message }}</span>
                            <span class="text-primary">●</span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main bar -->
    <div class="bg-white border-b-4 border-line">

        <div class="max-w-7xl mx-auto px-4 lg:px-6">

            <div class="flex items-center gap-3 lg:gap-6 h-16 lg:h-20">

                <!-- Mobile menu button -->
                <button id="open-menu" class="lg:hidden -ml-2 p-2 rounded-lg hover:bg-primary-soft" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false">
                    <svg class="w-7 h-7">
                        <use href="#i-menu" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0" aria-label="{{ $storeName }} home">
                    @if ($setting('logo_header'))
                        <img src="{{ asset($setting('logo_header')) }}" alt="{{ $storeName }}" class="h-10 lg:h-12 w-auto">
                    @else
                        <span class="relative grid place-items-center w-10 h-10 lg:w-11 lg:h-11 rounded-xl bg-primary -rotate-6">
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-accent border border-white"></span>
                            <span class="absolute -bottom-1 -left-1 w-3 h-3 rounded-full bg-primary border border-white"></span>
                            <span class="logo-mark font-display font-extrabold text-white">{{ $storeInitials }}</span>
                        </span>
                        <span class="font-display font-extrabold text-2xl lg:text-[28px] tracking-tight">{{ $storeName }}</span>
                    @endif
                </a>

                <!-- Search (desktop) -->
                <form action="{{ route('search') }}" method="GET" role="search" data-suggest-url="{{ route('search.suggest') }}" class="hidden lg:flex flex-1 max-w-2xl mx-auto">
                    <div class="flex w-full rounded-2xl border border-line bg-paper overflow-hidden focus-within:shadow-md transition-shadow">
                        <label class="sr-only" for="search-q">Search products</label>
                        <input id="search-q" type="search" name="q" placeholder="Search for shoes, phones, skincare…" class="flex-1 bg-transparent px-4 py-3 text-[15px] placeholder:text-ink/50 focus:outline-none">
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 grid place-items-center transition-colors" aria-label="Search">
                            <svg class="w-5 h-5">
                                <use href="#i-search" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Actions -->
                <div class="ml-auto lg:ml-0 flex items-center gap-1 sm:gap-2">

                    <!-- Account dropdown -->
                    @php
                        $customer = auth()->user();
                    @endphp
                    <div class="drop relative hidden sm:block">
                        <a href="{{ $customer ? route('account.index') : route('login') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-primary-soft font-bold text-sm">
                            <svg class="w-6 h-6">
                                <use href="#i-user" />
                            </svg>
                            <span class="hidden xl:block leading-tight text-left">
                                <span class="block text-[11px] font-medium text-ink/60">Hello, {{ $customer ? \Illuminate\Support\Str::before($customer->name, ' ') : 'guest' }}</span>
                                Account
                            </span>
                        </a>
                        <div class="drop-panel absolute right-0 top-full pt-2 w-60">
                            <div class="bg-white border border-line rounded-2xl shadow-sm p-3">
                                @if ($customer)
                                    <a href="{{ route('account.index') }}" class="block text-center bg-primary text-white font-bold rounded-xl py-2.5 hover:bg-primary-dark">My account</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-center mt-2 bg-accent text-ink font-bold rounded-xl py-2.5 hover:brightness-95">Sign out</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="block text-center bg-primary text-white font-bold rounded-xl py-2.5 hover:bg-primary-dark">Sign
                                        in</a>
                                    <a href="{{ route('register') }}" class="block text-center mt-2 bg-accent text-ink font-bold rounded-xl py-2.5 hover:brightness-95">Create
                                        account</a>
                                @endif
                                <div class="mt-3 pt-3 border-t-2 border-dashed border-line text-sm font-medium grid gap-1">
                                    <a href="{{ route('account.orders') }}" class="px-2 py-1.5 rounded-lg hover:bg-primary-soft">My orders</a>
                                    <a href="{{ route('track.show') }}" class="px-2 py-1.5 rounded-lg hover:bg-primary-soft">Track a package</a>
                                    <a href="{{ route('account.addresses') }}" class="px-2 py-1.5 rounded-lg hover:bg-primary-soft">Saved addresses</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wishlist -->
                    <a href="{{ route('account.wishlist') }}" class="relative p-2.5 rounded-xl hover:bg-primary-soft hidden sm:grid place-items-center" aria-label="Wishlist, {{ count($wishlistIds) }} {{ count($wishlistIds) === 1 ? 'item' : 'items' }}">
                        <svg class="w-6 h-6">
                            <use href="#i-heart" />
                        </svg>
                        <span data-wish-badge class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-primary text-white text-[10px] font-bold grid place-items-center" @if (! count($wishlistIds)) hidden @endif>{{ count($wishlistIds) }}</span>
                    </a>

                    <!-- Cart -->
                    <button id="open-cart" class="flex items-center gap-2 bg-ink text-white rounded-xl pl-3 pr-3.5 py-2.5 font-bold text-sm hover:bg-primary transition-colors" aria-label="Open cart, {{ $cartSummary['count'] }} {{ $cartSummary['count'] === 1 ? 'item' : 'items' }}" aria-controls="cart-drawer" aria-expanded="false">
                        <span class="relative">
                            <svg class="w-6 h-6">
                                <use href="#i-bag" />
                            </svg>
                            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-accent text-ink text-[10px] font-extrabold grid place-items-center" data-cart-badge>{{ $cartSummary['count'] }}</span>
                        </span>
                        <span class="hidden md:block" data-cart-total>৳{{ number_format($cartSummary['subtotal'], floor($cartSummary['subtotal']) == $cartSummary['subtotal'] ? 0 : 2) }}</span>
                    </button>

                </div>

            </div>

            <!-- Search (mobile) -->
            <form action="{{ route('search') }}" method="GET" role="search" data-suggest-url="{{ route('search.suggest') }}" class="lg:hidden pb-3">
                <div class="flex rounded-xl border border-line bg-paper overflow-hidden">
                    <label class="sr-only" for="search-m">Search products</label>
                    <input id="search-m" type="search" name="q" placeholder="Search products…" class="flex-1 bg-transparent px-4 py-2.5 text-[15px] focus:outline-none">
                    <button type="submit" class="bg-primary text-white px-4 grid place-items-center" aria-label="Search">
                        <svg class="w-5 h-5">
                            <use href="#i-search" />
                        </svg>
                    </button>
                </div>
            </form>

        </div>

    </div>

    <!-- ===== Desktop nav + MEGA MENU ===== -->
    <nav class="hidden lg:block bg-white border-b border-line relative" aria-label="Main">

        <div class="max-w-7xl mx-auto px-6">

            <ul class="flex items-center gap-1 py-2">

                @foreach ($menuCategories as $category)
                    @if ($category->children->isEmpty())
                        <li>
                            <a href="{{ $category->url }}" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl font-bold text-[15px] transition-colors hover:bg-primary-soft">{{ $category->name }}</a>
                        </li>
                    @else
                        @php
                            $promos = collect($category->promos ?? [])->filter(fn ($promo) => filled($promo['title'] ?? null))->values();
                        @endphp
                        <li class="mega-item static">
                            <button class="mega-trigger flex items-center gap-1.5 px-4 py-2.5 rounded-xl font-bold text-[15px] transition-colors" aria-haspopup="true">
                                {{ $category->name }}
                                <svg class="chev w-4 h-4 transition-transform">
                                    <use href="#i-chev" />
                                </svg>
                            </button>
                            <div class="mega-panel absolute left-0 right-0 top-full z-40">
                                <div class="max-w-7xl mx-auto px-6">
                                    <div class="bg-white border border-line rounded-3xl shadow-sm mt-1 p-8 grid gap-8" style="grid-template-columns: minmax(0, 1fr){{ $promos->isNotEmpty() ? ' repeat('.$promos->count().', 16.5rem)' : '' }}">
                                        <div class="grid gap-8 content-start" style="grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr))">
                                            @foreach ($category->children as $group)
                                                <div>
                                                    <h3 class="font-display font-extrabold text-lg mb-3"><a href="{{ $group->url }}">{{ $group->name }}</a></h3>
                                                    @if ($group->children->isNotEmpty())
                                                        <ul class="grid gap-2 text-[15px]">
                                                            @include('frontend.dashboard.layouts.partials.mega_links', ['items' => $group->children])
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                            <div style="grid-column: 1 / -1">
                                                <a href="{{ $category->url }}" class="font-bold underline underline-offset-4">View all {{ $category->name }}</a>
                                            </div>
                                        </div>
                                        @foreach ($promos as $promo)
                                            @php
                                                $isNavy = ($promo['color'] ?? ($loop->first ? 'navy' : 'yellow')) === 'navy';
                                            @endphp
                                            <a href="{{ filled($promo['link'] ?? null) ? $promo['link'] : $category->url }}" class="block rounded-2xl p-5 relative overflow-hidden {{ $isNavy ? 'bg-primary text-white' : 'bg-accent' }}" style="align-self: start; min-height: 13.5rem">
                                                <span class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full {{ $isNavy ? 'bg-accent' : 'bg-primary' }}"></span>
                                                @if (filled($promo['badge'] ?? null))
                                                    <span class="relative inline-block text-xs font-bold px-2.5 py-1 rounded-full {{ $isNavy ? 'bg-white text-primary' : 'bg-ink text-white' }}">{{ $promo['badge'] }}</span>
                                                @endif
                                                <p class="relative font-display font-extrabold text-2xl leading-tight mt-3">{{ $promo['title'] }}</p>
                                                @if (filled($promo['cta'] ?? null))
                                                    <span class="relative inline-block mt-4 font-bold underline underline-offset-4">{{ $promo['cta'] }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach

                <li class="ml-auto">
                    <a href="{{ route('track.show') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-[15px] border-2 border-line hover:border-primary hover:text-primary hover:bg-primary-soft transition-colors">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" aria-hidden="true">
                            <path fill="currentColor" d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" />
                            <circle cx="7" cy="17" r="2" fill="currentColor" />
                            <circle cx="17" cy="17" r="2" fill="currentColor" />
                        </svg>
                        Track order
                    </a>
                </li>

                <li>
                    <a href="{{ route('flash-deals') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-extrabold text-[15px] bg-primary text-white hover:brightness-95">
                        Flash deals <span class="bg-accent text-ink text-[11px] px-1.5 py-0.5 rounded-md">HOT</span>
                    </a>
                </li>

            </ul>

        </div>

    </nav>

</header>
