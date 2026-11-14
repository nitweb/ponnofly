<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">

    <title>Login · {{ $setting('store_name') }} Admin</title>

    @include('admin.dashboard.layouts.favicon')

    <link rel="stylesheet" href="{{ asset('assets/admin/css/tailwind.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/inline.css') }}">

</head>

<body class="min-h-screen auth-bg text-white">

    <script src="{{ asset('assets/admin/js/admin.js') }}"></script>

    <div class="min-h-screen flex items-center justify-center p-6 sm:p-12 relative overflow-hidden">

        <div class="absolute -right-24 -bottom-24 w-[420px] h-[420px] rounded-full bg-accent/20 blur-3xl pointer-events-none"></div>

        <section class="relative z-10 w-full flex flex-col items-center">

            <div class="flex items-center gap-3 mb-8">
                @include('admin.dashboard.layouts.brand', ['large' => true])
            </div>

            <div class="w-full max-w-md bg-white text-ink rounded-[28px] p-8 sm:p-10 shadow-2xl">

                <h1 class="font-display font-extrabold text-3xl tracking-tight">Welcome back 👋</h1>

                <p class="text-ink/55 mt-2 mb-7">Sign in to access your admin dashboard.</p>

                {{-- Session Status (e.g. password reset link sent) --}}
                @if (session('status'))
                    <div class="mb-5 text-sm font-medium text-success">{{ session('status') }}</div>
                @endif

                <form class="space-y-5" method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="lbl" for="email">Email address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <svg class="ic">
                                <use href="#i-mail" />
                            </svg>
                            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username">
                        </div>
                        @error('email')
                            <p class="text-sm text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="flex justify-between">
                            <label class="lbl" for="password">Password <span class="text-danger">*</span></label>
                            {{-- @if (Route::has('admin.password.request'))
                                <a href="{{ route('admin.password.request') }}" class="text-sm font-semibold text-primary hover:underline">Forgot password?</a>
                            @endif --}}
                        </div>
                        <div class="input-group relative">
                            <svg class="ic">
                                <use href="#i-lock" />
                            </svg>
                            <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
                        </div>
                        @error('password')
                            <p class="text-sm text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me --}}
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="chk" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                        <span class="font-medium">Keep me signed in</span>
                    </label>

                    <button type="submit" class="btn btn-primary w-full !h-12 text-base">
                        Sign in
                        <svg class="ic ic-sm">
                            <use href="#i-chev-right" />
                        </svg>
                    </button>

                </form>

                <p class="text-center text-sm text-ink/50 mt-7 flex items-center justify-center gap-2">
                    <svg class="ic ic-sm text-success">
                        <use href="#i-shield" />
                    </svg>
                    Protected area · Authorized personnel only
                </p>

            </div>

        </section>

    </div>

</body>

</html>
