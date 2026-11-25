<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', app()->getLocale())) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Account Settings | Blood Bank</title>
        <style>
            :root {
                --bg: #f5f1f6;
                --panel: #fff;
                --primary: #8a3d78;
                --primary-dark: #6e2859;
                --text: #2e2033;
                --muted: #73657b;
                --line: rgba(95, 62, 87, 0.16);
                --danger: #9f1239;
                --success: #166534;
            }

            * { box-sizing: border-box; }

            body {
                min-height: 100vh;
                margin: 0;
                padding: 32px 18px;
                background: linear-gradient(180deg, #f7f1f5 0%, #f1edf2 100%);
                color: var(--text);
                font-family: "Segoe UI", Tahoma, sans-serif;
            }

            .settings-shell {
                display: grid;
                gap: 20px;
                width: min(760px, 100%);
                margin: 0 auto;
            }

            .settings-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
            }

            h1, h2, p { margin-top: 0; }
            h1 { margin-bottom: 6px; font-size: clamp(1.8rem, 4vw, 2.4rem); }
            h2 { margin-bottom: 6px; font-size: 1.15rem; }
            .settings-header p, .panel-description { margin-bottom: 0; color: var(--muted); }

            .back-link {
                flex: 0 0 auto;
                padding: 10px 14px;
                border-radius: 10px;
                background: var(--primary-dark);
                color: #fff;
                font-weight: 700;
                text-decoration: none;
            }

            .settings-panel {
                display: grid;
                gap: 18px;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 20px;
                background: var(--panel);
                box-shadow: 0 12px 28px rgba(40, 18, 33, 0.05);
            }

            .settings-form {
                display: grid;
                gap: 14px;
            }

            .settings-form label {
                display: grid;
                gap: 7px;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .settings-form input {
                width: 100%;
                min-height: 46px;
                padding: 10px 12px;
                border: 1px solid var(--line);
                border-radius: 10px;
                background: #fbf8fb;
                color: var(--text);
                font: inherit;
            }

            .settings-form input:focus {
                outline: 3px solid rgba(138, 61, 120, 0.18);
                border-color: var(--primary);
            }

            .save-button {
                justify-self: start;
                min-height: 44px;
                padding: 10px 16px;
                border: 0;
                border-radius: 10px;
                background: var(--primary-dark);
                color: #fff;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
            }

            .message {
                padding: 12px 14px;
                border-radius: 10px;
                background: #dcfce7;
                color: var(--success);
            }

            .errors {
                padding: 12px 14px;
                border-radius: 10px;
                background: #ffe4e6;
                color: var(--danger);
            }

            @media (max-width: 520px) {
                .settings-header { align-items: flex-start; flex-direction: column; }
                .settings-panel { padding: 18px; }
            }
        </style>
    </head>
    <body>
        <main class="settings-shell">
            <header class="settings-header">
                <div>
                    <h1>Account Settings</h1>
                    <p>Manage your profile and password.</p>
                </div>
                <a class="back-link" href="<?= e(route('dashboard')) ?>">Back to dashboard</a>
            </header>

            <?php if (session('status')) { ?>
                <div class="message" role="status"><?= e(session('status')) ?></div>
            <?php } ?>

            <?php if ($errors->any()) { ?>
                <div class="errors" role="alert">
                    <?php foreach ($errors->all() as $error) { ?>
                        <div><?= e($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <section class="settings-panel" aria-labelledby="profile-heading">
                <div>
                    <h2 id="profile-heading">Profile information</h2>
                    <p class="panel-description">Change the name, email address, and mobile contact number on your account.</p>
                </div>

                <form class="settings-form" method="POST" action="<?= e(route('settings.profile.update')) ?>">
                    <?= csrf_field() ?>
                    <?= method_field('PATCH') ?>

                    <label for="name">
                        Name
                        <input id="name" type="text" name="name" value="<?= e(old('name', $user->name)) ?>" autocomplete="name" maxlength="255" required>
                    </label>

                    <label for="email">
                        Email address
                        <input id="email" type="email" name="email" value="<?= e(old('email', $user->email)) ?>" autocomplete="email" maxlength="255" required>
                    </label>

                    <label for="phone_number">
                        Mobile contact number (09XXXXXXXXX or +639XXXXXXXXX)
                        <input id="phone_number" type="tel" name="phone_number" value="<?= e(old('phone_number', $user->phone_number)) ?>" autocomplete="tel" maxlength="13" pattern="^(\+639[0-9]{9}|09[0-9]{9})$" title="Enter a Philippine mobile number as 09XXXXXXXXX or +639XXXXXXXXX">
                    </label>

                    <button class="save-button" type="submit">Save profile</button>
                </form>
            </section>

            <section class="settings-panel" aria-labelledby="password-heading">
                <div>
                    <h2 id="password-heading">Change password</h2>
                    <p class="panel-description">Enter your current password before choosing a new one.</p>
                </div>

                <form class="settings-form" method="POST" action="<?= e(route('settings.password.update')) ?>">
                    <?= csrf_field() ?>
                    <?= method_field('PUT') ?>

                    <label for="current_password">
                        Current password
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                    </label>

                    <label for="password">
                        New password
                        <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                    </label>

                    <label for="password_confirmation">
                        Confirm new password
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                    </label>

                    <button class="save-button" type="submit">Change password</button>
                </form>
            </section>
        </main>
    </body>
</html>
