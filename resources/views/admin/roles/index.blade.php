@extends('admin.dashboard.dashboard')
@section('admin_title', 'Roles & Permissions · Nebula Mart Admin')
@section('admin_contents')

    @php $total = \App\Support\Permissions::total(); @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Roles &amp; Permissions</h1>
            <p class="text-ink/55 mt-1">Control exactly what each team member can see and do.</p>
        </div>
        @if (auth('admin')->user()->hasPermission('roles:create'))
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add role
            </a>
        @endif
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            @php
                $count = $role->permissionCount();
                $percent = $total ? round($count / $total * 100) : 0;
                $admin = auth('admin')->user();
            @endphp
            <article class="card p-6 flex flex-col" data-row>
                <div class="flex items-start justify-between">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center" style="background: {{ $role->color }}1a; color: {{ $role->color }}">
                        <svg class="ic ic-lg"><use href="#i-shield" /></svg>
                    </span>
                    <div class="flex gap-1 items-center">
                        @unless ($role->is_active)
                            <span class="badge b-muted">Inactive</span>
                        @endunless
                        @if ($role->is_locked)
                            <span class="badge b-muted">Locked</span>
                        @else
                            @if ($admin->hasPermission('roles:edit'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="icon-btn" title="Edit"><svg class="ic"><use href="#i-edit" /></svg></a>
                            @endif
                            @if ($admin->hasPermission('roles:delete'))
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="icon-btn danger" data-delete="{{ $role->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <h3 class="font-display font-extrabold text-lg mt-4">{{ $role->name }}</h3>
                <p class="text-ink/60 mt-1 flex-1">{{ $role->description }}</p>

                <div class="mt-5">
                    <div class="flex justify-between text-sm mb-2"><span class="text-ink/55 font-semibold">Permissions</span><b>{{ $count }} / {{ $total }}</b></div>
                    <div class="bar-track"><i style="width: {{ $percent }}%; background: {{ $role->color }}"></i></div>
                </div>

                <div class="flex items-center justify-between mt-5 pt-5 border-t border-line">
                    <span class="text-sm text-ink/60 flex items-center gap-2"><svg class="ic ic-sm"><use href="#i-users" /></svg><b>{{ $role->admins_count }}</b> {{ \Illuminate\Support\Str::plural('member', $role->admins_count) }}</span>
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm font-bold text-primary hover:underline">{{ $role->is_locked ? 'View' : 'Manage' }} →</a>
                </div>
            </article>
        @endforeach
    </div>

@endsection
