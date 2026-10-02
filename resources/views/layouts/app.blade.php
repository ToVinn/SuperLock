<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Neper PhoneLock')</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
@stack('head')
    <style>.menu-grup { font-size:10.5px; font-weight:700; color:rgba(255,255,255,.5); margin:12px 12px 4px; letter-spacing:1px; text-transform:uppercase }</style>
</head>
<body>
@hasSection('nav')
@yield('nav')
@else
@php
        $menuPerRole = [
        'admin' => [
            'UTAMA' => [
                ['monitoring', 'Monitoring', 'm3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
                ['km', 'Pengecekan KM', 'm9 3a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 17 3-7 3 2 2-5 3 6 3-4 3 8H2zm18-4-4-6-3 5-2-3-4 8h13z'],
            ],
            'DATA & PENITIPAN' => [
                ['siswa', 'Kelola Siswa', 'M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 2c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4zm8 0c-.3 0-.7 0-1 .1 1.3 1 2 2.2 2 3.9v2h7v-2c0-2.7-5.3-4-8-4z'],
                ['edit', 'Pengeditan', 'M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25zM20.7 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z'],
                ['rekap', 'Rekap Harian', 'M4 20h3V10H4v10zm6 0h3V4h-3v16zm6 0h3v-7h-3v7z'],
            ],
            'PENGATURAN' => [
                ['users', 'Kelola User', 'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5c0-3-4-5.5-9-5.5z'],
                ['aktivitas', 'Log Aktivitas', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.07V19H11v-2.93c-2.25-.33-4-2.28-4-4.57 0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.29-1.75 4.24-4 4.57z'],
            ]
        ],
        'guru' => [
            'UTAMA' => [
                ['monitoring', 'Monitoring', 'm3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
            ],
            'DATA & PENITIPAN' => [
                ['edit', 'Pengeditan', 'M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25zM20.7 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z'],
            ]
        ],
        'km' => [
            'UTAMA' => [
                ['km', 'Pengecekan KM', 'm9 3a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 17 3-7 3 2 2-5 3 6 3-4 3 8H2zm18-4-4-6-3 5-2-3-4 8h13z'],
            ]
        ],
    ];
    $menu = $menuPerRole[auth()->user()->role] ?? [];
    $logoAda = file_exists(public_path('images/logo.png'));
@endphp
<button class="toggle" onclick="document.body.classList.toggle('menu-buka')" aria-label="Menu">
    <i></i><i></i><i></i>
</button>
<div class="layout">
<aside class="sidebar">
    <div class="logo">
        <div class="logo-foto">
            @if ($logoAda)
                <img src="{{ asset('images/logo.png').'?v='.filemtime(public_path('images/logo.png')) }}" alt="Logo">
            @else
                P
            @endif
        </div>
    </div>
    <nav class="menu">
        @foreach ($menu as $grup => $items)
            <div class="menu-grup">{{ $grup }}</div>
            @foreach ($items as [$route, $label, $ikon])
                <a href="{{ route($route) }}" @class(['aktif' => request()->routeIs($route)])>
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $ikon }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="sidebar-footer">
        <div class="user">{{ auth()->user()->nama }}<small>{{ auth()->user()->role }}</small></div>
        <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('frm-out').submit()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M10 3h4a2 2 0 0 1 2 2v3h-2V5h-4v14h4v-3h2v3a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm8 7 4 2-4 2v-1.5h-6v-1h6V10z"/></svg>
            Keluar
        </a>
    </div>
</aside>
<main class="content">
@yield('content')
</main>
</div>
<form id="frm-out" method="POST" action="{{ route('logout') }}">@csrf</form>
@endif
</body>
</html>
