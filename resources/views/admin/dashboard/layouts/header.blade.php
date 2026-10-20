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

    {{-- Search --}}
    <div class="input-group ml-auto w-full max-w-md hidden sm:block">
        <svg class="ic ">
            <use href="#i-search" />
        </svg>
        <input class="input !bg-bg" placeholder="Search products, orders, customers…" aria-label="Search">
    </div>

    <div class="flex items-center gap-1 ml-auto sm:ml-0">

        {{-- Notification --}}
        <div class="dropdown">
            <button class="icon-btn relative" data-dd aria-label="Notifications">
                <svg class="ic ">
                    <use href="#i-bell" />
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-accent ring-2 ring-white"></span>
            </button>
            <div class="dropdown-menu !w-80">
                <p class="px-3 py-2 font-display font-extrabold">Notifications</p>
                <a href="orders.html">
                    <span class="w-9 h-9 rounded-xl bg-primary/10 text-primary grid place-items-center">
                        <svg class="ic ">
                            <use href="#i-cart" />
                        </svg>
                    </span>
                    <span class="text-sm"><b>New order #NM-10482</b><br><span class="text-ink/50">2 min ago · ৳4,250</span></span>
                </a>
                <a href="products.html">
                    <span class="w-9 h-9 rounded-xl bg-danger/10 text-danger grid place-items-center">
                        <svg class="ic ">
                            <use href="#i-alert" />
                        </svg>
                    </span>
                    <span class="text-sm"><b>Low stock: Omron HEM-7156T</b><br><span class="text-ink/50">Only 3 left</span></span>
                </a>
                <a href="messages.html">
                    <span class="w-9 h-9 rounded-xl bg-accent/20 text-accent-dark grid place-items-center">
                        <svg class="ic ">
                            <use href="#i-mail" />
                        </svg>
                    </span>
                    <span class="text-sm"><b>New contact message</b><br><span class="text-ink/50">Rahim Uddin · 1h ago</span></span>
                </a>
            </div>
        </div>

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
