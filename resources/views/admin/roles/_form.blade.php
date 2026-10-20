@php
    $locked = (bool) $role?->is_locked;
    $hasOld = session()->hasOldInput();
    $granted = $locked ? \App\Support\Permissions::all() : ($hasOld ? (array) old('perm', []) : ($role?->permissionKeys() ?? []));
    $isActive = $hasOld ? (bool) old('is_active') : ($role?->is_active ?? true);
    $actor = auth('admin')->user();
    $matrixActions = \App\Support\Permissions::actions();
@endphp

@if ($errors->any())
    <div class="card p-4 text-danger text-sm space-y-1 mb-6">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if ($locked)
    <div class="card p-4 text-sm text-ink/70 mb-6">The Super Admin role always has every permission and cannot be edited or deleted.</div>
@endif

<fieldset @disabled($locked) style="display:contents;border:0;padding:0;margin:0;min-width:0">
    <div class="grid xl:grid-cols-4 gap-6 items-start">

        <div class="space-y-6 xl:sticky xl:top-24">
            <section class="card">
                <div class="card-h"><h3>Role details</h3></div>
                <div class="card-b">
                    <div class="space-y-5">
                        <div>
                            <label class="lbl" for="name">Role name <span class="text-danger">*</span></label>
                            <input class="input" id="name" name="name" type="text" value="{{ old('name', $role?->name) }}" placeholder="e.g. Order Manager" maxlength="80" required>
                        </div>
                        <div>
                            <label class="lbl" for="slug">Slug</label>
                            <input class="input" id="slug" name="slug" type="text" value="{{ old('slug', $role?->slug) }}" data-slug-from="#name">
                        </div>
                        <div>
                            <label class="lbl" for="description">Description</label>
                            <textarea class="textarea" id="description" name="description" rows="3" maxlength="255" placeholder="What can this role do?">{{ old('description', $role?->description) }}</textarea>
                        </div>
                        <div>
                            <label class="lbl" for="color">Color tag</label>
                            <div class="flex gap-3">
                                <input type="color" id="color" name="color" value="{{ old('color', $role?->color ?? '#0B3A8C') }}" class="w-12 h-11 rounded-xl border border-line p-1 bg-white cursor-pointer">
                                <span class="text-sm text-ink/55 self-center">Shown on staff badges</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold">Active</p>
                                <p class="text-xs text-ink/50">Inactive roles grant no access</p>
                            </div>
                            <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($isActive)><i></i></label>
                        </div>
                    </div>
                </div>
            </section>

            @unless ($locked)
                <section class="card">
                    <div class="card-h"><h3>Quick setup</h3></div>
                    <div class="card-b">
                        <label class="lbl">Copy permissions from</label>
                        <select class="select" data-presets="{{ json_encode($presets) }}">
                            <option value="">Choose a role…</option>
                            @foreach (array_keys($presets) as $presetName)
                                <option>{{ $presetName }}</option>
                            @endforeach
                        </select>
                        <div class="mt-5 rounded-2xl bg-primary-soft p-4 flex items-center justify-between">
                            <span class="font-semibold text-primary">Selected</span>
                            <b class="font-display text-xl text-primary" data-perm-count>0</b>
                        </div>
                        @unless ($actor->isSuperAdmin())
                            <p class="hint mt-3">You can only grant permissions you hold yourself.</p>
                        @endunless
                    </div>
                </section>
            @endunless
        </div>

        <div class="xl:col-span-3">
            <section class="card">
                <div class="card-h"><h3>Permission matrix</h3></div>
                <div>
                    <div class="px-6 py-4 border-b border-line flex flex-wrap items-center justify-between gap-3 bg-accent-soft/60">
                        <span class="flex items-center gap-2 text-sm font-semibold">
                            <svg class="ic ic-sm text-accent-dark"><use href="#i-shield" /></svg>Tick what this role is allowed to do. Unticked actions are hidden and blocked.
                        </span>
                        <label class="flex items-center gap-2 font-bold cursor-pointer"><input type="checkbox" class="chk" data-all>Select all</label>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="tbl" data-matrix>
                            <thead>
                                <tr>
                                    <th class="min-w-[220px]">Module</th>
                                    @foreach ($matrixActions as $action => $actionLabel)
                                        <th class="text-center">
                                            <label class="inline-flex flex-col items-center gap-1.5 cursor-pointer">{{ $actionLabel }}<input type="checkbox" class="chk" data-col-all="{{ $action }}"></label>
                                        </th>
                                    @endforeach
                                    <th class="text-center">All</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (\App\Support\Permissions::modules() as $module => $definition)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="w-9 h-9 rounded-xl bg-primary/10 text-primary grid place-items-center"><svg class="ic ic-sm"><use href="#i-{{ $definition['icon'] }}" /></svg></span>
                                                <b>{{ $definition['label'] }}</b>
                                            </div>
                                        </td>
                                        @foreach ($matrixActions as $action => $actionLabel)
                                            @if (in_array($action, $definition['actions'], true))
                                                <td class="text-center">
                                                    <input type="checkbox" class="chk pm" name="perm[]" value="{{ $module }}:{{ $action }}" data-row="{{ $module }}" data-col="{{ $action }}" @checked(in_array("{$module}:{$action}", $granted, true))>
                                                </td>
                                            @else
                                                <td class="text-center text-ink/20">—</td>
                                            @endif
                                        @endforeach
                                        <td class="text-center"><input type="checkbox" class="chk" data-row-all="{{ $module }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</fieldset>

<div class="savebar mt-6">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">{{ $locked ? 'Back' : 'Cancel' }}</a>
    @unless ($locked)
        <button type="submit" class="btn btn-primary">Save role</button>
    @endunless
</div>
