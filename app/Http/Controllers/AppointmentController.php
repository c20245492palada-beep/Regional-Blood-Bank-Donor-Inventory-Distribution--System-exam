<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    private const SERVICES = [
        'screening' => ['name' => 'Blood donation screening', 'fee' => 500],
        'blood-typing' => ['name' => 'Blood typing & checkup', 'fee' => 800],
        'plasma' => ['name' => 'Plasma donation', 'fee' => 1200],
        'consultation' => ['name' => 'Doctor consultation', 'fee' => 1500],
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service' => ['required', Rule::in(array_keys(self::SERVICES))],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_method' => ['required', Rule::in(['Cash', 'GCash', 'Card'])],
        ]);

        $service = self::SERVICES[$validated['service']];

        Appointment::create([
            'user_id' => $request->user()->getAuthIdentifier(),
            'service' => $service['name'],
            'appointment_date' => $validated['appointment_date'],
            'fee' => $service['fee'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Appointment scheduled. Payment is pending and must be completed at the clinic.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $request->user()->appointments()
            ->whereKey($appointment->getKey())
            ->firstOrFail();

        abort_unless($appointment->status === 'Pending', 409, 'Only pending appointments can be cancelled.');

        $appointment->update(['status' => 'Cancelled']);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Appointment cancelled.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $request->user()->appointments()
            ->whereKey($appointment->getKey())
            ->firstOrFail();

        $appointment->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Appointment deleted.');
    }
}
