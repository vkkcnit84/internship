@extends('layouts.app')
@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body.login-page { font-family: 'Inter', sans-serif; background: #f8fafc; }
    .login-shell {
        position: fixed; inset: 0; z-index: 1000; display: flex; background: #fff; overflow: auto;
    }
    .login-hero {
        flex: 1 1 50%; position: relative; display: flex; flex-direction: column; justify-content: center;
        padding: 64px; color: #fff; overflow: hidden;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #14532d 120%);
    }
    .login-hero::before, .login-hero::after {
        content: ''; position: absolute; border-radius: 50%; background: #22c55e; opacity: .15;
    }
    .login-hero::before { width: 420px; height: 420px; top: -140px; right: -120px; }
    .login-hero::after { width: 300px; height: 300px; bottom: -100px; left: -80px; opacity: .1; }
    .login-hero > * { position: relative; z-index: 1; }
    .login-brand { font-size: 1.25rem; font-weight: 700; letter-spacing: .3px; margin-bottom: 48px; }
    .login-brand i { color: #22c55e; margin-right: 8px; }
    .login-hero h2 { font-size: 2.4rem; font-weight: 700; line-height: 1.2; margin-bottom: 16px; }
    .login-hero h2 span { color: #22c55e; }
    .login-hero p.lead { color: #cbd5e1; font-size: 1.05rem; max-width: 440px; margin-bottom: 36px; }
    .login-points { list-style: none; padding: 0; margin: 0; }
    .login-points li { display: flex; align-items: center; margin-bottom: 14px; color: #e2e8f0; }
    .login-points i {
        width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 8px;
        background: rgba(34,197,94,.18); color: #22c55e; margin-right: 12px;
    }
    .login-panel { flex: 1 1 50%; display: flex; align-items: center; justify-content: center; padding: 32px; }
    .login-box { width: 100%; max-width: 400px; }
    .login-box h1 { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
    .login-box .sub { color: #64748b; margin-bottom: 28px; }
    .login-box label { font-weight: 500; color: #1e293b; font-size: .875rem; margin-bottom: 6px; }
    .field { position: relative; margin-bottom: 20px; }
    .field > i.icon { position: absolute; left: 14px; top: 13px; color: #94a3b8; }
    .field .form-control {
        height: 46px; padding-left: 40px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc;
    }
    .field .form-control:focus { background: #fff; border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
    .field .toggle-pw { position: absolute; right: 14px; top: 13px; color: #94a3b8; cursor: pointer; }
    .field .invalid-feedback { display: block; }
    .btn-login {
        width: 100%; height: 46px; border: 0; border-radius: 8px; font-weight: 600; color: #fff;
        background: #22c55e; transition: background .2s, transform .1s;
    }
    .btn-login:hover { background: #16a34a; }
    .btn-login:active { transform: translateY(1px); }
    .login-links { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; font-size: .875rem; }
    .login-links a { color: #16a34a; }
    .login-foot { text-align: center; color: #94a3b8; font-size: .8rem; margin-top: 32px; }
    @media (max-width: 860px) {
        .login-hero { display: none; }
    }
</style>
@endsection
@section('content')
<div class="login-shell">
    <div class="login-hero">
        <div class="login-brand"><i class="fa fa-graduation-cap"></i>{{ trans('panel.site_title') }}</div>
        <h2>Launch your career with <span>real internships</span></h2>
        <p class="lead">Manage registrations, classes and certificates from one simple dashboard.</p>
        <ul class="login-points">
            <li><i class="fa fa-check"></i> UGC-recognised internship programmes</li>
            <li><i class="fa fa-calendar"></i> Live classes and attendance tracking</li>
            <li><i class="fa fa-certificate"></i> Verified certificates of internship</li>
        </ul>
    </div>

    <div class="login-panel">
        <div class="login-box">
            <h1>Welcome back</h1>
            <p class="sub">Sign in to continue to your account</p>

            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <label for="email">{{ trans('global.login_email') }}</label>
                <div class="field">
                    <i class="icon fa fa-envelope"></i>
                    <input id="email" name="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required autocomplete="email" autofocus placeholder="{{ trans('global.login_email') }}" value="{{ old('email', null) }}">
                    @if($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <label for="password">{{ trans('global.login_password') }}</label>
                <div class="field">
                    <i class="icon fa fa-lock"></i>
                    <input id="password" name="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('global.login_password') }}">
                    <i class="toggle-pw fa fa-eye" id="togglePw" title="Show password"></i>
                    @if($errors->has('password'))
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <div class="login-links">
                    <div class="form-check checkbox">
                        <input class="form-check-input" name="remember" type="checkbox" id="remember" />
                        <label class="form-check-label mb-0" for="remember">{{ trans('global.remember_me') }}</label>
                    </div>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}">{{ trans('global.forgot_password') }}</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">{{ trans('global.login') }}</button>
            </form>

            <div class="login-foot">&copy; {{ date('Y') }} {{ trans('panel.site_title') }}</div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    document.getElementById('togglePw').addEventListener('click', function () {
        var pw = document.getElementById('password');
        var show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        this.classList.toggle('fa-eye', !show);
        this.classList.toggle('fa-eye-slash', show);
    });
</script>
@endsection
