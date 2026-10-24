@extends('admin.dashboard.dashboard')
@section('admin_title', 'Site Settings · Nebula Mart Admin')
@section('admin_contents')

    @php
        $value = fn (string $key) => old($key, $setting($key));
        $checked = fn (string $key) => session()->hasOldInput() ? (bool) old($key) : $setting($key) === '1';
        $imageSrc = fn (string $key) => $setting($key) ? asset($setting($key)) : asset('assets/admin/img/no-image.svg');
        $shippingPage = \App\Models\ShippingPage::instance();
        $fonts = ['Plus Jakarta Sans', 'Inter'];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Site Settings</h1>
            <p class="text-ink/55 mt-1">Configure your store, delivery, couriers and branding.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="card p-4 text-danger text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="tabs" data-tabs="#panes">
                <span class="tab active" data-tab="general"><svg class="ic ic-sm"><use href="#i-settings" /></svg>General</span>
                <span class="tab" data-tab="contact"><svg class="ic ic-sm"><use href="#i-phone" /></svg>Contact & Social</span>
                <span class="tab" data-tab="shipping"><svg class="ic ic-sm"><use href="#i-truck" /></svg>Shipping & Payment</span>
                <span class="tab" data-tab="couriers"><svg class="ic ic-sm"><use href="#i-box" /></svg>Couriers</span>
                <span class="tab" data-tab="chat"><svg class="ic ic-sm"><use href="#i-mail" /></svg>Chat launcher</span>
                <span class="tab" data-tab="seo"><svg class="ic ic-sm"><use href="#i-globe" /></svg>SEO & Analytics</span>
                <span class="tab" data-tab="theme"><svg class="ic ic-sm"><use href="#i-image" /></svg>Theme</span>
            </div>
        </div>

        <div id="panes" class="space-y-6">

            {{-- General --}}
            <div class="tab-pane active space-y-6" id="general">
                <section class="card">
                    <div class="card-h"><h3>Store identity</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="lbl" for="store_name">Store name</label>
                                <input class="input" id="store_name" name="store_name" type="text" value="{{ $value('store_name') }}">
                            </div>
                            <div>
                                <label class="lbl" for="tagline">Tagline</label>
                                <input class="input" id="tagline" name="tagline" type="text" value="{{ $value('tagline') }}">
                            </div>
                            <div>
                                <label class="lbl" for="currency">Currency</label>
                                <select class="select" id="currency" name="currency">
                                    <option value="BDT" @selected($value('currency') === 'BDT')>BDT (৳)</option>
                                    <option value="USD" @selected($value('currency') === 'USD')>USD ($)</option>
                                </select>
                            </div>
                            <div>
                                <label class="lbl" for="timezone">Timezone</label>
                                <select class="select" id="timezone" name="timezone">
                                    @foreach (\App\Models\Setting::TIMEZONES as $zone => $zoneLabel)
                                        <option value="{{ $zone }}" @selected($value('timezone') === $zone)>{{ $zoneLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @foreach ([
                                ['logo_header', 'lg-h', 'Header logo', 'Shown in the site header · PNG / SVG', 'w-24 h-16'],
                                ['logo_footer', 'lg-f', 'Footer logo', 'Shown in the site footer · light version recommended', 'w-24 h-16'],
                            ] as [$key, $id, $label, $hint, $size])
                                <div>
                                    <label class="lbl">{{ $label }}</label>
                                    <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                                        <img id="{{ $id }}" src="{{ $imageSrc($key) }}" class="{{ $size }} rounded-xl object-contain bg-white border border-line shrink-0" alt="">
                                        <div class="text-center sm:text-left">
                                            <p class="font-bold">{{ $label }}</p>
                                            <p class="text-xs text-ink/50 mt-1 mb-3">{{ $hint }}</p>
                                            <div class="flex gap-2 justify-center sm:justify-start">
                                                <label class="btn btn-primary btn-sm cursor-pointer">
                                                    <svg class="ic ic-sm"><use href="#i-upload" /></svg>Upload
                                                    <input type="file" name="{{ $key }}" class="hidden" accept="image/*" data-preview="#{{ $id }}">
                                                </label>
                                                <button type="button" class="btn btn-ghost btn-sm" data-reset-img="#{{ $id }}">Remove</button>
                                            </div>
                                            <input type="hidden" name="remove_{{ $key }}" value="0" data-remove>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="sm:col-span-2">
                                <label class="lbl">Favicon</label>
                                <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                                    <img id="fv" src="{{ $imageSrc('favicon') }}" class="w-16 h-16 rounded-xl object-contain bg-white border border-line shrink-0" alt="">
                                    <div class="text-center sm:text-left">
                                        <p class="font-bold">Favicon</p>
                                        <p class="text-xs text-ink/50 mt-1 mb-3">32×32 PNG / ICO</p>
                                        <div class="flex gap-2 justify-center sm:justify-start">
                                            <label class="btn btn-primary btn-sm cursor-pointer">
                                                <svg class="ic ic-sm"><use href="#i-upload" /></svg>Upload
                                                <input type="file" name="favicon" class="hidden" accept="image/*,.ico" data-preview="#fv">
                                            </label>
                                            <button type="button" class="btn btn-ghost btn-sm" data-reset-img="#fv">Remove</button>
                                        </div>
                                        <input type="hidden" name="remove_favicon" value="0" data-remove>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-h"><h3>Store status</h3></div>
                    <div class="card-b">
                        <div class="space-y-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold">Store open</p>
                                    <p class="text-xs text-ink/50">Turn off to show a maintenance page</p>
                                </div>
                                <label class="switch"><input type="checkbox" name="store_open" value="1" @checked($checked('store_open'))><i></i></label>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div><p class="font-semibold">Allow guest checkout</p></div>
                                <label class="switch"><input type="checkbox" name="guest_checkout" value="1" @checked($checked('guest_checkout'))><i></i></label>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div><p class="font-semibold">Show out-of-stock products</p></div>
                                <label class="switch"><input type="checkbox" name="show_out_of_stock" value="1" @checked($checked('show_out_of_stock'))><i></i></label>
                            </div>
                            <div>
                                <label class="lbl" for="announcement">Announcement bar text</label>
                                <textarea class="textarea" id="announcement" name="announcement" rows="3">{{ $value('announcement') }}</textarea>
                                <p class="hint mt-1">One message per line. Leave empty to hide the bar.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Contact & Social --}}
            <div class="tab-pane space-y-6" id="contact">
                <section class="card">
                    <div class="card-h"><h3>Contact details</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="lbl" for="support_email">Support email</label>
                                <div class="input-group"><svg class="ic"><use href="#i-mail" /></svg><input class="input" id="support_email" name="support_email" type="email" value="{{ $value('support_email') }}"></div>
                            </div>
                            <div>
                                <label class="lbl" for="phone">Phone</label>
                                <div class="input-group"><svg class="ic"><use href="#i-phone" /></svg><input class="input" id="phone" name="phone" type="text" value="{{ $value('phone') }}"></div>
                            </div>
                            <div>
                                <label class="lbl" for="opening_hours">Opening hours</label>
                                <div class="input-group"><svg class="ic"><use href="#i-clock" /></svg><input class="input" id="opening_hours" name="opening_hours" type="text" value="{{ $value('opening_hours') }}"></div>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl" for="address">Office address</label>
                                <textarea class="textarea" id="address" name="address" rows="2">{{ $value('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-h"><h3>Social links</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $key => $label)
                                <div>
                                    <label class="lbl" for="{{ $key }}">{{ $label }}</label>
                                    <div class="input-group"><svg class="ic"><use href="#i-link" /></svg><input class="input" id="{{ $key }}" name="{{ $key }}" type="url" value="{{ $value($key) }}"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            {{-- Shipping & Payment --}}
            <div class="tab-pane space-y-6" id="shipping">
                <section class="card">
                    <div class="card-h"><h3>Delivery zones</h3></div>
                    <div>
                        <table class="tbl">
                            <thead>
                                <tr><th>Zone</th><th>Charge</th><th>Delivery time</th><th>On</th></tr>
                            </thead>
                            <tbody>
                                @foreach (\App\Models\Setting::ZONES as $zone => $label)
                                    <tr>
                                        <td><b>{{ $label }}</b><p class="text-xs text-ink/50">{{ $shippingPage->zoneArea($zone)['areas'] }}</p></td>
                                        <td>
                                            <div class="addon"><span>৳</span><input class="input" name="{{ $zone }}_charge" type="number" min="0" value="{{ $value($zone.'_charge') }}" placeholder="0"></div>
                                        </td>
                                        <td><input class="input" name="{{ $zone }}_time" value="{{ $value($zone.'_time') }}"></td>
                                        <td><label class="switch"><input type="checkbox" name="{{ $zone }}_on" value="1" @checked($checked($zone.'_on'))><i></i></label></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="card">
                    <div class="card-h"><h3>Rules & payment</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="lbl" for="free_delivery_above">Free delivery above</label>
                                <div class="addon"><span>৳</span><input class="input" id="free_delivery_above" name="free_delivery_above" type="number" min="0" value="{{ $value('free_delivery_above') }}" placeholder="0"></div>
                            </div>
                            <div>
                                <label class="lbl" for="min_order">Minimum order</label>
                                <div class="addon"><span>৳</span><input class="input" id="min_order" name="min_order" type="number" min="0" value="{{ $value('min_order') }}" placeholder="0"></div>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5 mt-5">
                            <div>
                                <label class="lbl" for="return_days">Return window (days)</label>
                                <input class="input" id="return_days" name="return_days" type="number" min="1" max="90" value="{{ $value('return_days') }}" required>
                            </div>
                            <div>
                                <label class="lbl" for="refund_time">Refund time</label>
                                <input class="input" id="refund_time" name="refund_time" value="{{ $value('refund_time') }}" placeholder="3–5 working days" required>
                            </div>
                        </div>
                        <div class="space-y-5 mt-6 pt-6 border-t border-line">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold">Cash on Delivery</p>
                                    <p class="text-xs text-ink/50">Currently the only payment method</p>
                                </div>
                                <label class="switch"><input type="checkbox" name="cod" value="1" @checked($checked('cod'))><i></i></label>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold">bKash / Nagad</p>
                                    <p class="text-xs text-ink/50">Disabled for this store</p>
                                </div>
                                <label class="switch"><input type="checkbox" name="mfs" value="1" @checked($checked('mfs'))><i></i></label>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold">Allow returns</p>
                                    <p class="text-xs text-ink/50">{{ $value('return_days') }}-day return window</p>
                                </div>
                                <label class="switch"><input type="checkbox" name="allow_returns" value="1" @checked($checked('allow_returns'))><i></i></label>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Couriers --}}
            <div class="tab-pane space-y-6" id="couriers">
                @foreach (\App\Models\Setting::COURIERS as $courier => $label)
                    <section class="card">
                        <div class="card-h">
                            <h3>{{ $label }}</h3>
                            <label class="switch"><input type="checkbox" name="{{ $courier }}_enabled" value="1" @checked($checked($courier.'_enabled'))><i></i></label>
                        </div>
                        <div class="card-b">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="lbl" for="{{ $courier }}_client_id">Client ID</label>
                                    <input class="input" id="{{ $courier }}_client_id" name="{{ $courier }}_client_id" type="password" value="" autocomplete="new-password" placeholder="{{ filled($setting($courier.'_client_id')) ? '•••••••• saved · leave blank to keep' : '' }}">
                                </div>
                                <div>
                                    <label class="lbl" for="{{ $courier }}_client_secret">Client secret</label>
                                    <input class="input" id="{{ $courier }}_client_secret" name="{{ $courier }}_client_secret" type="password" value="" autocomplete="new-password" placeholder="{{ filled($setting($courier.'_client_secret')) ? '•••••••• saved · leave blank to keep' : '' }}">
                                </div>
                                <div>
                                    <label class="lbl" for="{{ $courier }}_store_id">Store ID</label>
                                    <input class="input" id="{{ $courier }}_store_id" name="{{ $courier }}_store_id" type="text" value="{{ $value($courier.'_store_id') }}">
                                </div>
                                <div>
                                    <label class="lbl" for="{{ $courier }}_mode">Mode</label>
                                    <select class="select" id="{{ $courier }}_mode" name="{{ $courier }}_mode">
                                        <option value="Live" @selected($value($courier.'_mode') === 'Live')>Live</option>
                                        <option value="Sandbox" @selected($value($courier.'_mode') === 'Sandbox')>Sandbox</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- Chat launcher --}}
            <div class="tab-pane space-y-6" id="chat">
                <section class="card">
                    <div class="card-h"><h3>Floating chat launcher</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div class="flex items-center justify-between gap-4 sm:col-span-2">
                                <div><p class="font-semibold">Show launcher</p></div>
                                <label class="switch"><input type="checkbox" name="chat_show" value="1" @checked($checked('chat_show'))><i></i></label>
                            </div>
                            <div>
                                <label class="lbl" for="chat_call">Call number</label>
                                <div class="input-group"><svg class="ic"><use href="#i-phone" /></svg><input class="input" id="chat_call" name="chat_call" type="text" value="{{ $value('chat_call') }}"></div>
                            </div>
                            <div>
                                <label class="lbl" for="chat_whatsapp">WhatsApp number</label>
                                <div class="input-group"><svg class="ic"><use href="#i-phone" /></svg><input class="input" id="chat_whatsapp" name="chat_whatsapp" type="text" value="{{ $value('chat_whatsapp') }}"></div>
                                <p class="hint mt-1">Country code + number, no + or spaces.</p>
                            </div>
                            <div>
                                <label class="lbl" for="chat_messenger">Messenger page</label>
                                <div class="input-group"><svg class="ic"><use href="#i-link" /></svg><input class="input" id="chat_messenger" name="chat_messenger" type="text" value="{{ $value('chat_messenger') }}"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- SEO & Analytics --}}
            <div class="tab-pane space-y-6" id="seo">
                <section class="card">
                    <div class="card-h"><h3>Search engines</h3></div>
                    <div class="card-b">
                        <div class="grid gap-5">
                            <div>
                                <label class="lbl" for="meta_title">Default meta title</label>
                                <input class="input" id="meta_title" name="meta_title" type="text" value="{{ $value('meta_title') }}">
                            </div>
                            <div>
                                <label class="lbl" for="meta_description">Default meta description</label>
                                <textarea class="textarea" id="meta_description" name="meta_description" rows="3">{{ $value('meta_description') }}</textarea>
                            </div>
                            <div>
                                <label class="lbl" for="canonical_domain">Canonical domain</label>
                                <input class="input" id="canonical_domain" name="canonical_domain" type="url" value="{{ $value('canonical_domain') }}" placeholder="https://nebulamart.com">
                            </div>
                            <div>
                                <label class="lbl">Social share image (OG image)</label>
                                <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                                    <img id="og-img" src="{{ $imageSrc('og_image') }}" class="w-40 h-[84px] rounded-xl object-cover bg-white border border-line shrink-0" alt="">
                                    <div class="text-center sm:text-left">
                                        <p class="font-bold">Default share image</p>
                                        <p class="text-xs text-ink/50 mt-1 mb-3">Exactly 1200 × 630 px · PNG / JPG / WebP · max 2 MB</p>
                                        <div class="flex gap-2 justify-center sm:justify-start">
                                            <label class="btn btn-primary btn-sm cursor-pointer">
                                                <svg class="ic ic-sm"><use href="#i-upload" /></svg>Upload
                                                <input type="file" name="og_image" class="hidden" accept="image/png,image/jpeg,image/webp" data-preview="#og-img">
                                            </label>
                                            <button type="button" class="btn btn-ghost btn-sm" data-reset-img="#og-img">Remove</button>
                                        </div>
                                        <input type="hidden" name="remove_og_image" value="0" data-remove>
                                    </div>
                                </div>
                                @error('og_image')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-h"><h3>Tracking</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="lbl" for="ga_id">Google Analytics ID</label>
                                <input class="input" id="ga_id" name="ga_id" type="text" value="{{ $value('ga_id') }}" placeholder="G-XXXXXXXXXX">
                            </div>
                            <div>
                                <label class="lbl" for="pixel_id">Facebook Pixel ID</label>
                                <input class="input" id="pixel_id" name="pixel_id" type="text" value="{{ $value('pixel_id') }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl" for="gsc_code">Google Search Console code</label>
                                <input class="input" id="gsc_code" name="gsc_code" type="text" value="{{ $value('gsc_code') }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl" for="custom_header_script">Custom header script</label>
                                <textarea class="textarea font-mono text-xs" id="custom_header_script" name="custom_header_script" rows="6" spellcheck="false" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ $value('custom_header_script') }}</textarea>
                                <p class="hint">Added inside &lt;head&gt; on every storefront page. Only paste code you trust.</p>
                                @error('custom_header_script')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl" for="custom_footer_script">Custom footer script</label>
                                <textarea class="textarea font-mono text-xs" id="custom_footer_script" name="custom_footer_script" rows="6" spellcheck="false" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ $value('custom_footer_script') }}</textarea>
                                <p class="hint">Added just before &lt;/body&gt; on every storefront page. Only paste code you trust.</p>
                                @error('custom_footer_script')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Theme --}}
            <div class="tab-pane space-y-6" id="theme">
                <section class="card">
                    <div class="card-h"><h3>Brand colors</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            @foreach (['color_primary' => 'Primary', 'color_accent' => 'Accent', 'color_text' => 'Text', 'color_background' => 'Page background'] as $key => $label)
                                <div>
                                    <label class="lbl" for="hx_{{ $key }}">{{ $label }}</label>
                                    <div class="flex gap-3">
                                        <input type="color" value="{{ $value($key) }}" data-hex="#hx_{{ $key }}" class="w-12 h-11 rounded-xl border border-line p-1 bg-white cursor-pointer">
                                        <input class="input font-mono" id="hx_{{ $key }}" name="{{ $key }}" value="{{ $value($key) }}" maxlength="7">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="hint mt-4">Applied on the storefront automatically.</p>
                    </div>
                </section>

                <section class="card">
                    <div class="card-h"><h3>Fonts</h3></div>
                    <div class="card-b">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="lbl" for="font_heading">Heading font</label>
                                <select class="select" id="font_heading" name="font_heading">
                                    @foreach ($fonts as $font)
                                        <option value="{{ $font }}" @selected($value('font_heading') === $font)>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="lbl" for="font_body">Body font</label>
                                <select class="select" id="font_body" name="font_body">
                                    @foreach ($fonts as $font)
                                        <option value="{{ $font }}" @selected($value('font_body') === $font)>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>

        <div class="savebar">
            <button type="reset" class="btn btn-ghost">Reset</button>
            <button type="submit" class="btn btn-primary">
                <svg class="ic ic-sm"><use href="#i-check" /></svg>Save settings
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('click', function (e) {
            var button = e.target.closest('[data-reset-img]');
            if (!button) return;
            var flag = button.closest('.drop').querySelector('input[data-remove]');
            if (flag) flag.value = 1;
        });
        document.addEventListener('change', function (e) {
            if (!e.target.matches('.drop input[type=file]')) return;
            var flag = e.target.closest('.drop').querySelector('input[data-remove]');
            if (flag) flag.value = 0;
        });
    </script>

@endsection
