@extends('admin.dashboard.dashboard')
@section('admin_title', 'Staff Members · Nebula Mart Admin')
@section('admin_contents')

    @php $actor = auth('admin')->user(); @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Staff Members</h1>
            <p class="text-ink/55 mt-1">Admin users who can sign in to this dashboard.</p>
        </div>
        @if ($actor->hasPermission('staff:create'))
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add staff
            </a>
        @endif
    </div>

    <section class="card">
        <form method="GET" action="{{ route('admin.staff.index') }}" class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $search }}" placeholder="Search name, email, phone…">
            </div>
            <div class="w-48">
                <select class="select" name="role" onchange="this.form.submit()" aria-label="Role">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected($roleId === $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
            @if ($search !== '' || $roleId)
                <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="tbl" id="tbl">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Last login</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        @php
                            $isSelf = $actor->is($member);
                            $locked = $member->isSuperAdmin() && ! $actor->isSuperAdmin();
                            $color = $member->role?->color ?? '#DC2626';
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($member->avatarUrl())
                                        <img src="{{ $member->avatarUrl() }}" alt="" class="w-11 h-11 rounded-xl object-cover">
                                    @else
                                        <span class="w-11 h-11 rounded-xl text-white grid place-items-center font-display font-extrabold" style="background: {{ $color }}">{{ $member->initial() }}</span>
                                    @endif
                                    <div>
                                        <b>{{ $member->name }}</b>@if ($isSelf) <span class="text-xs text-ink/50">(you)</span>@endif
                                        <p class="text-xs text-ink/50">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->phone ?: '—' }}</td>
                            <td><span class="badge" style="background: {{ $color }}1a; color: {{ $color }}">{{ $member->displayTitle() }}</span></td>
                            <td class="text-ink/60">{{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td>
                                @if ($actor->hasPermission('staff:edit') && ! $isSelf && ! $locked)
                                    <form method="POST" action="{{ route('admin.staff.toggle', $member) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="switch"><input type="checkbox" @checked($member->is_active) onchange="this.form.submit()"><i></i></label>
                                    </form>
                                @else
                                    <span class="badge {{ $member->is_active ? 'b-success' : 'b-muted' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @if ($isSelf)
                                        <a href="{{ route('admin.profile.edit') }}" class="icon-btn" title="My profile"><svg class="ic"><use href="#i-user" /></svg></a>
                                    @elseif (! $locked)
                                        @if ($actor->hasPermission('staff:edit'))
                                            <a href="{{ route('admin.staff.edit', $member) }}" class="icon-btn" title="Edit"><svg class="ic"><use href="#i-edit" /></svg></a>
                                        @endif
                                        @if ($actor->hasPermission('staff:delete'))
                                            <form method="POST" action="{{ route('admin.staff.destroy', $member) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="icon-btn danger" data-delete="{{ $member->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-ink/55">No staff members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($staff->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $staff->firstItem() }}–{{ $staff->lastItem() }}</b> of <b class="text-ink">{{ $staff->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($staff->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $staff->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($staff->getUrlRange(1, $staff->lastPage()) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $staff->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($staff->hasMorePages())
                        <a href="{{ $staff->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
