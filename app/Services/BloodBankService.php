<?php

namespace App\Services;

use App\Models\BloodBag;
use App\Models\DistributionLog;
use App\Models\Donor;
use App\Models\HospitalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BloodBankService
{
    public function createBloodBag(array $data): BloodBag
    {
        return DB::transaction(function () use ($data) {
            $donor = Donor::query()->lockForUpdate()->findOrFail($data['donor_id']);
            $this->normalizeDonor($donor);

            return BloodBag::create([
                'donor_id' => $donor->id,
                'blood_bag_code' => trim(strip_tags($data['blood_bag_code'])),
                'donation_date' => $data['donation_date'],
            ]);
        });
    }

    public function updateBloodBag(int $id, array $data): BloodBag
    {
        return DB::transaction(function () use ($id, $data) {
            $bag = BloodBag::query()->lockForUpdate()->findOrFail($id);

            if ($bag->status !== 'Available') {
                throw ValidationException::withMessages([
                    'blood_bag' => 'Only available blood bags can be edited.',
                ]);
            }

            if (isset($data['donor_id'])) {
                $donor = Donor::query()->lockForUpdate()->findOrFail($data['donor_id']);
                $this->normalizeDonor($donor);
                $bag->donor_id = $donor->id;
            }

            if (isset($data['blood_bag_code'])) {
                $bag->blood_bag_code = trim(strip_tags($data['blood_bag_code']));
            }

            if (isset($data['donation_date'])) {
                $bag->donation_date = $data['donation_date'];
            }

            $bag->save();

            return $bag->load('donor');
        });
    }

    public function distribute(array $data): DistributionLog
    {
        return DB::transaction(function () use ($data) {
            $bag = BloodBag::query()
                ->lockForUpdate()
                ->findOrFail($data['blood_bag_id']);
            $request = HospitalRequest::query()
                ->lockForUpdate()
                ->findOrFail($data['hospital_request_id']);

            if ($bag->status !== 'Available') {
                throw ValidationException::withMessages([
                    'blood_bag_id' => 'This blood bag is not available for distribution.',
                ]);
            }

            $donor = Donor::query()->lockForUpdate()->findOrFail($bag->donor_id);
            $this->normalizeDonor($donor);

            [$donorType, $donorRh] = $this->normalizeBloodGroup(
                $donor->blood_type,
                $donor->rh_factor
            );
            [$recipientType, $recipientRh] = $this->normalizeBloodGroup(
                $request->blood_type,
                $request->rh_factor
            );

            if (!$this->isCompatible($donorType, $donorRh, $recipientType, $recipientRh)) {
                throw ValidationException::withMessages([
                    'blood_bag_id' => 'The selected blood bag is not compatible with the hospital request.',
                ]);
            }

            $request->blood_type = $recipientType;
            $request->rh_factor = $recipientRh;

            if (!in_array($request->status, ['Pending', 'Partial'], true)) {
                throw ValidationException::withMessages([
                    'hospital_request_id' => 'This hospital request is no longer open for distribution.',
                ]);
            }

            $issued = (int) $request->distributionLogs()
                ->where('status', 'Completed')
                ->sum('units_issued');

            if ($issued >= $request->units_requested) {
                throw ValidationException::withMessages([
                    'hospital_request_id' => 'The requested number of units has already been issued.',
                ]);
            }

            $request->status = $issued + 1 >= $request->units_requested ? 'Fulfilled' : 'Partial';
            $request->save();

            $log = DistributionLog::create([
                'blood_bag_id' => $bag->id,
                'hospital_request_id' => $request->id,
                'units_issued' => 1,
                'processing_fee' => number_format((float) $data['processing_fee'], 2, '.', ''),
                'status' => 'Completed',
                'issued_at' => now(),
            ]);

            $bag->status = 'Issued';
            $bag->save();

            return $log->load(['bloodBag.donor', 'hospitalRequest.hospital']);
        });
    }

    public function updateDistributionLog(int $id, array $data): DistributionLog
    {
        return DB::transaction(function () use ($id, $data) {
            $log = DistributionLog::query()->lockForUpdate()->findOrFail($id);

            if (isset($data['processing_fee'])) {
                $log->processing_fee = number_format((float) $data['processing_fee'], 2, '.', '');
                $log->save();
            }

            return $log->load(['bloodBag.donor', 'hospitalRequest.hospital']);
        });
    }

    public function formatDonorId(string $donorId): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim(strip_tags($donorId))));
    }

    public function bloodGroup(Donor $donor): string
    {
        $this->normalizeDonor($donor);

        return $donor->blood_type . $donor->rh_factor;
    }

    private function normalizeDonor(Donor $donor): void
    {
        $donor->donor_national_id = $this->formatDonorId($donor->donor_national_id);
        $donor->donor_name = trim(strip_tags($donor->donor_name));
        [$donor->blood_type, $donor->rh_factor] = $this->normalizeBloodGroup(
            $donor->blood_type,
            $donor->rh_factor
        );
        $donor->save();
    }

    /**
     * @return array{string, string}
     */
    private function normalizeBloodGroup(string $bloodType, string $rhFactor): array
    {
        $bloodType = strtoupper(trim(strip_tags($bloodType)));

        if (!preg_match('/^(AB|A|B|O)([+-])?$/', $bloodType, $matches)) {
            throw ValidationException::withMessages([
                'blood_type' => 'Blood type must be A, B, AB, or O, optionally followed by an RH sign.',
            ]);
        }

        $rhFactor = strtoupper(trim(strip_tags($rhFactor)));
        $rhFactor = match ($rhFactor) {
            '+', 'POSITIVE', 'POS' => '+',
            '-', 'NEGATIVE', 'NEG' => '-',
            default => throw ValidationException::withMessages([
                'rh_factor' => 'RH factor must be positive (+) or negative (-).',
            ]),
        };

        if (isset($matches[2]) && $matches[2] !== $rhFactor) {
            throw ValidationException::withMessages([
                'rh_factor' => 'The RH factor must match the sign included in the blood type.',
            ]);
        }

        return [$matches[1], $rhFactor];
    }

    private function isCompatible(
        string $donorType,
        string $donorRh,
        string $recipientType,
        string $recipientRh
    ): bool {
        $aboCompatible = match ($donorType) {
            'O' => true,
            'A' => in_array($recipientType, ['A', 'AB'], true),
            'B' => in_array($recipientType, ['B', 'AB'], true),
            'AB' => $recipientType === 'AB',
        };

        return $aboCompatible && ($donorRh === '-' || $recipientRh === '+');
    }
}
