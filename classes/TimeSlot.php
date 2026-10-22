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
        // We use LEFT JOIN to count only 'pending' and 'confirmed' reservations
        // We use CURDATE() and CURTIME() to exclude past slots
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
}