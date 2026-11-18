@extends('admin.dashboard.dashboard')
@section('admin_title', 'Add supplier · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Add supplier</h1>

    <form method="POST" action="{{ route('admin.suppliers.store') }}">
        @csrf
        @include('admin.suppliers._form', ['supplier' => null, 'products' => collect(), 'productCount' => 0])
    </form>

@endsection
