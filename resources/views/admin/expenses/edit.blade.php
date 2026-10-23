@extends('admin.dashboard.dashboard')
@section('admin_title', 'Edit expense · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Edit expense</h1>

    <form method="POST" action="{{ route('admin.expenses.update', $expense) }}" class="max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.expenses._form')
    </form>

@endsection
