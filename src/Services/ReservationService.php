<?php

namespace App\Services;

use App\Repositories\ReservationRepository;
use MongoDB\BSON\UTCDateTime;

class ReservationService
{
    private ReservationRepository $repo;

    public function __construct()
    {
        $this->repo = new ReservationRepository();
    }

    public function create(string $userId, array $data): string
    {
        // Required fields
        $requiredFields = [
            'date',
            'time',
            'customerName',
            'phone',
            'partySize'
        ];

        foreach ($requiredFields as $field) {
            if (
                !isset($data[$field]) ||
                trim((string) $data[$field]) === ''
            ) {
                throw new \Exception("$field is required");
            }
        }

        // Customer name
        $customerName = trim((string) $data['customerName']);

        if (strlen($customerName) < 2) {
            throw new \Exception(
                'Customer name must be at least 2 characters'
            );
        }

        // Phone
        $phone = trim((string) $data['phone']);

        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            throw new \Exception('Invalid phone number');
        }

        // Party size
        $partySize = (int) $data['partySize'];

        if ($partySize < 1) {
            throw new \Exception(
                'Party size must be at least 1'
            );
        }

        // Date
        $date = trim((string) $data['date']);

        // Time
        $time = trim((string) $data['time']);

        // Validate HH:MM
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            throw new \Exception(
                'Invalid time format. Use HH:MM'
            );
        }

        // Combine date and time
        $reservationDateTime = strtotime(
            "$date $time"
        );

        if ($reservationDateTime === false) {
            throw new \Exception(
                'Invalid reservation date or time'
            );
        }

        // Reservation must be in the future
        if ($reservationDateTime < time()) {
            throw new \Exception(
                'Reservation date and time must be in the future'
            );
        }

        // Create reservation
        $id = $this->repo->create([
            'userId' => $userId,

            'customerName' => $customerName,

            'phone' => $phone,

            'partySize' => $partySize,

            'date' => new UTCDateTime(
                $reservationDateTime * 1000
            ),

            'time' => $time,

            'status' => 'requested',

            'tableNumber' => null,
        ]);

        return (string) $id;
    }

    public function listAll(?string $date = null): array
    {
        return $this->repo->findAll($date);
    }

    public function confirm(
        string $id,
        int $tableNumber
    ): void {

        if ($tableNumber < 1) {
            throw new \Exception(
                'Invalid table number'
            );
        }

        $this->repo->confirm(
            $id,
            $tableNumber
        );
    }

    public function updateStatus(
        string $id,
        string $status
    ): void {

        $valid = [
            'requested',
            'confirmed',
            'seated',
            'completed',
            'cancelled'
        ];

        if (!in_array($status, $valid, true)) {
            throw new \Exception(
                'Invalid status'
            );
        }

        $this->repo->updateStatus(
            $id,
            $status
        );
    }

    public function delete(string $id): void
    {
        $this->repo->delete($id);
    }
}