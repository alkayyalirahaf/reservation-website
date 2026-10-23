<?php

class TimeSlot
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Get all upcoming available slots with remaining capacity
    public function getAvailable($filterDate = null)
    {
        // use LEFT JOIN to count only 'pending' and 'confirmed' reservations
        // use CURDATE() and CURTIME() to exclude past slots
        $sql = "
            SELECT 
                ts.id, 
                ts.slot_date, 
                ts.start_time, 
                ts.end_time, 
                ts.capacity,
                COUNT(r.id) AS booked_count,
                (ts.capacity - COUNT(r.id)) AS remaining_capacity
            FROM time_slots ts
            LEFT JOIN reservations r 
                ON ts.id = r.time_slot_id 
                AND r.status IN ('pending', 'confirmed')
            WHERE 
                (ts.slot_date > CURDATE() OR (ts.slot_date = CURDATE() AND ts.start_time > CURTIME()))
        ";

        // Add date filter if provided
        if ($filterDate) {
            $sql .= " AND ts.slot_date = ?";
        }

        $sql .= " GROUP BY ts.id ORDER BY ts.slot_date ASC, ts.start_time ASC";

        $stmt = $this->pdo->prepare($sql);
        
        if ($filterDate) {
            $stmt->execute([$filterDate]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // Get a single slot by ID (used in booking page)
    public function findById($id)
    {
        $sql = "SELECT * FROM time_slots WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }



        // Get all slots for admin (including past ones)
    public function getAll()
    {
        $sql = "
            SELECT ts.*, 
                   (SELECT COUNT(*) FROM reservations r WHERE r.time_slot_id = ts.id AND r.status IN ('pending', 'confirmed')) AS active_reservations
            FROM time_slots ts
            ORDER BY ts.slot_date DESC, ts.start_time DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Create a new slot
    public function create($slot_date, $start_time, $end_time, $capacity)
    {
        $errors = [];

        // 1. Validate inputs
        if (empty($slot_date) || empty($start_time) || empty($end_time) || empty($capacity)) {
            $errors[] = "All fields are required.";
        }
        
        if (!is_numeric($capacity) || $capacity < 1) {
            $errors[] = "Capacity must be a number greater than 0.";
        }

        if ($start_time >= $end_time) {
            $errors[] = "End time must be after start time.";
        }

        // Ensure slot is not in the past
        $slotDateTime = $slot_date . ' ' . $start_time;
        if (strtotime($slotDateTime) < time()) {
            $errors[] = "Cannot create a time slot in the past.";
        }

        if (!empty($errors)) return $errors;

        // 2. Insert
        $sql = "INSERT INTO time_slots (slot_date, start_time, end_time, capacity) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$slot_date, $start_time, $end_time, $capacity]);
            return [];
        } catch (PDOException $e) {
            // Handle duplicate slot error (UNIQUE constraint)
            if ($e->getCode() == 23000) {
                return ["This time slot already exists on that date."];
            }
            return ["Database error: Could not create slot."];
        }
    }

    // Delete a slot (only if no active reservations)
    public function delete($id)
    {
        // 1. Check for active reservations
        $sql = "SELECT COUNT(*) AS active_count FROM reservations WHERE time_slot_id = ? AND status IN ('pending', 'confirmed')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $activeCount = $stmt->fetch()['active_count'];

        if ($activeCount > 0) {
            return ["Cannot delete this slot because it has active reservations."];
        }

        // 2. Delete old cancelled/rejected reservations first (Foreign Key constraint)
        $sql = "DELETE FROM reservations WHERE time_slot_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        // 3. Delete the slot
        $sql = "DELETE FROM time_slots WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return [];
    }



        // Update a time slot's details
    public function update($id, $slot_date, $start_time, $end_time, $capacity)
    {
        $errors = [];

        // 1. Basic Validation
        if (empty($slot_date) || empty($start_time) || empty($end_time) || empty($capacity)) {
            $errors[] = "All fields are required.";
        }
        if (!is_numeric($capacity) || $capacity < 1) {
            $errors[] = "Capacity must be a number greater than 0.";
        }
        if ($start_time >= $end_time) {
            $errors[] = "End time must be after start time.";
        }

        // 2. Business Rule: Capacity cannot drop below active reservations
        $sql = "SELECT COUNT(*) AS active_count FROM reservations WHERE time_slot_id = ? AND status IN ('pending', 'confirmed')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $activeCount = $stmt->fetch()['active_count'];

        if ($capacity < $activeCount) {
            $errors[] = "Capacity cannot be less than the number of active reservations ($activeCount).";
        }

        if (!empty($errors)) return $errors;

        // 3. Update the slot
        $sql = "UPDATE time_slots SET slot_date = ?, start_time = ?, end_time = ?, capacity = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$slot_date, $start_time, $end_time, $capacity, $id]);
            return [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["Another slot already exists with this date and time."];
            }
            return ["Database error: Could not update slot."];
        }
    }
}