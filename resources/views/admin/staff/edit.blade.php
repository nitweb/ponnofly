@extends('admin.dashboard.dashboard')
@section('admin_title', 'Edit staff · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Edit staff</h1>

    <form method="POST" action="{{ route('admin.staff.update', $member) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.staff._form')
    </form>

@endsection
