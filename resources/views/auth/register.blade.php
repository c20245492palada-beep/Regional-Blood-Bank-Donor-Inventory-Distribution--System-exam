<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign Up | Blood Bank</title>
        <style>
            :root {
                --bg-soft: #f4f0f3;
                --purple-dark: #8f3d7b;
                --purple-mid: #a55b92;
                --purple-light: #d1b1d3;
                --white: #ffffff;
                --text-dark: #3a2b3d;
            }

            * { box-sizing: border-box; }

            html, body {
                margin: 0;
                min-height: 100%;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: var(--bg-soft);
                color: var(--text-dark);
            }

            body {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
            }

            .signup-shell {
                width: min(480px, 100%);
                background: linear-gradient(160deg, var(--purple-dark) 0%, #8b3d74 32%, #a15a88 100%);
                border-radius: 28px;
                box-shadow: 0 32px 80px rgba(116, 76, 100, 0.12);
                padding: 2.25rem 2rem;
                position: relative;
                color: var(--white);
            }

            .signup-shell::before,
            .signup-shell::after {
                content: "";
                position: absolute;
                width: 22px;
                height: 22px;
                background: rgba(255,255,255,0.13);
                clip-path: polygon(50% 0%, 60% 40%, 100% 50%, 60% 60%, 50% 100%, 40% 60%, 0% 50%, 40% 40%);
            }

            .signup-shell::before { top: 56px; right: 68px; }
            .signup-shell::after { bottom: 54px; left: 58px; }

            .signup-card {
                position: relative;
                z-index: 1;
            }

            h1 {
                margin: 0 0 0.35rem;
                text-align: center;
                font-size: clamp(2rem, 2.5vw, 2.6rem);
                letter-spacing: -0.03em;
            }

            .subtitle {
                text-align: center;
                color: rgba(255,255,255,0.78);
                margin-bottom: 1.6rem;
            }

            .signup-form {
                display: grid;
                gap: 0.9rem;
            }

            .form-errors {
                padding: 0.8rem 1rem;
                border-radius: 12px;
                background: rgba(255,255,255,0.16);
                color: #fff;
                font-size: 0.88rem;
                margin-bottom: 1rem;
            }

            .input-group {
                display: grid;
                gap: 0.45rem;
            }

            label {
                font-size: 0.8rem;
                font-weight: 600;
                color: rgba(255,255,255,0.9);
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
            }

            input::placeholder {
                color: rgba(255,255,255,0.65);
            }

            input:focus {
                border-color: rgba(255,255,255,0.8);
                box-shadow: 0 0 0 3px rgba(255,255,255,0.14);
                background: rgba(255,255,255,0.24);
            }

            .submit-btn {
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

            .signin-link {
                text-align: center;
                margin-top: 0.85rem;
                font-size: 0.82rem;
                color: rgba(255,255,255,0.8);
            }

            .signin-link a {
                color: #fff;
                text-decoration: none;
                font-weight: 700;
            }
        </style>
    </head>
    <body>
        <div class="signup-shell">
            <div class="signup-card">
                <h1>Create Account</h1>
                <p class="subtitle">Sign up to continue</p>

                @if ($errors->any())
                    <div class="form-errors" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form class="signup-form" method="POST" action="{{ route('register.submit') }}">
                    @csrf

                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" autocomplete="name" required />
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" autocomplete="email" required />
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Password" autocomplete="new-password" required />
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password" required />
                    </div>

                    <button class="submit-btn" type="submit">Sign Up</button>
                </form>

                <p class="signin-link">Already have an account? <a href="{{ route('login') }}">Login</a></p>
            </div>
        </div>
    </body>
</html>
