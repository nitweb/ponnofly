@extends('frontend.dashboard.dashboard')

@section('page', 'account')

@section('frontend_contents')
    @php
        $nav = [
            ['account.index', 'Overview', 'account.index'],
            ['account.orders', 'My orders', 'account.orders*'],
            ['account.wishlist', 'Wishlist', 'account.wishlist'],
            ['account.addresses', 'Saved addresses', 'account.addresses'],
            ['account.profile', 'Profile & password', 'account.profile'],
        ];
    @endphp

    <main id="main">
        <section class="bg-primary border-b-4 border-line relative overflow-hidden">
            <span class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-primary"></span>
            <span class="absolute right-40 -bottom-14 w-40 h-40 rounded-full bg-accent"></span>
            <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 relative text-white">
                <nav aria-label="Breadcrumb" class="text-sm">
                    <ol class="flex gap-2">
                        <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page" class="font-bold">@yield('account_title', 'My account')</li>
                    </ol>
                </nav>
                <h1 class="font-display font-extrabold text-4xl lg:text-5xl mt-3">@yield('account_title', 'My account')</h1>
                <p class="mt-2 max-w-xl opacity-90">@yield('account_sub', 'Manage your orders, addresses and wishlist.')</p>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10">
            <div class="grid lg:grid-cols-[260px_1fr] gap-8 items-start">
                <nav aria-label="Account" class="bg-white border border-line rounded-3xl p-3 grid gap-1">
                    @foreach ($nav as [$route, $label, $pattern])
                        <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                            class="block px-4 py-2.5 rounded-xl font-bold {{ request()->routeIs($pattern) ? 'bg-primary text-white' : 'hover:bg-primary-soft' }}">{{ $label }}</a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2.5 rounded-xl font-bold text-ink/60 hover:bg-primary-soft">Sign out</button>
                    </form>
                </nav>

                <div class="min-w-0">
                    @if (session('status'))
                        <p class="mb-5 rounded-xl bg-primary-soft border border-line px-4 py-3 text-sm font-bold text-success" role="status">{{ session('status') }}</p>
                    @endif
                    @if (session('error'))
                        <p class="mb-5 rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-bold text-danger" role="alert">{{ session('error') }}</p>
                    @endif

                    @yield('account_body')
                </div>
            </div>
        </div>
    </main>
@endsection
