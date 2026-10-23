@extends('admin.dashboard.dashboard')
@section('admin_title', 'Add expense · Nebula Mart Admin')
@section('admin_contents')

    <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Add expense</h1>

    <form method="POST" action="{{ route('admin.expenses.store') }}" class="max-w-3xl">
        @csrf
        @include('admin.expenses._form', ['expense' => null])
    </form>

@endsection
