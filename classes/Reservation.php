<?php

class Reservation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new reservation
    public function create($userId, $timeSlotId, $address, $note = null)
    {
        $errors = [];

        // 1. Basic Validation
        if (empty(trim($address))) {
            $errors[] = "Address is required.";
        }
        if (!empty($errors)) return $errors;

        try {
            // Start Transaction (locks the database until commit/rollback)
            $this->pdo->beginTransaction();

            // 2. Lock the slot row to prevent race conditions
            $sql = "SELECT * FROM time_slots WHERE id = ? FOR UPDATE";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$timeSlotId]);
            $slot = $stmt->fetch();

            if (!$slot) {
                $this->pdo->rollBack();
                return ["Time slot not found."];
            }

            // 3. Check if slot is in the past
            $slotDateTime = $slot['slot_date'] . ' ' . $slot['start_time'];
            if (strtotime($slotDateTime) < time()) {
                $this->pdo->rollBack();
                return ["You cannot book a time slot in the past."];
            }

            // 4. Check if user already booked this slot (pending/confirmed only)
            $sql = "SELECT id FROM reservations WHERE user_id = ? AND time_slot_id = ? AND status IN ('pending', 'confirmed')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $timeSlotId]);
            if ($stmt->fetch()) {
                $this->pdo->rollBack();
                return ["You have already booked this time slot."];
            }

            // 5. Check Capacity
            $sql = "SELECT COUNT(*) AS booked FROM reservations WHERE time_slot_id = ? AND status IN ('pending', 'confirmed')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$timeSlotId]);
            $bookedCount = $stmt->fetch()['booked'];

            if ($bookedCount >= $slot['capacity']) {
                $this->pdo->rollBack();
                return ["This time slot is fully booked."];
            }

            // 6. Insert the reservation
            $sql = "INSERT INTO reservations (user_id, time_slot_id, address, note, status) VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $timeSlotId, $address, $note]);

            // Commit Transaction (save changes)
            $this->pdo->commit();
            return []; // Success

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ["Database error: Could not create reservation."];
        }
    }
}