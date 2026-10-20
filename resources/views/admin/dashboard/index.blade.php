@extends('admin.dashboard.dashboard')
@section('admin_title', 'Dashboard · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Dashboard</h1>
            <p class="text-ink/55 mt-1">Welcome back, {{ \Illuminate\Support\Str::before(auth('admin')->user()->name, ' ') }} — here’s what’s happening today.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="btn btn-ghost" data-toast="Report downloaded">
                <svg class="ic ic-sm">
                    <use href="#i-download" />
                </svg>
                Export report
            </button>
            <a href="product-add.html" class="btn btn-primary">
                <svg class="ic ic-sm">
                    <use href="#i-plus" />
                </svg>
                Add product
            </a>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <span class="is-5 stat-ic">
                    <svg class="ic ic-lg">
                        <use href="#i-dollar" />
                    </svg>
                </span>
                <span class="badge b-success !py-0">
                    <svg class="ic ic-sm">
                        <use href="#i-up" />
                    </svg>
                    12.5%
                </span>
            </div>
            <p class="text-ink/55 font-semibold mt-4">Total Revenue</p>
            <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">৳48,26,500</p>
            <div data-spark="[30,38,34,45,41,52,58,63]" data-color="#0B3A8C" class="mt-2"></div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <span class="is-11 stat-ic">
                    <svg class="ic ic-lg">
                        <use href="#i-cart" />
                    </svg>
                </span>
                <span class="badge b-success !py-0">
                    <svg class="ic ic-sm">
                        <use href="#i-up" />
                    </svg>
                    8.2%
                </span>
            </div>
            <p class="text-ink/55 font-semibold mt-4">Total Orders</p>
            <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">1,284</p>
            <div data-spark="[22,30,26,35,33,40,38,47]" data-color="#F5A800" class="mt-2"></div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <span class="is-9 stat-ic">
                    <svg class="ic ic-lg">
                        <use href="#i-users" />
                    </svg>
                </span>
                <span class="badge b-success !py-0">
                    <svg class="ic ic-sm">
                        <use href="#i-up" />
                    </svg>
                    5.4%
                </span>
            </div>
            <p class="text-ink/55 font-semibold mt-4">Customers</p>
            <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">3,642</p>
            <div data-spark="[20,24,27,25,31,34,36,40]" data-color="#0A8F6B" class="mt-2"></div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <span class="is-7 stat-ic">
                    <svg class="ic ic-lg">
                        <use href="#i-box" />
                    </svg>
                </span>
                <span class="badge b-danger !py-0">
                    <svg class="ic ic-sm">
                        <use href="#i-down" />
                    </svg>
                    2.1%
                </span>
            </div>
            <p class="text-ink/55 font-semibold mt-4">Products</p>
            <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">842</p>
            <div data-spark="[40,42,41,44,43,46,45,47]" data-color="#7C3AED" class="mt-2"></div>
        </div>

    </div>

    {{-- Revenue overview / Orders by status --}}
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Revenue overview --}}
        <section class="card xl:col-span-2">
            <div class="card-h">
                <h3>Revenue overview</h3>
                <div id="rev-sw" class="pills">
                    <span class="pill active" data-range="7d">7D</span>
                    <span class="pill" data-range="30d">30D</span>
                    <span class="pill" data-range="12m">12M</span>
                </div>
            </div>
            <div class="card-b">
                <div class="flex gap-5 mb-4 text-sm font-semibold">
                    <span class="flex items-center gap-2">
                        <i class="w-3 h-3 rounded-full bg-primary"></i>
                        Revenue
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="w-3 h-3 rounded-full bg-accent"></i>
                        Profit
                    </span>
                </div>
                <div id="rev" data-area='{"7d": {"labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"], "series": [[42000, 51000, 47500, 63000, 58000, 79000, 72000], [31000, 36000, 39000, 44000, 41000, 58000, 52000]]}, "30d": {"labels": ["1", "3", "5", "7", "9", "11", "13", "15", "17", "19", "21", "23", "25", "27", "29"], "series": [[380000, 440000, 410000, 520000, 490000, 610000, 580000, 660000, 630000, 720000, 690000, 780000, 740000, 830000, 800000], [260000, 310000, 290000, 380000, 360000, 450000, 410000, 490000, 470000, 550000, 510000, 580000, 560000, 640000, 610000]]}, "12m": {"labels": ["Nov", "Dec", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct"], "series": [[3100000, 3800000, 3400000, 3600000, 4200000, 4550000, 4900000, 5200000, 5050000, 5600000, 6100000, 6720000], [2100000, 2600000, 2400000, 2500000, 2900000, 3200000, 3400000, 3650000, 3500000, 3950000, 4300000, 4700000]]}}' data-switch="#rev-sw"></div>
            </div>
        </section>

        {{-- Orders by status --}}
        <section class="card ">
            <div class="card-h">
                <h3>Orders by status</h3>
            </div>
            <div class="card-b">
                <div class="relative w-44 h-44 mx-auto" data-donut='[{"l": "Delivered", "v": 742, "c": "#0A8F6B"}, {"l": "Shipped", "v": 221, "c": "#7C3AED"}, {"l": "Processing", "v": 176, "c": "#2563EB"}, {"l": "Pending", "v": 98, "c": "#F5A800"}, {"l": "Cancelled", "v": 47, "c": "#DC2626"}]' data-center="Orders"></div>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="is-8 w-2.5 h-2.5 rounded-full"></i>
                            Delivered
                        </span>
                        <b>742</b>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="is-6 w-2.5 h-2.5 rounded-full"></i>
                            Shipped
                        </span>
                        <b>221</b>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="is-18 w-2.5 h-2.5 rounded-full"></i>
                            Processing
                        </span>
                        <b>176</b>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="is-10 w-2.5 h-2.5 rounded-full"></i>
                            Pending
                        </span>
                        <b>98</b>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="is-2 w-2.5 h-2.5 rounded-full"></i>
                            Cancelled
                        </span>
                        <b>47</b>
                    </li>
                </ul>
            </div>
        </section>

    </div>

    {{-- Recent orders / Top selling products --}}
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Recent orders --}}
        <section class="card xl:col-span-2">
            <div class="card-h">
                <h3>Recent orders</h3>
                <a href="orders.html" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="">
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10482</a></td>
                                <td>
                                    <p class="font-semibold">Rahim Uddin</p>
                                    <p class="text-xs text-ink/50">Dhaka</p>
                                </td>
                                <td class="font-bold">৳4,250</td>
                                <td><span class="badge b-warn">Pending</span></td>
                                <td class="text-ink/60">12 Oct 2026</td>
                            </tr>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10481</a></td>
                                <td>
                                    <p class="font-semibold">Nusrat Jahan</p>
                                    <p class="text-xs text-ink/50">Chattogram</p>
                                </td>
                                <td class="font-bold">৳946</td>
                                <td><span class="badge b-info">Processing</span></td>
                                <td class="text-ink/60">12 Oct 2026</td>
                            </tr>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10480</a></td>
                                <td>
                                    <p class="font-semibold">Sabbir Hossain</p>
                                    <p class="text-xs text-ink/50">Sylhet</p>
                                </td>
                                <td class="font-bold">৳38,700</td>
                                <td><span class="badge b-purple">Shipped</span></td>
                                <td class="text-ink/60">11 Oct 2026</td>
                            </tr>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10479</a></td>
                                <td>
                                    <p class="font-semibold">Farhana Akter</p>
                                    <p class="text-xs text-ink/50">Rajshahi</p>
                                </td>
                                <td class="font-bold">৳2,150</td>
                                <td><span class="badge b-success">Delivered</span></td>
                                <td class="text-ink/60">11 Oct 2026</td>
                            </tr>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10478</a></td>
                                <td>
                                    <p class="font-semibold">Tanvir Ahmed</p>
                                    <p class="text-xs text-ink/50">Khulna</p>
                                </td>
                                <td class="font-bold">৳12,400</td>
                                <td><span class="badge b-success">Delivered</span></td>
                                <td class="text-ink/60">10 Oct 2026</td>
                            </tr>
                            <tr>
                                <td><a href="order-view.html" class="font-bold text-primary hover:underline">#NM-10477</a></td>
                                <td>
                                    <p class="font-semibold">Mim Sultana</p>
                                    <p class="text-xs text-ink/50">Dhaka</p>
                                </td>
                                <td class="font-bold">৳1,980</td>
                                <td><span class="badge b-info">Processing</span></td>
                                <td class="text-ink/60">10 Oct 2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Top selling products --}}
        <section class="card ">
            <div class="card-h">
                <h3>Top selling products</h3>
                <a href="reports.html" class="text-sm font-semibold text-primary">Report</a>
            </div>
            <div class="card-b">
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <img class="thumb" src="{{ asset('assets/admin/images/products/oxxo-premium-cotton-panjabi-for-men.webp') }}" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">OXXO Premium Cotton Panjabi for Men</p>
                            <p class="text-xs text-ink/50">182 sold</p>
                        </div>
                        <b>৳172,172</b>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb" src="{{ asset('assets/admin/images/products/vaseline-gluta-hya-dewy-radiance-lotion-200ml.webp') }}" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">Vaseline Gluta-Hya Dewy Radiance Lotion 200ml</p>
                            <p class="text-xs text-ink/50">164 sold</p>
                        </div>
                        <b>৳101,680</b>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb" src="{{ asset('assets/admin/images/products/omron-hem-7156t.webp') }}" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">Omron HEM-7156T BP Monitor</p>
                            <p class="text-xs text-ink/50">97 sold</p>
                        </div>
                        <b>৳504,400</b>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb" src="{{ asset('assets/admin/images/products/electronics/sony-wh-1000xm4.webp') }}" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">Sony WH-1000XM4 Headphones</p>
                            <p class="text-xs text-ink/50">41 sold</p>
                        </div>
                        <b>৳1,332,500</b>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb" src="{{ asset('assets/admin/images/products/fingertip-pulse-oximeter-oled-version-jumper-jpd-500d.webp') }}" alt="">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold truncate">Fingertip Pulse Oximeter OLED – Jumper JPD-500D</p>
                            <p class="text-xs text-ink/50">138 sold</p>
                        </div>
                        <b>৳255,300</b>
                    </li>
                </ul>
            </div>
        </section>

    </div>

    {{-- Low stock alert / Courier split / Quick actions --}}
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Low stock alert --}}
        <section class="card ">
            <div class="card-h">
                <h3>Low stock alert</h3>
            </div>
            <div class="card-b">
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <img class="thumb !w-10 !h-10" src="{{asset('assets/admin/images/products/accu-chek-instant-blood-glucose-meter-bluetooth.webp')}}" alt="">
                        <p class="flex-1 min-w-0 truncate font-semibold">Accu-Chek Instant Glucose Meter Bluetooth</p>
                        <span class="badge b-danger">0 left</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb !w-10 !h-10" src="{{asset('assets/admin/images/products/omron-hem-7156t.webp')}}" alt="">
                        <p class="flex-1 min-w-0 truncate font-semibold">Omron HEM-7156T BP Monitor</p>
                        <span class="badge b-warn">3 left</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb !w-10 !h-10" src="{{asset('assets/admin/images/products/electronics/logitech-g502-hero.webp')}}" alt="">
                        <p class="flex-1 min-w-0 truncate font-semibold">Logitech G502 HERO Mouse</p>
                        <span class="badge b-danger">0 left</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <img class="thumb !w-10 !h-10" src="{{asset('assets/admin/images/products/himalaya-moisturizing-aloe-vera-face-gel-100ml.webp')}}" alt="">
                        <p class="flex-1 min-w-0 truncate font-semibold">Himalaya Aloe Vera Face Gel 100ml</p>
                        <span class="badge b-warn">9 left</span>
                    </li>
                </ul>
                <a href="products.html" class="btn btn-ghost btn-sm w-full mt-5">Manage inventory</a>
            </div>
        </section>

        {{-- Courier split --}}
        <section class="card ">
            <div class="card-h">
                <h3>Courier split</h3>
            </div>
            <div class="card-b">
                <div class="space-y-5">
                    <div>
                        <div class="flex justify-between mb-1.5">
                            <b>Pathao</b>
                            <span class="text-ink/55">46%</span>
                        </div>
                        <div class="bar-track"><i class="is-19"></i></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1.5">
                            <b>Steadfast</b>
                            <span class="text-ink/55">34%</span>
                        </div>
                        <div class="bar-track"><i class="is-20"></i></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1.5">
                            <b>RedX</b>
                            <span class="text-ink/55">20%</span>
                        </div>
                        <div class="bar-track"><i class="is-21"></i></div>
                    </div>
                </div>
                <p class="text-xs text-ink/50 mt-5">Cash on Delivery only · based on last 30 days</p>
            </div>
        </section>

        {{-- Quick actions --}}
        <section class="card ">
            <div class="card-h">
                <h3>Quick actions</h3>
            </div>
            <div class="card-b">
                <div class="grid grid-cols-2 gap-3">
                    <a href="product-add.html" class="rounded-2xl border border-line p-4 hover:border-primary hover:bg-primary-soft transition">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center mb-3">
                            <svg class="ic ">
                                <use href="#i-plus" />
                            </svg>
                        </span>
                        <b>Add product</b>
                    </a>
                    <a href="coupon-add.html" class="rounded-2xl border border-line p-4 hover:border-primary hover:bg-primary-soft transition">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center mb-3">
                            <svg class="ic ">
                                <use href="#i-ticket" />
                            </svg>
                        </span>
                        <b>New coupon</b>
                    </a>
                    <a href="flash-deal-add.html" class="rounded-2xl border border-line p-4 hover:border-primary hover:bg-primary-soft transition">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center mb-3">
                            <svg class="ic ">
                                <use href="#i-zap" />
                            </svg>
                        </span>
                        <b>Flash deal</b>
                    </a>
                    <a href="banner-add.html" class="rounded-2xl border border-line p-4 hover:border-primary hover:bg-primary-soft transition">
                        <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center mb-3">
                            <svg class="ic ">
                                <use href="#i-image" />
                            </svg>
                        </span>
                        <b>Add banner</b>
                    </a>
                </div>
            </div>
        </section>
        
    </div>

@endsection
