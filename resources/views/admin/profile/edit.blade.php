@extends('admin.dashboard.dashboard')
@section('admin_title', 'My Profile · Nebula Mart Admin')
@section('admin_contents')

    @php
        $field = fn (string $key) => old($key, $admin->{$key});
        $openTab = session('tab', $errors->has('current_password') || $errors->has('password') ? 'security' : 'info');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">My Profile</h1>
            <p class="text-ink/55 mt-1">Manage your account, password and security.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost !text-danger"><svg class="ic ic-sm"><use href="#i-logout" /></svg>Logout</button>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <div class="card p-4 text-danger text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid xl:grid-cols-3 gap-6 items-start">

        <div class="space-y-6">
            <div class="card overflow-hidden">
                <div class="h-28 auth-bg"></div>
                <div class="px-6 pb-6 text-center -mt-12">
                    <div class="relative inline-block">
                        <img id="avatar-preview" src="{{ $admin->avatarUrl() }}" alt="{{ $admin->name }}" class="w-24 h-24 rounded-3xl object-cover ring-4 ring-white shadow-lg {{ $admin->avatar ? '' : 'hidden' }}">
                        <span id="avatar-initial" class="w-24 h-24 rounded-3xl bg-gradient-to-br from-accent to-amber-300 text-[#1b1300] grid place-items-center font-display font-extrabold text-4xl ring-4 ring-white shadow-lg {{ $admin->avatar ? 'hidden' : '' }}">{{ $admin->initial() }}</span>
                        <label class="absolute -right-1 -bottom-1 w-9 h-9 rounded-xl bg-primary text-white grid place-items-center cursor-pointer ring-2 ring-white" title="Change photo">
                            <svg class="ic ic-sm"><use href="#i-camera" /></svg>
                            <input type="file" id="avatar-input" name="avatar" form="profile-form" class="hidden" accept="image/png,image/jpeg,image/webp">
                        </label>
                    </div>
                    <h2 class="font-display font-extrabold text-xl mt-3">{{ $admin->name }}</h2>
                    <p class="text-ink/55">{{ $admin->displayTitle() }}</p>
                    <p id="avatar-hint" class="text-xs text-primary mt-2 hidden">New photo selected — click “Save changes”.</p>

                    <div class="grid grid-cols-3 gap-3 mt-5">
                        <div class="rounded-2xl bg-bg p-3"><b class="font-display">{{ number_format($ordersCount) }}</b><p class="text-xs text-ink/50">Orders</p></div>
                        <div class="rounded-2xl bg-bg p-3"><b class="font-display">{{ number_format($productsCount) }}</b><p class="text-xs text-ink/50">Products</p></div>
                        <div class="rounded-2xl bg-bg p-3"><b class="font-display">{{ $admin->created_at->diffForHumans(null, true, true) }}</b><p class="text-xs text-ink/50">Member</p></div>
                    </div>
                </div>
            </div>

            <section class="card">
                <div class="card-h"><h3>Recent activity</h3></div>
                <div class="card-b">
                    @include('admin.profile._activity', ['items' => $activities->take(4)])
                </div>
            </section>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="card">
                <div class="tabs" data-tabs="#panes">
                    <span class="tab {{ $openTab === 'info' ? 'active' : '' }}" data-tab="info"><svg class="ic ic-sm"><use href="#i-user" /></svg>Profile</span>
                    <span class="tab {{ $openTab === 'security' ? 'active' : '' }}" data-tab="security"><svg class="ic ic-sm"><use href="#i-lock" /></svg>Security</span>
                    <span class="tab" data-tab="activity"><svg class="ic ic-sm"><use href="#i-clock" /></svg>Activity</span>
                </div>
            </div>

            <div id="panes" class="space-y-6">

                <div class="tab-pane {{ $openTab === 'info' ? 'active' : '' }} space-y-6" id="info">
                    <section class="card">
                        <div class="card-h"><h3>Personal information</h3></div>
                        <div class="card-b">
                            <form id="profile-form" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-5">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label class="lbl" for="name">Full name</label>
                                    <input class="input" id="name" name="name" type="text" value="{{ $field('name') }}" required>
                                </div>
                                <div>
                                    <label class="lbl" for="email">Email</label>
                                    <input class="input" id="email" name="email" type="email" value="{{ $field('email') }}" required>
                                </div>
                                <div>
                                    <label class="lbl" for="phone">Phone</label>
                                    <input class="input" id="phone" name="phone" type="text" value="{{ $field('phone') }}" placeholder="+880 1700-000000">
                                </div>
                                <div>
                                    <label class="lbl" for="job_title">Role</label>
                                    <input class="input" id="job_title" type="text" value="{{ $admin->displayTitle() }}" disabled>
                                    <p class="hint">Assigned by a Super Admin</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="lbl" for="bio">Bio</label>
                                    <textarea class="textarea" id="bio" name="bio" rows="3" maxlength="500">{{ $field('bio') }}</textarea>
                                </div>

                                @if ($admin->avatar)
                                    <label class="sm:col-span-2 flex items-center gap-3 text-sm text-ink/70">
                                        <input type="checkbox" name="remove_avatar" value="1" @checked(old('remove_avatar'))> Remove current profile photo
                                    </label>
                                @endif

                                <div class="sm:col-span-2 flex justify-end">
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="tab-pane {{ $openTab === 'security' ? 'active' : '' }} space-y-6" id="security">
                    <section class="card">
                        <div class="card-h"><h3>Change password</h3></div>
                        <div class="card-b">
                            <form method="POST" action="{{ route('admin.profile.password') }}" class="grid gap-5 max-w-lg">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label class="lbl" for="current_password">Current password</label>
                                    <div class="input-group">
                                        <svg class="ic"><use href="#i-lock" /></svg>
                                        <input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="lbl" for="password">New password</label>
                                    <div class="input-group">
                                        <svg class="ic"><use href="#i-lock" /></svg>
                                        <input class="input" id="password" name="password" type="password" autocomplete="new-password" required>
                                    </div>
                                    <p class="hint">At least 8 characters with a number</p>
                                </div>
                                <div>
                                    <label class="lbl" for="password_confirmation">Confirm new password</label>
                                    <div class="input-group">
                                        <svg class="ic"><use href="#i-lock" /></svg>
                                        <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                                    </div>
                                </div>
                                <div><button type="submit" class="btn btn-primary">Update password</button></div>
                            </form>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-h"><h3>Current session</h3></div>
                        <div class="card-b">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold">{{ $lastLogin?->browserLabel() ?? 'This device' }}</p>
                                    <p class="text-xs text-ink/50">
                                        {{ request()->ip() }}
                                        @if ($lastLogin) · Signed in {{ $lastLogin->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, g:i A') }} @endif
                                    </p>
                                </div>
                                <span class="badge b-success">Active</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="tab-pane space-y-6" id="activity">
                    <section class="card">
                        <div class="card-h">
                            <h3>Full activity log</h3>
                            @if ($activities->isNotEmpty())
                                <form method="POST" action="{{ route('admin.profile.activity.clear') }}" data-swal data-swal-title="Clear activity log?" data-swal-text="This cannot be undone." data-swal-confirm="Yes, clear all" data-swal-danger="1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-danger"><svg class="ic ic-sm"><use href="#i-trash" /></svg>Clear all</button>
                                </form>
                            @endif
                        </div>
                        <div class="card-b">
                            @include('admin.profile._activity', ['items' => $activities])
                            <p class="text-xs text-ink/45 mt-5">Activity older than {{ \App\Models\AdminActivity::RETENTION_DAYS }} days is deleted automatically.</p>
                        </div>
                    </section>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('avatar-input');
            if (!input) return;

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;

                var preview = document.getElementById('avatar-preview');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                document.getElementById('avatar-initial').classList.add('hidden');
                document.getElementById('avatar-hint').classList.remove('hidden');
            });
        });
    </script>

@include('admin.partials.swal-confirm')

@endsection
