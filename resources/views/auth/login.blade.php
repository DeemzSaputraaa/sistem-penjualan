@extends('layouts.app')

@section('body-class', 'auth-page')

@push('styles')
@vite('resources/css/auth-login.css')
@endpush

@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="row g-0 h-100">
            <div class="col-lg-6 d-none d-lg-flex auth-visual">
                <div class="auth-brand">
                    <span class="auth-brand-badge">SP</span>
                    <span>Sistem Penjualan</span>
                </div>
            </div>
            <div class="col-12 col-lg-6 auth-form">
                <div class="auth-title">
                    Selamat datang di <span>Sistem Penjualan</span>
                </div>
                <div class="auth-subtitle">
                    Kelola transaksi, stok, dan laporan secara cepat dalam satu dashboard.
                </div>
                <div class="auth-indicator">
                    <span class="active"></span>
                    <span></span>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="text-decoration-none" href="{{ route('password.request') }}">Lupa password?</a>
                        @endif
                    </div>

                    <div class="auth-actions d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            Masuk
                        </button>
                        @if (Route::has('register'))
                            <a class="btn btn-outline-primary flex-fill" href="{{ route('register') }}">
                                Buat akun
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
