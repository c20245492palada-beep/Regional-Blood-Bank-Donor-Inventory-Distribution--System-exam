<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blood Bank Dashboard</title>
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

        .page-shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 36px 20px 56px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
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

        .appointment-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .appointment-actions form {
            margin: 0;
        }

        .appointment-action {
            border: 0;
            border-radius: 8px;
            padding: 7px 10px;
            font: inherit;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .appointment-action:focus-visible {
            outline: 2px solid var(--primary-dark);
            outline-offset: 2px;
        }

        .appointment-action-cancel {
            background: var(--primary-soft);
            color: var(--primary-dark);
        }

        .appointment-action-delete {
            background: var(--danger-soft);
            color: var(--danger);
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
            .stats-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .topbar, .hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark">+</div>
                <span>BSIS-3B Blood Bank</span>
            </div>
            <div class="account-actions">
                <div class="account-pill">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Log out</button>
                </form>
            </div>
        </div>

        <header class="hero">
            <div>
                <h1>Blood Bank Dashboard</h1>
                <p>Track donor inventory, open hospital requests, and distribution activity across the network.</p>
            </div>
            <div class="hero-badge">{{ now()->format('d M Y') }}</div>
        </header>

        @if (session('status'))
            <div class="flash-message" role="status">{{ session('status') }}</div>
        @endif

        <section class="booking-panel panel" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h2>Book an appointment</h2>
                <span class="panel-tag">Payment required</span>
            </div>

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="booking-body">
                <form method="POST" action="{{ route('appointments.store') }}" class="appointment-form">
                    @csrf
                    <div class="booking-grid">
                        <label>
                            <span>Clinic service</span>
                            <select name="service" id="service">
                                <option value="screening">Blood donation screening</option>
                                <option value="blood-typing">Blood typing & checkup</option>
                                <option value="plasma">Plasma donation</option>
                                <option value="consultation">Doctor consultation</option>
                            </select>
                        </label>

                        <label>
                            <span>Preferred date</span>
                            <input type="date" name="appointment_date" min="{{ now()->toDateString() }}" value="{{ now()->addDay()->format('Y-m-d') }}" required />
                        </label>

                        <label>
                            <span>Payment method at the clinic</span>
                            <select name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash at clinic</option>
                                <option value="Card">Card at clinic</option>
                            </select>
                        </label>

                        <div class="fee-box">
                            <span>Estimated fee</span>
                            <strong id="fee-display">₱500.00</strong>
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
                <span class="panel-tag">{{ $appointments->count() }} total</span>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td><strong>{{ $appointment->service }}</strong></td>
                                <td>{{ $appointment->appointment_date->format('d M Y') }}</td>
                                <td>₱{{ number_format((float) $appointment->fee, 2) }}</td>
                                <td>{{ $appointment->payment_method }} · {{ $appointment->payment_status }}</td>
                                <td>
                                    {{ $appointment->status }}
                                    @if ($appointment->status === 'Pending')
                                        <div class="appointment-actions">
                                            <form method="POST" action="{{ route('appointments.cancel', $appointment) }}">
                                                @csrf
                                                <button type="submit" class="appointment-action appointment-action-cancel">Cancel</button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" onsubmit="return confirm('Delete this appointment? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="appointment-action appointment-action-delete">Delete</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted" style="padding: 24px 22px;">No appointments yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="stats-grid" aria-label="Key health indicators">
            <article class="stat-card">
                <div class="stat-title">
                    <span>Donors</span>
                    <span class="stat-icon">👥</span>
                </div>
                <p class="stat-value">{{ $stats['donors'] }}</p>
                <div class="stat-delta">Active registered donors</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Available</span>
                    <span class="stat-icon">🩸</span>
                </div>
                <p class="stat-value">{{ $stats['available_bags'] }}</p>
                <div class="stat-delta">Bags ready for issue</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Requests</span>
                    <span class="stat-icon">📋</span>
                </div>
                <p class="stat-value">{{ $stats['pending_requests'] }}</p>
                <div class="stat-delta">Open hospital requests</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Units issued</span>
                    <span class="stat-icon">✅</span>
                </div>
                <p class="stat-value">{{ $stats['issued_units'] }}</p>
                <div class="stat-delta">Cross-matched units</div>
            </article>

            <article class="stat-card">
                <div class="stat-title">
                    <span>Revenue</span>
                    <span class="stat-icon">₦</span>
                </div>
                <p class="stat-value">{{ number_format($stats['collection_total'], 2) }}</p>
                <div class="stat-delta">Processing fee total</div>
            </article>
        </section>

        <section class="content-grid">
            <article class="panel">
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
                        @forelse ($recentDistributions as $distribution)
                            <tr>
                                <td>
                                    <strong>{{ $distribution->bloodBag?->blood_bag_code ?? '—' }}</strong>
                                    <span class="muted">{{ $distribution->issued_at?->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <strong>{{ $distribution->bloodBag?->donor?->donor_name ?? '—' }}</strong>
                                    <span class="muted">{{ $distribution->bloodBag?->donor?->blood_type ?? '—' }}{{ $distribution->bloodBag?->donor?->rh_factor ?? '' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $distribution->hospitalRequest?->hospital?->hospital_name ?? '—' }}</strong>
                                    <span class="muted">{{ $distribution->hospitalRequest?->blood_type ?? '—' }}{{ $distribution->hospitalRequest?->rh_factor ?? '' }}</span>
                                </td>
                                <td>{{ $distribution->units_issued }}</td>
                                <td>
                                    <span class="status-pill status-completed">{{ $distribution->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted" style="padding: 24px 22px;">No distribution records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </article>

            <aside class="panel">
                <div class="panel-header">
                    <h2>Available stock</h2>
                    <span class="panel-tag">By group</span>
                </div>

                <div class="stock-list">
                    @forelse ($stockByGroup as $group)
                        <div class="stock-item">
                            <div class="stock-meta">
                                <div class="blood-badge">{{ $group->blood_group }}</div>
                                <div>
                                    <div style="font-weight: 700;">{{ $group->blood_group }}</div>
                                    <div class="muted">Ready to issue</div>
                                </div>
                            </div>
                            <div class="stock-count">{{ $group->total }}</div>
                        </div>
                    @empty
                        <div class="muted" style="padding-top: 12px;">No available bags in the inventory.</div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>

    <script>
        const feeMap = {
            screening: 500,
            'blood-typing': 800,
            plasma: 1200,
            consultation: 1500,
        };

        const serviceSelect = document.getElementById('service');
        const feeDisplay = document.getElementById('fee-display');

        if (serviceSelect && feeDisplay) {
            const updateFee = () => {
                const selected = serviceSelect.value;
                const amount = feeMap[selected] ?? 0;
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
