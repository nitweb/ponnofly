@extends('admin.dashboard.dashboard')
@section('admin_title', 'Add staff · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Add staff</h1>

    <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.staff._form')
    </form>

@endsection
