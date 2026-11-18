@extends('admin.dashboard.dashboard')
@section('admin_title', 'Edit supplier · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Edit supplier</h1>

    <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">
        @csrf
        @method('PUT')
        @include('admin.suppliers._form', ['supplier' => $supplier])
    </form>

@endsection
