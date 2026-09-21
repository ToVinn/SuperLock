@extends('layouts.app')

@section('title', 'Login — Neper SuperLock')

@push('head')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('nav')
<div class="min-h-screen grid lg:grid-cols-2 font-sans text-slate-800 bg-slate-50">

  <!-- Kolom kiri: form -->
  <div class="flex items-center justify-center px-6 py-12 lg:px-16">
    <div class="w-full max-w-md">

      <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Selamat datang 👋</h1>
        <p class="mt-2 text-sm text-slate-500">Masuk untuk mengelola penitipan HP.</p>
      </div>

      <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.06)] ring-1 ring-slate-100">

        @if ($errors->any())
          <div class="mb-5 rounded-lg bg-red-50 border border-red-100 px-4 py-2.5 text-center text-sm font-medium text-red-600">{{ $errors->first() }}</div>
        @endif

        <form id="form-login" method="post" action="{{ route('login') }}" class="space-y-5">
          @csrf
          <div>
            <label for="username" class="block text-sm font-medium mb-1.5">Username</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
              </span>
              <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="Masukkan username"
                class="!w-full !rounded-xl !border !border-slate-200 !bg-slate-50 !pl-11 !pr-4 !py-3 !text-sm !text-slate-800 placeholder:!text-slate-400
                       focus:!bg-white focus:!ring-2 focus:!ring-teal-500/60 focus:!border-teal-500 focus:!outline-none transition-all duration-300" />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium mb-1.5">Password</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M3.75 18a2.25 2.25 0 0 1 2.25-2.25h12A2.25 2.25 0 0 1 20.25 18v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V18Z"/></svg>
              </span>
              <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                class="!w-full !rounded-xl !border !border-slate-200 !bg-slate-50 !pl-11 !pr-11 !py-3 !text-sm !text-slate-800 placeholder:!text-slate-400
                       focus:!bg-white focus:!ring-2 focus:!ring-teal-500/60 focus:!border-teal-500 focus:!outline-none transition-all duration-300" />
              <button type="button" onclick="lihat(this)" aria-label="Lihat password"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-teal-600 transition-colors duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
              </button>
            </div>
          </div>

          <button type="submit"
            class="w-full rounded-xl bg-teal-600 py-3 text-sm font-bold text-white
                   shadow-lg shadow-teal-600/25
                   hover:bg-teal-500 hover:shadow-xl hover:shadow-teal-500/30 hover:-translate-y-0.5
                   active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                   transition-all duration-300 ease-in-out">
            Masuk
          </button>
        </form>
      </div>

      <p class="mt-6 text-center text-xs text-slate-400">© 2026 NEPER · SuperLock</p>
    </div>
  </div>

  <!-- Kolom kanan: hero kurva gradasi -->
  <div class="relative overflow-hidden hidden lg:block">
    <div class="absolute inset-0 bg-gradient-to-br from-teal-500 via-teal-600 to-emerald-700"></div>

    <svg class="absolute top-0 left-0 w-[140%] text-white/10" viewBox="0 0 1440 320" fill="currentColor" preserveAspectRatio="none">
      <path d="M0,160 C360,320 720,0 1080,120 C1260,180 1380,140 1440,160 L1440,0 L0,0 Z" opacity=".6"/>
    </svg>
    <svg class="absolute bottom-0 left-0 w-full text-white/15" viewBox="0 0 1440 220" fill="currentColor" preserveAspectRatio="none">
      <path d="M0,96 C240,220 480,0 720,64 C960,128 1200,64 1440,128 L1440,220 L0,220 Z"/>
    </svg>

    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-2xl"></div>
    <div class="absolute bottom-10 -left-20 w-80 h-80 rounded-full bg-emerald-300/20 blur-3xl"></div>

    <div class="relative h-full flex flex-col items-center justify-center text-white px-16 text-center">
      <div class="mb-6 w-20 h-20 rounded-3xl bg-white/15 backdrop-blur flex items-center justify-center ring-1 ring-white/25 shadow-xl">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M3.75 18a2.25 2.25 0 0 1 2.25-2.25h12A2.25 2.25 0 0 1 20.25 18v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V18Z"/></svg>
      </div>
      <h2 class="text-4xl font-extrabold tracking-tight drop-shadow-sm">NEPER · SuperLock</h2>
      <p class="mt-4 max-w-md text-teal-50/90 leading-relaxed">Penitipan HP terpadu untuk sekolah. Aman, terpantau, dan tertib semua di satu tempat.</p>
      <div class="mt-10 grid grid-cols-3 gap-8 text-center">
        <div><p class="text-3xl font-extrabold">24/7</p><p class="mt-1 text-xs uppercase tracking-widest text-teal-100/70">Terpantau</p></div>
        <div><p class="text-3xl font-extrabold">QR</p><p class="mt-1 text-xs uppercase tracking-widest text-teal-100/70">Kartu &amp; Scan</p></div>
        <div><p class="text-3xl font-extrabold">100%</p><p class="mt-1 text-xs uppercase tracking-widest text-teal-100/70">Aman</p></div>
      </div>
    </div>
  </div>
</div>

<script>
function lihat(btn) {
  const i = btn.parentElement.querySelector('input');
  i.type = i.type === 'password' ? 'text' : 'password';
}
</script>
@endsection
