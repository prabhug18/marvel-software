@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-logo" style="text-align: center; margin-bottom: 15px;">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="height: 100px;" width="250px" />
        </div>
        <h2 class="auth-heading" style="text-align: center;">Login</h2>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="row mb-3">
                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                <div class="col-md-6">
                    <input id="email" type="email" class="auth-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="auth-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            {{-- <div class="row mb-3">
                <div class="col-md-6 offset-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div> --}}

            <div class="row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="auth-button">
                        {{ __('Login') }}
                    </button>

                    {{-- @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif --}}
                </div>
            </div>
        </form>                
        
    </div>
</div>
@endsection
