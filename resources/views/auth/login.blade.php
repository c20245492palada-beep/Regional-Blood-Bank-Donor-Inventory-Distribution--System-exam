<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Welcome Back | Blood Bank</title>
        <style>
            :root {
                --bg-soft: #f5f0f5;
                --card-bg: #f4f1f3;
                --purple-dark: #8f3d7b;
                --purple-mid: #a55b92;
                --purple-light: #d1b1d3;
                --text-dark: #3a2b3d;
                --text-soft: #7d6d7f;
                --line: rgba(95, 62, 87, 0.18);
                --shadow: rgba(84, 47, 76, 0.18);
            }

            * {
                box-sizing: border-box;
            }

            html, body {
                margin: 0;
                min-height: 100%;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(ellipse at 14% 18%, rgba(165, 91, 146, 0.1), transparent 34%),
                    radial-gradient(ellipse at 88% 84%, rgba(143, 61, 123, 0.08), transparent 32%),
                    linear-gradient(135deg, #fcf8fc 0%, var(--bg-soft) 55%, #eef1f8 100%);
                color: var(--text-dark);
            }

            body {
                min-height: 100vh;
                display: grid;
                place-items: center;
                position: relative;
                isolation: isolate;
            }

            body::before,
            body::after {
                content: "";
                position: fixed;
                z-index: -1;
                width: 128px;
                aspect-ratio: 1;
                border-radius: 0 70% 70% 70%;
                transform: rotate(45deg);
                background: rgba(165, 91, 146, 0.035);
                pointer-events: none;
            }

            body::before {
                top: 10%;
                left: 12%;
            }

            body::after {
                right: 12%;
                bottom: 10%;
                width: 92px;
            }

            .login-shell {
                width: min(480px, 92vw);
                min-height: auto;
                background: linear-gradient(160deg, var(--purple-dark) 0%, #8b3d74 32%, #a15a88 100%);
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 32px 80px rgba(116, 76, 100, 0.12);
                padding: 2.25rem 2rem;
                position: relative;
                z-index: 1;
            }

            .login-shell::before,
            .login-shell::after {
                content: "";
                position: absolute;
                width: 22px;
                height: 22px;
                background: rgba(255, 255, 255, 0.13);
                clip-path: polygon(50% 0%, 60% 40%, 100% 50%, 60% 60%, 50% 100%, 40% 60%, 0% 50%, 40% 40%);
            }

            .login-shell::before { top: 56px; right: 68px; }
            .login-shell::after { bottom: 54px; left: 58px; }

            .login-card {
                width: min(390px, 100%);
                margin: 0 auto;
                position: relative;
                z-index: 1;
            }

            .login-card h1 {
                text-align: center;
                color: #fff;
                margin: 0 0 0.35rem;
                font-size: clamp(2rem, 2.5vw, 2.6rem);
                font-weight: 700;
                line-height: 1.15;
                letter-spacing: -0.03em;
            }

            .login-card .subtitle {
                text-align: center;
                color: rgba(255,255,255,0.77);
                font-size: 0.95rem;
                margin-bottom: 1.6rem;
            }

            .login-form {
                display: grid;
                gap: 0.9rem;
            }

            .form-errors {
                padding: 0.8rem 1rem;
                border-radius: 12px;
                background: rgba(255,255,255,0.16);
                color: #fff;
                font-size: 0.88rem;
            }

            .input-group {
                display: grid;
                gap: 0.45rem;
            }

            label {
                color: rgba(255,255,255,0.9);
                font-size: 0.8rem;
                font-weight: 600;
            }

            input {
                width: 100%;
                border: 1px solid rgba(255,255,255,0.3);
                background: rgba(255,255,255,0.22);
                color: #fff;
                border-radius: 12px;
                min-height: 52px;
                padding: 0.8rem 1rem;
                font-size: 1rem;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            }

            input::placeholder {
                color: rgba(255,255,255,0.65);
            }

            input:focus {
                border-color: rgba(255,255,255,0.8);
                box-shadow: 0 0 0 3px rgba(255,255,255,0.14);
                background: rgba(255,255,255,0.24);
            }

            .login-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-top: 0.2rem;
            }

            .remember {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                color: rgba(255,255,255,0.8);
                font-size: 0.78rem;
            }

            .remember input {
                width: auto;
                min-height: auto;
                accent-color: #f7d7e8;
            }

            .forgot {
                color: rgba(255,255,255,0.8);
                text-decoration: none;
                font-size: 0.78rem;
            }

            .login-btn {
                margin-top: 0.6rem;
                border: none;
                border-radius: 12px;
                min-height: 50px;
                background: linear-gradient(180deg, #fff 0%, #f4edf3 100%);
                color: var(--purple-dark);
                font-size: 1.02rem;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 14px 28px rgba(70, 18, 50, 0.18);
            }

            .register-link {
                text-align: center;
                margin-top: 0.85rem;
                font-size: 0.82rem;
                color: rgba(255,255,255,0.8);
            }

            .register-link a {
                color: #fff;
                text-decoration: none;
                font-weight: 700;
            }

            @media (max-width: 560px) {
                .login-shell {
                    padding: 1.5rem 1.1rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-shell">
            <div class="login-card">
                <h1>Welcome Back !</h1>
                <p class="subtitle">Login to your Account</p>

                @if ($errors->any())
                    <div class="form-errors" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form class="login-form" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" autocomplete="email" required />
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Password" autocomplete="current-password" required />
                    </div>

                    <div class="login-meta">
                        <label class="remember">
                            <input type="checkbox" name="remember" value="1" />
                            Remember me
                        </label>
                        <a class="forgot" href="#">Forgot Password?</a>
                    </div>

                    <button class="login-btn" type="submit">Login</button>
                </form>

                <p class="register-link">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
            </div>
        </div>
    </body>
</html>
