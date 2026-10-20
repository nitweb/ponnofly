@extends('admin.dashboard.dashboard')
@section('admin_title', 'Edit role · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">{{ $role->is_locked ? 'View role' : 'Edit role' }}</h1>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('admin.roles._form')
    </form>

@endsection
