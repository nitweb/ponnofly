@extends('admin.dashboard.dashboard')
@section('admin_title', 'Add role · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Add role</h1>

    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        @include('admin.roles._form')
    </form>

@endsection
