<div id="mobile-menu" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-label="Menu">

    <div class="absolute inset-0 bg-ink/60" data-close-menu></div>

    <div class="absolute left-0 top-0 bottom-0 w-[86%] max-w-sm bg-white border-r-4 border-line overflow-y-auto">

        <div class="flex items-center justify-between p-4 bg-primary text-white">

            <span class="font-display font-extrabold text-xl">Menu</span>

            <button class="p-2 rounded-lg hover:bg-white/15" data-close-menu aria-label="Close menu">
                <svg class="w-6 h-6">
                    <use href="#i-x" />
                </svg>
            </button>

        </div>

        @auth
            <div class="p-4 grid grid-cols-2 gap-2">
                <a href="{{ route('account.index') }}" class="text-center bg-ink text-white font-bold rounded-xl py-2.5">My account</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-center bg-accent font-bold rounded-xl py-2.5">Sign out</button>
                </form>
            </div>
        @else
            <div class="p-4 grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" class="text-center bg-ink text-white font-bold rounded-xl py-2.5">Sign in</a>
                <a href="{{ route('register') }}" class="text-center bg-accent font-bold rounded-xl py-2.5">Register</a>
            </div>
        @endauth

        <ul class="px-4 pb-6 divide-y-2 divide-line font-bold">

            <li><a href="{{ route('shop') }}" class="block py-3.5">All Products</a></li>

            @foreach ($menuCategories as $category)
                @if ($category->children->isEmpty())
                    <li><a href="{{ $category->url }}" class="block py-3.5">{{ $category->name }}</a></li>
                @else
                    <li>
                        <details>
                            <summary class="flex justify-between items-center py-3.5 cursor-pointer">
                                {{ $category->name }}
                                <svg class="chev w-5 h-5 transition-transform">
                                    <use href="#i-chev" />
                                </svg>
                            </summary>
                            <ul class="pb-3 pl-3 grid gap-2.5 font-medium text-[15px]">
                                <li><a href="{{ $category->url }}">All {{ $category->name }}</a></li>
                                @include('frontend.dashboard.layouts.partials.mobile_links', ['items' => $category->children])
                            </ul>
                        </details>
                    </li>
                @endif
            @endforeach

            <li><a href="{{ route('track.show') }}" class="block py-3.5">Track order</a></li>

            <li><a href="{{ route('account.orders') }}" class="block py-3.5">My orders</a></li>

            <li><a href="{{ route('account.wishlist') }}" class="block py-3.5">Wishlist</a></li>

            <li><a href="shop.html" class="block my-3 text-center bg-primary text-white rounded-xl py-3">Flash deals</a></li>

        </ul>

    </div>

</div>
