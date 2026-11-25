<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', app()->getLocale())) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password | Blood Bank</title>
        <style>
            * { box-sizing: border-box; }
            body {
                min-height: 100vh;
                margin: 0;
                display: grid;
                place-items: center;
                padding: 24px;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: #f5f0f5;
                color: #3a2b3d;
            }
            .reset-shell {
                width: min(480px, 100%);
                padding: 2.25rem 2rem;
                border-radius: 28px;
                background: linear-gradient(160deg, #8f3d7b 0%, #8b3d74 32%, #a15a88 100%);
                box-shadow: 0 32px 80px rgba(116, 76, 100, 0.12);
                color: #fff;
            }
            h1 { margin: 0 0 0.5rem; text-align: center; }
            .subtitle { margin: 0 0 1.6rem; text-align: center; color: rgba(255,255,255,0.8); }
            .form-message {
                margin-bottom: 1rem;
                padding: 0.8rem 1rem;
                border-radius: 12px;
                background: rgba(255,255,255,0.16);
                font-size: 0.9rem;
            }
            form { display: grid; gap: 0.9rem; }
            label { font-size: 0.85rem; font-weight: 600; }
            input {
                width: 100%;
                min-height: 52px;
                padding: 0.8rem 1rem;
                border: 1px solid rgba(255,255,255,0.3);
                border-radius: 12px;
                background: rgba(255,255,255,0.22);
                color: #fff;
                font-size: 1rem;
            }
            input::placeholder { color: rgba(255,255,255,0.65); }
            button {
                min-height: 50px;
                margin-top: 0.4rem;
                border: 0;
                border-radius: 12px;
                background: #fff;
                color: #8f3d7b;
                font-size: 1rem;
                font-weight: 700;
                cursor: pointer;
            }
            a { display: block; margin-top: 1rem; color: #fff; text-align: center; text-decoration: none; }
        </style>
    </head>
    <body>
        <main class="reset-shell">
            <h1>Reset Password</h1>
            <p class="subtitle">Choose a new password for your account.</p>

            <?php if ($errors->any()) { ?>
                <div class="form-message" role="alert">
                    <?php foreach ($errors->all() as $error) { ?>
                        <div><?= e($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <form method="POST" action="<?= e(route('password.update')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="<?= e(old('email', $email)) ?>" autocomplete="email" required>
                <label for="password">New Password</label>
                <input id="password" type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" required>
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm New Password" autocomplete="new-password" required>
                <button type="submit">Reset Password</button>
            </form>

            <a href="<?= e(route('login')) ?>">Back to Login</a>
        </main>
    </body>
</html>
