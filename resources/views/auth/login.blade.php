@extends('layouts.app')

@section('content')
<style>
    body {
        margin: 0;
        font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background-color: #f4f7f6;
    }
    
    .login-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        margin: -8px; /* Offset potential body margin from app layout */
    }

    /* Left Panel - Hero/Branding */
    .login-hero {
        flex: 1;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        padding: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .login-hero::before {
        content: '';
        position: absolute;
        width: 150%;
        height: 150%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
        top: -25%;
        left: -25%;
        pointer-events: none;
    }

    .hero-content {
        z-index: 1;
        text-align: center;
        max-width: 500px;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 20px;
        letter-spacing: -0.05em;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        font-weight: 300;
        opacity: 0.8;
        line-height: 1.6;
    }

    /* Right Panel - Form */
    .login-form-side {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        padding: 40px;
    }

    .login-form-wrapper {
        width: 100%;
        max-width: 440px;
    }

    .login-logo {
        text-align: center;
        margin-bottom: 40px;
    }

    .login-logo img {
        height: 80px;
        width: auto;
        object-fit: contain;
    }

    .form-heading {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 10px;
        text-align: center;
    }

    .form-subheading {
        color: #666;
        text-align: center;
        margin-bottom: 40px;
        font-size: 1rem;
    }

    .input-group {
        margin-bottom: 25px;
        width: 100%;
        position: relative;
    }

    .input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-align: left;
    }

    .input-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e1e5ea;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #fafbfa;
        color: #333;
        box-sizing: border-box;
    }

    .input-group input:focus {
        outline: none;
        border-color: #2c5364;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(44, 83, 100, 0.1);
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 6px;
        display: block;
        text-align: left;
    }

    .input-group input.is-invalid {
        border-color: #dc3545;
    }

    .btn-login {
        width: 100%;
        padding: 16px;
        margin-top: 10px;
        background: linear-gradient(135deg, #11998e, #38ef7d);
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 15px rgba(56, 239, 125, 0.3);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(56, 239, 125, 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 900px) {
        .login-container {
            flex-direction: column;
        }
        
        .login-hero {
            display: none;
        }
        
        .login-form-side {
            min-height: 100vh;
        }
    }
</style>

<div class="login-container">
    <div class="login-hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Marvel CRM</h1>
            <p class="hero-subtitle">Empowering your business with intelligent insights and seamless management.</p>
        </div>
    </div>
    
    <div class="login-form-side">
        <div class="login-form-wrapper">
            <div class="login-logo">
                <img src="{{ asset('assets/images/logo.webp') }}" alt="Marvel CRM Logo" />
            </div>
            
            <h2 class="form-heading">Sign In</h2>
            <p class="form-subheading">Access your dashboard to continue.</p>

            <form method="POST" action="{{ route('login') }}" style="width: 100%;">
                @csrf

                <div class="input-group">
                    <label for="email">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                    
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" class="@error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="input-group">
                    <button type="submit" class="btn-login">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
