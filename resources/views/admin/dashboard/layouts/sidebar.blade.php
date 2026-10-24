<aside id="sidebar" class="sidebar text-white flex flex-col lg:sticky lg:top-0 lg:h-screen shrink-0">

    <div class="px-6 h-20 flex items-center gap-3 shrink-0">

        <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent to-amber-300 grid place-items-center text-[#1b1300] font-display font-extrabold text-base tracking-tighter shadow-lg">NM</span>

        <div class="leading-tight">
            <p class="font-display font-extrabold text-lg tracking-tight">Nebula Mart</p>
            <p class="text-[11px] tracking-[.18em] uppercase text-white/45 font-bold">Admin Console</p>
        </div>

        <button class="lg:hidden ml-auto icon-btn !text-white/70" data-sidebar-toggle aria-label="Close menu">
            <svg class="ic ">
                <use href="#i-x" />
            </svg>
        </button>

    </div>

    @php($navAdmin = auth('admin')->user())
    <nav class="flex-1 overflow-y-auto px-4 pb-6">

        <p class="nav-label">Main</p>

        @if ($navAdmin->hasPermission('dashboard:view'))
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-home" />
            </svg>
            <span>Dashboard</span>
        </a>
        @endif

        <p class="nav-label">Catalog</p>

        @if ($navAdmin->hasPermission('products:view'))
        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-box" />
            </svg>
            <span>Products</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('categories:view'))
        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-layers" />
            </svg>
            <span>Categories</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('brands:view'))
        <a href="{{ route('admin.brands.index') }}" class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-award" />
            </svg>
            <span>Brands</span>
        </a>
        @endif

        <p class="nav-label">Sales</p>

        @php($pendingOrders = \App\Models\Order::where('status', 'pending')->count())
        @if ($navAdmin->hasPermission('orders:view'))
        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-cart" />
            </svg>
            <span>Orders</span>
            @if ($pendingOrders)
                <span class="nav-badge">{{ $pendingOrders }}</span>
            @endif
        </a>
        @endif

        @if ($navAdmin->hasPermission('orders:view'))
        @php($pendingReturns = \App\Models\OrderReturn::where('status', 'pending')->count())
        <a href="{{ route('admin.return-requests.index') }}" class="nav-link {{ request()->routeIs('admin.return-requests.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-refresh" />
            </svg>
            <span>Returns</span>
            @if ($pendingReturns)
                <span class="nav-badge">{{ $pendingReturns }}</span>
            @endif
        </a>
        @endif

        @if ($navAdmin->hasPermission('customers:view'))
        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-users" />
            </svg>
            <span>Customers</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('coupons:view'))
        <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-ticket" />
            </svg>
            <span>Coupons</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('flash:view'))
        <a href="{{ route('admin.flash-deals.index') }}" class="nav-link {{ request()->routeIs('admin.flash-deals.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-zap" />
            </svg>
            <span>Flash Deals</span>
        </a>
        @endif

        <p class="nav-label">Content</p>

        @if ($navAdmin->hasPermission('banners:view'))
        <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-image" />
            </svg>
            <span>Banners & Sliders</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('pages:view'))
        <a href="{{ route('admin.about.edit') }}" class="nav-link {{ request()->routeIs('admin.about.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-file" />
            </svg>
            <span>About</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('pages:view'))
        <a href="{{ route('admin.privacy.edit') }}" class="nav-link {{ request()->routeIs('admin.privacy.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-shield" />
            </svg>
            <span>Privacy policy</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('pages:view'))
        <a href="{{ route('admin.terms.edit') }}" class="nav-link {{ request()->routeIs('admin.terms.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-file" />
            </svg>
            <span>Terms of service</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('pages:view'))
        <a href="{{ route('admin.shipping.edit') }}" class="nav-link {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-truck" />
            </svg>
            <span>Shipping info</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('pages:view'))
        <a href="{{ route('admin.returns.edit') }}" class="nav-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-refresh" />
            </svg>
            <span>Returns &amp; refunds</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('faqs:view'))
        <a href="{{ route('admin.faqs.index') }}" class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-help" />
            </svg>
            <span>FAQs</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('messages:view'))
        <a href="{{ route('admin.messages.index') }}" class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-mail" />
            </svg>
            <span>Messages</span>
            @if (($unreadMessages = \App\Models\ContactMessage::unread()->count()) > 0)
                <span class="nav-badge">{{ $unreadMessages }}</span>
            @endif
        </a>
        @endif

        <p class="nav-label">Insights</p>

        @if ($navAdmin->hasPermission('reports:view'))
        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-chart" />
            </svg>
            <span>Reports</span>
        </a>
        @endif

        @if ($navAdmin->hasPermission('expenses:view'))
        <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*', 'admin.expense-categories.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-wallet" />
            </svg>
            <span>Expenses</span>
        </a>
        @endif

        <p class="nav-label">Access Control</p>
        @if ($navAdmin->hasPermission('roles:view'))
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <svg class="ic ">
                    <use href="#i-shield" />
                </svg>
                <span>Roles & Permissions</span>
            </a>
        @endif

        @if ($navAdmin->hasPermission('staff:view'))
            <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <svg class="ic ">
                    <use href="#i-users" />
                </svg>
                <span>Staff Members</span>
            </a>
        @endif

        <p class="nav-label">System</p>

        @if ($navAdmin->hasPermission('settings:view'))
        <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-settings" />
            </svg>
            <span>Site Settings</span>
        </a>
        @endif

        <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
            <svg class="ic ">
                <use href="#i-user" />
            </svg>
            <span>My Profile</span>
        </a>

    </nav>

    <div class="p-4 shrink-0">
        <div class="is-1 rounded-2xl p-4 glow">
            <p class="font-display font-extrabold">View your store</p>
            <p class="text-xs text-white/60 mt-1 mb-3">See changes live on the storefront.</p>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-accent btn-sm w-full">
                Open store
                <svg class="ic ic-sm">
                    <use href="#i-external" />
                </svg>
            </a>
        </div>
    </div>

</aside>
