<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">

    <title>{{ str_replace('Nebula Mart', $setting('store_name'), $__env->yieldContent('admin_title')) }}</title>

    @include('admin.dashboard.layouts.favicon')

    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tailwind.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/inline.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css">

</head>

<body class="min-h-screen">

    <div id="overlay" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    <div class="flex">

        {{-- Sidebar --}}
        @include('admin.dashboard.layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col min-h-screen">

            {{-- Header --}}
            @include('admin.dashboard.layouts.header')

            <main class="flex-1 p-4 sm:p-8 space-y-6 max-w-[1500px] w-full mx-auto">
                @yield('admin_contents')
            </main>

            {{-- Footer --}}
            @include('admin.dashboard.layouts.footer')

        </div>

    </div>

    <div id="confirm-modal" class="modal">
        <div class="p-7 text-center">
            <span class="w-16 h-16 rounded-2xl bg-danger/10 text-danger grid place-items-center mx-auto mb-4">
                <svg class="ic ic-lg">
                    <use href="#i-trash" />
                </svg>
            </span>
            <h3 class="font-display font-extrabold text-xl">Delete item?</h3>
            <p class="text-ink/60 mt-2">You are about to delete <b data-name></b>. This action cannot be undone.</p>
            <div class="flex gap-3 mt-6">
                <button class="btn btn-ghost flex-1" data-close>Cancel</button>
                <button class="btn btn-danger flex-1" data-confirm>Yes, delete</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
    <script>
        window.notify = function (message, type) {
            if (typeof Toastify === 'undefined') {
                if (window.toast) window.toast(message, type);
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
    <script src="{{ asset('assets/admin/js/admin.js') }}"></script>

    @if (session('success') || session('error') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if (session('success'))
                    notify(@json(session('success')), 'success');
                @endif
                @if (session('error'))
                    notify(@json(session('error')), 'error');
                @endif
                @if ($errors->any())
                    notify('Please fix the errors and try again.', 'error');
                @endif
            });
        </script>
    @endif

</body>

</html>
