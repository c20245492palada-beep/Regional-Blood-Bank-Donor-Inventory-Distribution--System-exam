<!DOCTYPE html>
<html lang="<?php

use Illuminate\Foundation\Vite;

?><?= e(str_replace('_', '-', app()->getLocale())) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blood Bank Dashboard</title>
    <?= app(Vite::class)(['resources/css/app.css', 'resources/js/app.js']) ?>
    <style>
        :root {
            --bg: #f5f1f6;
            --panel: #ffffff;
            --panel-alt: #f9f5f7;
            --primary: #8a3d78;
            --primary-dark: #6e2859;
            --primary-soft: #ead6eb;
            --text: #2e2033;
            --muted: #73657b;
            --line: rgba(95, 62, 87, 0.12);
            --success: #166534;
            --success-soft: #dcfce7;
            --warning: #9a6700;
            --warning-soft: #fef3c7;
            --danger: #9f1239;
            --danger-soft: #ffe4e6;
            --shadow: 0 16px 40px rgba(90, 51, 79, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(180deg, #f7f1f5 0%, #f1edf2 100%);
            color: var(--text);
        }

        .app-layout {
            display: grid;
            grid-template-columns: 238px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px 14px;
            background: #252936;
            color: #f8fafc;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px;
            color: inherit;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            text-decoration: none;
        }

        .sidebar-mark {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #b25995);
            color: #fff;
            font-size: 1.5rem;
        }

        .sidebar-nav {
            display: grid;
            gap: 6px;
            margin-top: 36px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            color: #cbd0dc;
            font-size: 0.92rem;
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-link:hover,
        .sidebar-link:focus-visible {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
        }

        .sidebar-icon {
            width: 20px;
            flex: 0 0 20px;
            color: #d7a4cd;
            text-align: center;
        }

        .page-shell {
            min-width: 0;
            padding: 36px clamp(16px, 3vw, 40px) 56px;
        }

        .topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #b25995);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .account-pill {
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(138, 61, 120, 0.08);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .account-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            background: var(--primary-dark);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            border-radius: 28px;
            padding: 28px 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            margin-bottom: 26px;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 2.8rem);
            letter-spacing: -0.04em;
        }

        .hero p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.78);
            max-width: 620px;
        }

        .hero-badge {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.24);
            padding: 12px 18px;
            border-radius: 16px;
            font-weight: 700;
            white-space: nowrap;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 18px 18px 16px;
            box-shadow: 0 12px 26px rgba(41, 22, 35, 0.04);
        }

        .stat-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--muted);
            font-size: 0.83rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            font-size: 1.1rem;
            background: var(--primary-soft);
        }

        .stat-value {
            font-size: clamp(1.5rem, 2vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            margin: 0;
        }

        .stat-delta {
            margin-top: 8px;
            color: var(--success);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 22px;
        }

        .blood-stock-forms {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(40, 18, 33, 0.04);
        }

        .panel-header {
            padding: 20px 22px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1.1rem;
        }

        .panel-tag {
            font-size: 0.72rem;
            color: var(--primary);
            background: rgba(138,61,120,0.08);
            border-radius: 999px;
            padding: 7px 10px;
            font-weight: 700;
        }

        .booking-panel {
            overflow: hidden;
        }

        .booking-body {
            padding: 20px 22px 22px;
        }

        .booking-notice,
        .flash-message {
            margin: 18px 22px 0;
            padding: 12px 14px;
            border-radius: 12px;
            color: var(--primary-dark);
            background: rgba(138, 61, 120, 0.08);
            font-size: 0.9rem;
        }

        .form-errors {
            margin: 18px 22px 0;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--danger-soft);
            color: var(--danger);
            font-size: 0.9rem;
        }

        .flash-message {
            margin: 0 0 22px;
            background: var(--success-soft);
            color: var(--success);
        }

        .flash-message.error {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .appointment-form {
            display: block;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .booking-grid label {
            display: grid;
            gap: 8px;
            font-weight: 600;
            color: var(--text);
        }

        .booking-grid input,
        .booking-grid select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            min-height: 46px;
            padding: 0.8rem 0.9rem;
            background: var(--panel-alt);
            color: var(--text);
            font: inherit;
        }

        .fee-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            border: 1px solid rgba(138, 61, 120, 0.18);
            background: rgba(138, 61, 120, 0.06);
            border-radius: 16px;
            padding: 16px;
            min-height: 46px;
        }

        .fee-box span {
            font-size: 0.76rem;
            color: var(--muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .fee-box strong {
            font-size: 1.6rem;
            letter-spacing: -0.04em;
            color: var(--primary-dark);
        }

        .booking-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .booking-actions button {
            border: none;
            border-radius: 12px;
            padding: 0.9rem 1.4rem;
            background: linear-gradient(135deg, var(--primary), #b25995);
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .cancel-button {
            border: 1px solid var(--danger);
            border-radius: 8px;
            padding: 8px 12px;
            background: var(--danger-soft);
            color: var(--danger);
            font: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
        }

        @media (max-width: 640px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }

            .account-actions {
                flex-wrap: wrap;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            text-align: left;
            padding: 16px 22px;
            border-bottom: 1px solid var(--line);
        }

        th {
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        td strong {
            display: block;
            color: var(--text);
        }

        .muted {
            color: var(--muted);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-completed {
            background: var(--success-soft);
            color: var(--success);
        }

        .stock-list {
            padding: 14px 22px 22px;
        }

        .stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
        }

        .stock-item:last-child {
            border-bottom: none;
        }

        .stock-meta {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .blood-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 800;
            background: var(--primary-soft);
            color: var(--primary-dark);
        }

        .stock-count {
            font-weight: 800;
            font-size: 1.1rem;
        }

        @media (max-width: 980px) {
            .app-layout {
                grid-template-columns: 210px minmax(0, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .app-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .sidebar {
                z-index: 10;
                height: auto;
                padding: 14px 16px;
            }

            .sidebar-brand {
                padding: 0;
            }

            .sidebar-nav {
                display: flex;
                gap: 6px;
                overflow-x: auto;
                margin-top: 12px;
                padding-bottom: 2px;
            }

            .sidebar-link {
                flex: 0 0 auto;
                padding: 10px;
                white-space: nowrap;
            }

            .page-shell {
                padding: 20px 14px 36px;
            }

            .topbar, .hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar {
                align-items: stretch;
            }

            .account-actions {
                justify-content: space-between;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <a class="sidebar-brand" href="#dashboard">
                <span class="sidebar-mark" aria-hidden="true">+</span>
                <span>BSIS-3B<br>Blood Bank</span>
            </a>

            <nav class="sidebar-nav" aria-label="Dashboard navigation">
                <a class="sidebar-link" href="#dashboard">
                    <span class="sidebar-icon" aria-hidden="true">⌂</span>
                    <span>Home</span>
                </a>
                <a class="sidebar-link" href="#appointments">
                    <span class="sidebar-icon" aria-hidden="true">◷</span>
                    <span>Appointments</span>
                </a>
                <a class="sidebar-link" href="#health-indicators">
                    <span class="sidebar-icon" aria-hidden="true">▦</span>
                    <span>Overview</span>
                </a>
                <a class="sidebar-link" href="#distribution-activity">
                    <span class="sidebar-icon" aria-hidden="true">⇄</span>
                    <span>Distribution activity</span>
                </a>
                <a class="sidebar-link" href="#blood-stock">
                    <span class="sidebar-icon" aria-hidden="true">◈</span>
                    <span>Blood stock</span>
                </a>
                <a class="sidebar-link" href="#hospital-requests">
                    <span class="sidebar-icon" aria-hidden="true">⌑</span>
                    <span>Hospital requests</span>
                </a>
                <a class="sidebar-link" href="<?= e(route('settings.edit')) ?>">
                    <span class="sidebar-icon" aria-hidden="true">⚙</span>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <main class="page-shell">
        <div class="topbar">
            <div class="account-actions">
                <div class="account-pill"><?= e(auth()->user()->name) ?></div>
                <form method="POST" action="<?= e(route('logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="logout-button" type="submit">Log out</button>
                </form>
            </div>
        </div>

        <header class="hero" id="dashboard">
            <div>
                <h1>Blood Bank Dashboard</h1>
                <p>Track donor inventory, open hospital requests, and distribution activity across the network.</p>
            </div>
            <div class="hero-badge"><?= e(now()->format('d M Y')) ?></div>
        </header>

        <?php if (session('status')) { ?>
            <div class="flash-message" role="status"><?= e(session('status')) ?></div>
        <?php } ?>
        <?php if (session('error')) { ?>
            <div class="flash-message error" role="alert"><?= e(session('error')) ?></div>
        <?php } ?>
        <section class="booking-panel panel" id="appointments" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h2>Book an appointment</h2>
                <span class="panel-tag">Payment required</span>
            </div>

            <?php if ($errors->any()) { ?>
                <div class="form-errors" role="alert">
                    <?php foreach ($errors->all() as $error) { ?>
                        <div><?= e($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="booking-body">
                <form method="POST" action="<?= e(route('appointments.store')) ?>" class="appointment-form">
                    <?= csrf_field() ?>
                    <div class="booking-grid">
                        <label>
                            <span>Clinic service</span>
                            <select name="service" id="service">
                                <?php foreach ($appointmentServices as $serviceKey => $service) { ?>
                                    <option value="<?= e($serviceKey) ?>" data-fee="<?= e($service['fee']) ?>"><?= e($service['name']) ?></option>
                                <?php } ?>
                            </select>
                        </label>

                        <label>
                            <span>Preferred date</span>
                            <input type="date" name="appointment_date" min="<?= e(now()->toDateString()) ?>" value="<?= e(now()->addDay()->format('Y-m-d')) ?>" required />
                        </label>

                        <label>
                            <span>Payment method at the clinic</span>
                            <select name="payment_method" required>
                                <?php foreach ($paymentMethods as $paymentMethod => $label) { ?>
                                    <option value="<?= e($paymentMethod) ?>"><?= e($label) ?></option>
                                <?php } ?>
                            </select>
                        </label>

                        <div class="fee-box">
                            <span>Estimated fee</span>
                            <strong id="fee-display">₱<?= e(number_format(array_values($appointmentServices)[0]['fee'], 2)) ?></strong>
                        </div>
                    </div>

                    <div class="booking-actions">
                        <button type="submit">Schedule appointment</button>
                    </div>
                </form>
            </div>
            <p class="booking-notice">Your appointment and fee will be recorded. Payment is not charged online; please pay at the clinic using your selected method.</p>
        </section>

        <section class="panel" style="margin-bottom: 28px;">
            <div class="panel-header">
                <h2>My appointments</h2>
                <span class="panel-tag"><?= e($appointments->count()) ?> total</span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Fee</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments->isEmpty()) { ?>
                            <tr>
                                <td colspan="6" class="muted" style="padding: 24px 22px;">No appointments yet.</td>
                            </tr>
                        <?php } else { ?>
                        <?php foreach ($appointments as $appointment) { ?>
                            <tr>
                                <td><strong><?= e($appointment->service) ?></strong></td>
                                <td><?= e($appointment->appointment_date->format('d M Y')) ?></td>
                                <td>₱<?= e(number_format((float) $appointment->fee, 2)) ?></td>
                                <td><?= e($appointment->payment_method) ?> · <?= e($appointment->payment_status) ?></td>
                                <td><?= e($appointment->status) ?></td>
                                <td>
                                    <?php if ($appointment->status === 'Pending') { ?>
                                        <form method="POST" action="<?= e(route('appointments.cancel', $appointment)) ?>" onsubmit="return confirm('Cancel this appointment?')">
                                            <?= csrf_field() ?>
                                            <button class="cancel-button" type="submit">Cancel</button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="muted">—</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel booking-panel" style="margin-bottom: 28px;" id="blood-stock-forms">
            <div class="panel-header">
                <h2>Manage blood stock</h2>
                <span class="panel-tag">Donor registry &amp; donations</span>
            </div>

            <div class="booking-body content-grid blood-stock-forms">
                <div>
                    <h3>Register donor</h3>
                    <form method="POST" action="<?= e(route('donors.store')) ?>" class="appointment-form">
                        <?= csrf_field() ?>
                        <div class="booking-grid">
                            <label>
                                <span>Donor ID</span>
                                <input name="donor_national_id" maxlength="50" value="<?= e(old('donor_national_id')) ?>" required />
                            </label>
                            <label>
                                <span>Full name</span>
                                <input name="donor_name" maxlength="150" value="<?= e(old('donor_name')) ?>" required />
                            </label>
                            <label>
                                <span>Blood type</span>
                                <select name="blood_type" required>
                                    <?php foreach (['A', 'B', 'AB', 'O'] as $bloodType) { ?>
                                        <option value="<?= e($bloodType) ?>" <?= old('blood_type', 'O') === $bloodType ? 'selected' : '' ?>><?= e($bloodType) ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <span>RH factor</span>
                                <select name="rh_factor" required>
                                    <option value="+" <?= old('rh_factor', '+') === '+' ? 'selected' : '' ?>>Positive (+)</option>
                                    <option value="-" <?= old('rh_factor') === '-' ? 'selected' : '' ?>>Negative (-)</option>
                                </select>
                            </label>
                        </div>
                        <div class="booking-actions">
                            <button type="submit">Register donor</button>
                        </div>
                    </form>
                </div>

                <div>
                    <h3>Record donation as blood bag</h3>
                    <form method="POST" action="<?= e(route('blood-bags.store')) ?>" class="appointment-form">
                        <?= csrf_field() ?>
                        <div class="booking-grid">
                            <label>
                                <span>Donor</span>
                                <select name="donor_id" required>
                                    <option value="">Select a donor</option>
                                    <?php foreach ($donors as $donor) { ?>
                                        <option value="<?= e($donor->id) ?>" <?= (string) old('donor_id') === (string) $donor->id ? 'selected' : '' ?>>
                                            <?= e($donor->donor_name) ?> · <?= e($donor->blood_type) ?><?= e($donor->rh_factor) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <span>Blood bag code</span>
                                <input name="blood_bag_code" maxlength="80" value="<?= e(old('blood_bag_code')) ?>" required />
                            </label>
                            <label>
                                <span>Donation date</span>
                                <input type="date" name="donation_date" max="<?= e(today()->toDateString()) ?>" value="<?= e(old('donation_date', today()->toDateString())) ?>" required />
                            </label>
                        </div>
                        <div class="booking-actions">
                            <button type="submit" <?= $donors->isEmpty() ? 'disabled' : '' ?>>Record donation</button>
                        </div>
                        <?php if ($donors->isEmpty()) { ?>
                            <p class="booking-notice">Register a donor before recording a donation.</p>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </section>

        <section class="panel booking-panel" style="margin-bottom: 28px;" id="hospital-requests">
            <div class="panel-header">
                <h2>Hospital requests &amp; distributions</h2>
                <span class="panel-tag">Live inventory workflow</span>
            </div>

            <div class="booking-body content-grid blood-stock-forms">
                <div>
                    <h3>Create hospital request</h3>
                    <form method="POST" action="<?= e(route('hospital-requests.store')) ?>" class="appointment-form">
                        <?= csrf_field() ?>
                        <div class="booking-grid">
                            <label>
                                <span>Hospital name</span>
                                <input name="hospital_name" maxlength="150" value="<?= e(old('hospital_name')) ?>" required />
                            </label>
                            <label>
                                <span>Request date</span>
                                <input type="date" name="request_date" max="<?= e(today()->toDateString()) ?>" value="<?= e(old('request_date', today()->toDateString())) ?>" required />
                            </label>
                            <label>
                                <span>Blood type</span>
                                <select name="blood_type" required>
                                    <?php foreach (['A', 'B', 'AB', 'O'] as $bloodType) { ?>
                                        <option value="<?= e($bloodType) ?>" <?= old('blood_type', 'O') === $bloodType ? 'selected' : '' ?>><?= e($bloodType) ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <span>RH factor</span>
                                <select name="rh_factor" required>
                                    <option value="+" <?= old('rh_factor', '+') === '+' ? 'selected' : '' ?>>Positive (+)</option>
                                    <option value="-" <?= old('rh_factor') === '-' ? 'selected' : '' ?>>Negative (-)</option>
                                </select>
                            </label>
                            <label>
                                <span>Units requested</span>
                                <input type="number" name="units_requested" min="1" step="1" value="<?= e(old('units_requested', 1)) ?>" required />
                            </label>
                        </div>
                        <div class="booking-actions">
                            <button type="submit">Create request</button>
                        </div>
                        <p class="booking-notice">New hospital names are registered when their first request is submitted.</p>
                    </form>
                </div>

                <div>
                    <h3>Issue blood bag to a request</h3>
                    <form method="POST" action="<?= e(route('distribution-logs.store')) ?>" class="appointment-form">
                        <?= csrf_field() ?>
                        <div class="booking-grid">
                            <label>
                                <span>Available blood bag</span>
                                <select name="blood_bag_id" required>
                                    <option value="">Select a blood bag</option>
                                    <?php foreach ($availableBagsForDistribution as $bag) { ?>
                                        <option value="<?= e($bag->id) ?>" <?= (string) old('blood_bag_id') === (string) $bag->id ? 'selected' : '' ?>>
                                            <?= e($bag->blood_bag_code) ?> · <?= e($bag->donor->donor_name) ?> · <?= e($bag->donor->blood_type) ?><?= e($bag->donor->rh_factor) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <span>Open hospital request</span>
                                <select name="hospital_request_id" required>
                                    <option value="">Select a request</option>
                                    <?php foreach ($openHospitalRequests as $hospitalRequest) { ?>
                                        <option value="<?= e($hospitalRequest->id) ?>" <?= (string) old('hospital_request_id') === (string) $hospitalRequest->id ? 'selected' : '' ?>>
                                            <?= e($hospitalRequest->hospital->hospital_name) ?> · <?= e($hospitalRequest->blood_type) ?><?= e($hospitalRequest->rh_factor) ?> · <?= e($hospitalRequest->units_requested) ?> units · <?= e($hospitalRequest->status) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <span>Processing fee</span>
                                <input type="number" name="processing_fee" min="0" max="99999999.99" step="0.01" value="<?= e(old('processing_fee', '0.00')) ?>" required />
                            </label>
                        </div>
                        <div class="booking-actions">
                            <button type="submit" <?= $availableBagsForDistribution->isEmpty() || $openHospitalRequests->isEmpty() ? 'disabled' : '' ?>>Issue blood bag</button>
                        </div>
                        <?php if ($availableBagsForDistribution->isEmpty() || $openHospitalRequests->isEmpty()) { ?>
                            <p class="booking-notice">An available blood bag and an open hospital request are required.</p>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </section>

        <section class="stats-grid" id="health-indicators" aria-label="Key health indicators">
            <article class="stat-card">
                <div class="stat-title">
                    <span>Accounts</span>
                    <span class="stat-icon">👥</span>
                </div>
                <p class="stat-value"><?= e($stats['users']) ?></p>
                <div class="stat-delta">Registered user accounts</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Available</span>
                    <span class="stat-icon">🩸</span>
                </div>
                <p class="stat-value"><?= e($stats['available_bags']) ?></p>
                <div class="stat-delta">Bags ready for issue</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Requests</span>
                    <span class="stat-icon">📋</span>
                </div>
                <p class="stat-value"><?= e($stats['open_requests']) ?></p>
                <div class="stat-delta">Open hospital requests</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Units issued</span>
                    <span class="stat-icon">✅</span>
                </div>
                <p class="stat-value"><?= e($stats['issued_units']) ?></p>
                <div class="stat-delta">Cross-matched units</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Revenue</span>
                    <span class="stat-icon">₦</span>
                </div>
                <p class="stat-value"><?= e(number_format($stats['collection_total'], 2)) ?></p>
                <div class="stat-delta">Processing fee total</div>
            </article>
        </section>

        <section class="content-grid">
            <article class="panel" id="distribution-activity">
                <div class="panel-header">
                    <h2>Recent distribution activity</h2>
                    <span class="panel-tag">Live log</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Bag</th>
                            <th>Donor</th>
                            <th>Hospital</th>
                            <th>Units</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentDistributions->isEmpty()) { ?>
                            <tr>
                                <td colspan="5" class="muted" style="padding: 24px 22px;">No distribution records yet.</td>
                            </tr>
                        <?php } else { ?>
                        <?php foreach ($recentDistributions as $distribution) { ?>
                            <tr>
                                <td>
                                    <strong><?= e($distribution->bloodBag?->blood_bag_code ?? '—') ?></strong>
                                    <span class="muted"><?= e($distribution->issued_at?->format('d M Y')) ?></span>
                                </td>
                                <td>
                                    <strong><?= e($distribution->bloodBag?->donor?->donor_name ?? '—') ?></strong>
                                    <span class="muted"><?= e($distribution->bloodBag?->donor?->blood_type ?? '—') ?><?= e($distribution->bloodBag?->donor?->rh_factor ?? '') ?></span>
                                </td>
                                <td>
                                    <strong><?= e($distribution->hospitalRequest?->hospital?->hospital_name ?? '—') ?></strong>
                                    <span class="muted"><?= e($distribution->hospitalRequest?->blood_type ?? '—') ?><?= e($distribution->hospitalRequest?->rh_factor ?? '') ?></span>
                                </td>
                                <td><?= e($distribution->units_issued) ?></td>
                                <td>
                                    <span class="status-pill status-completed"><?= e($distribution->status) ?></span>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </article>

            <aside class="panel" id="blood-stock">
                <div class="panel-header">
                    <h2>Available stock</h2>
                    <span class="panel-tag">By group &amp; bag</span>
                </div>

                <div class="stock-list">
                    <?php if ($stockByGroup->isEmpty()) { ?>
                        <div class="muted" style="padding-top: 12px;">No available bags in the inventory.</div>
                    <?php } else { ?>
                    <?php foreach ($stockByGroup as $group) { ?>
                        <div class="stock-item">
                            <div class="stock-meta">
                                <div class="blood-badge"><?= e($group->blood_group) ?></div>
                                <div>
                                    <div style="font-weight: 700;"><?= e($group->blood_group) ?></div>
                                    <div class="muted">Ready to issue</div>
                                </div>
                            </div>
                            <div class="stock-count"><?= e($group->total) ?></div>
                        </div>
                    <?php } ?>
                    <?php } ?>
                </div>

                <div class="stock-list" style="border-top: 1px solid var(--line);">
                    <h3>Recent available bags</h3>
                    <?php if ($availableBags->isEmpty()) { ?>
                        <div class="muted">No available bags recorded yet.</div>
                    <?php } else { ?>
                    <?php foreach ($availableBags as $bag) { ?>
                        <div class="stock-item">
                            <div>
                                <strong><?= e($bag->blood_bag_code) ?></strong>
                                <div class="muted">
                                    <?= e($bag->donor->donor_name) ?> · <?= e($bag->donor->blood_type) ?><?= e($bag->donor->rh_factor) ?>
                                    · Donated <?= e($bag->donation_date->format('d M Y')) ?>
                                </div>
                            </div>
                            <span class="status-pill status-completed">Available</span>
                        </div>
                    <?php } ?>
                    <?php } ?>
                </div>
            </aside>
        </section>
        </main>
    </div>

    <script>
        const serviceSelect = document.getElementById('service');
        const feeDisplay = document.getElementById('fee-display');

        if (serviceSelect && feeDisplay) {
            const updateFee = () => {
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                const amount = selectedOption ? selectedOption.dataset.fee : 0;
                feeDisplay.textContent = '₱' + Number(amount).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            serviceSelect.addEventListener('change', updateFee);
            updateFee();
        }
    </script>
</body>
</html>
