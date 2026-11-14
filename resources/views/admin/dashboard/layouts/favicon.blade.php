@php
    $adminFavicon = $setting('admin_favicon') ?: $setting('favicon');
@endphp

@if ($adminFavicon)
    <link rel="icon" href="{{ asset($adminFavicon) }}">
    <link rel="shortcut icon" href="{{ asset($adminFavicon) }}">
    <link rel="apple-touch-icon" href="{{ asset($adminFavicon) }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/admin/img/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/admin/img/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/admin/img/apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/admin/img/favicon.ico') }}">
@endif
