<header class="topbar sticky top-0 z-30 h-20 px-4 sm:px-8 flex items-center gap-4 bg-white/80 backdrop-blur-xl border-b border-line">

    {{-- Mobile Toggle --}}
    <button class="lg:hidden icon-btn" data-sidebar-toggle aria-label="Open menu">
        <svg class="ic ">
            <use href="#i-menu" />
        </svg>
    </button>

    {{-- Breadcrumb --}}
    <div class="hidden md:flex items-center gap-2 text-sm min-w-0">
        <a href="{{ route('admin.dashboard') }}" class="text-ink/55 hover:text-primary">Admin</a>
        <span class="text-ink/35">/</span>
        <span class="text-ink font-semibold">Dashboard</span>
    </div>

    <div class="flex items-center gap-1 ml-auto">

        {{-- Notification --}}
        @php($notifyAdmin = auth('admin')->user())
        @php($unreadNotifications = $notifyAdmin->unreadNotifications()->count())
        <div class="dropdown">
            <button class="icon-btn relative" data-dd aria-label="Notifications">
                <svg class="ic ">
                    <use href="#i-bell" />
                </svg>
                <span id="notif-dot" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-accent ring-2 ring-white {{ $unreadNotifications ? '' : 'hidden' }}"></span>
            </button>
            <div class="dropdown-menu !w-80">
                <div class="flex items-center justify-between px-3 py-2">
                    <p class="font-display font-extrabold">Notifications <span id="notif-count" class="text-xs text-ink/50 font-semibold">{{ $unreadNotifications ? '('.$unreadNotifications.')' : '' }}</span></p>
                </div>
                <div id="notif-list" class="max-h-96 overflow-y-auto">
                    @include('admin.notifications._list', ['items' => $notifyAdmin->notifications()->limit(8)->get()])
                </div>
                <div class="h-px bg-line my-1"></div>
                <div class="notif-foot">
                    <a href="{{ route('admin.notifications.index') }}" class="notif-foot-link">View all</a>
                    <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="notif-foot-btn" @disabled(! $unreadNotifications)>
                            <svg class="ic ic-sm"><use href="#i-check" /></svg><span>Mark all as read</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            (function () {
                var list = document.getElementById('notif-list'), dot = document.getElementById('notif-dot'), count = document.getElementById('notif-count');
                function refresh() {
                    fetch(@json(route('admin.notifications.feed')), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(function (r) { return r.ok ? r.json() : null })
                        .then(function (d) {
                            if (!d) return;
                            list.innerHTML = d.html;
                            dot.classList.toggle('hidden', !d.count);
                            count.textContent = d.count ? '(' + d.count + ')' : '';
                        })
                        .catch(function () { });
                }
                setInterval(function () { if (!document.hidden) refresh() }, 30000);
            })();
        </script>

        {{-- Profile --}}
        <div class="dropdown">
            <button data-dd class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-2xl hover:bg-primary-soft transition">
                @if (auth('admin')->user()->avatarUrl())
                    <img src="{{ auth('admin')->user()->avatarUrl() }}" alt="" class="w-10 h-10 rounded-xl object-cover">
                @else
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-[#1d5fd1] text-white grid place-items-center font-display font-extrabold">{{ auth('admin')->user()->initial() }}</span>
                @endif
                <span class="hidden sm:block text-left leading-tight">
                    <b class="block text-sm">{{ auth('admin')->user()->name }}</b>
                    <span class="text-xs text-ink/50">{{ auth('admin')->user()->displayTitle() }}</span>
                </span>
                <svg class="ic ic-sm hidden sm:block text-ink/40">
                    <use href="#i-chev-down" />
                </svg>
            </button>
            <div class="dropdown-menu">
                <a href="{{ route('admin.profile.edit') }}">
                    <svg class="ic ">
                        <use href="#i-user" />
                    </svg>
                    My profile
                </a>
                @if (auth('admin')->user()->hasPermission('settings:view'))
                    <a href="{{ route('admin.settings.edit') }}">
                        <svg class="ic ">
                            <use href="#i-settings" />
                        </svg>
                        Site settings
                    </a>
                @endif
                <a href="../index.html" target="_blank">
                    <svg class="ic ">
                        <use href="#i-external" />
                    </svg>
                    View store
                </a>
                <div class="h-px bg-line my-1"></div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <a href="{{ route('admin.logout') }}" class="!text-danger" onclick="event.preventDefault(); this.closest('form').submit();">
                        <svg class="ic">
                            <use href="#i-logout" />
                        </svg>
                        Logout
                    </a>
                </form>
            </div>
        </div>

    </div>

</header>
