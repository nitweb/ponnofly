@php
    $hasOld = session()->hasOldInput();
    $isSelf = $member && auth('admin')->user()->is($member);
    $isActive = $hasOld ? (bool) old('is_active') : ($member?->is_active ?? true);
    $selectedRole = (int) old('role_id', $member?->role_id ?? $roles->first()?->id);
@endphp

@if ($errors->any())
    <div class="card p-4 text-danger text-sm space-y-1 mb-6">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">

        <section class="card">
            <div class="card-h"><h3>Account details</h3></div>
            <div class="card-b">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="lbl" for="name">Full name <span class="text-danger">*</span></label>
                        <input class="input" id="name" name="name" type="text" value="{{ old('name', $member?->name) }}" maxlength="120" required>
                    </div>
                    <div>
                        <label class="lbl" for="email">Email <span class="text-danger">*</span></label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email', $member?->email) }}" required>
                    </div>
                    <div>
                        <label class="lbl" for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" type="tel" value="{{ old('phone', $member?->phone) }}" placeholder="01700-000000">
                    </div>
                    <div>
                        <label class="lbl" for="role_id">Role</label>
                        @if ($isSelf)
                            <input class="input" type="text" value="{{ $member->displayTitle() }}" disabled>
                            <p class="hint">You cannot change your own role</p>
                        @else
                            <select class="select" id="role_id" name="role_id" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected($selectedRole === $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>
                        <label class="lbl" for="password">Password @unless ($member)<span class="text-danger">*</span>@endunless</label>
                        <input class="input" id="password" name="password" type="password" autocomplete="new-password" @required(! $member)>
                        <p class="hint">{{ $member ? 'Leave blank to keep the current password' : 'At least 8 characters with a number' }}</p>
                    </div>
                    <div>
                        <label class="lbl" for="password_confirmation">Confirm password</label>
                        <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Role permissions preview</h3></div>
            <div class="card-b">
                <p class="text-ink/60 mb-4">Permissions come from the selected role. Manage them in
                    @if (auth('admin')->user()->hasPermission('roles:view'))
                        <a class="text-primary font-bold" href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>.
                    @else
                        Roles &amp; Permissions.
                    @endif
                </p>
                <div class="grid sm:grid-cols-2 gap-3" id="role-preview" data-roles="{{ json_encode($preview) }}" data-current="{{ $isSelf ? $member->role_id : '' }}"></div>
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="card">
            <div class="card-h"><h3>Profile photo</h3></div>
            <div class="card-b">
                <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                    <img id="sav" src="{{ $member?->avatarUrl() ?? asset('assets/admin/img/no-image.svg') }}" class="w-24 h-24 rounded-3xl object-cover bg-white border border-line shrink-0" alt="">
                    <div class="text-center sm:text-left">
                        <p class="font-bold">Upload image</p>
                        <p class="text-xs text-ink/50 mt-1 mb-3">Optional · square · max 2 MB</p>
                        <label class="btn btn-primary btn-sm cursor-pointer">Upload<input type="file" name="avatar" class="hidden" accept="image/png,image/jpeg,image/webp" data-preview="#sav"></label>
                    </div>
                </div>
                @if ($member?->avatar)
                    <label class="flex items-center gap-3 text-sm text-ink/70 mt-4">
                        <input type="checkbox" name="remove_avatar" value="1" @checked(old('remove_avatar'))> Remove current photo
                    </label>
                @endif
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Access</h3></div>
            <div class="card-b">
                @if ($isSelf)
                    <p class="text-sm text-ink/60">You cannot change your own status.</p>
                @else
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">Active</p>
                            <p class="text-xs text-ink/50">Shown as active in the staff list</p>
                        </div>
                        <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($isActive)><i></i></label>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<div class="savebar mt-6">
    <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $member ? 'Update staff' : 'Add staff' }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var box = document.getElementById('role-preview');
        var select = document.getElementById('role_id');
        if (!box) return;

        var roles = JSON.parse(box.dataset.roles || '{}');

        function render(id) {
            var rows = roles[id] || [];
            box.innerHTML = '';
            rows.forEach(function (row) {
                var item = document.createElement('div');
                item.className = 'flex items-center gap-3 rounded-xl bg-bg px-4 py-3';
                var name = document.createElement('b');
                name.textContent = row.label;
                var access = document.createElement('span');
                access.className = 'ml-auto text-xs ' + (row.access === 'None' ? 'text-ink/30' : 'text-ink/50');
                access.textContent = row.access;
                item.appendChild(name);
                item.appendChild(access);
                box.appendChild(item);
            });
        }

        if (select) {
            select.addEventListener('change', function () { render(select.value); });
            render(select.value);
        } else {
            render(box.dataset.current);
        }
    });
</script>
