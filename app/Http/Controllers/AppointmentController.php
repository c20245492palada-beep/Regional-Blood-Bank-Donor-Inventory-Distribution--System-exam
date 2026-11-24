<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validated();

        $service = config('appointments.services')[$validated['service']];

        Appointment::create([
            'user_id' => $user->getAuthIdentifier(),
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

        if ($appointment->status !== 'Pending') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Only pending appointments can be cancelled.');
        }

        $appointment->update(['status' => 'Cancelled']);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Appointment cancelled.');
    }
}
