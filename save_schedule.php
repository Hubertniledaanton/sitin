<?php
session_start();
include("connector.php");

// Check if user is logged in
if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Get JSON data from POST request
$json_data = file_get_contents('php://input');
$schedule_data = json_decode($json_data, true);

if (!$schedule_data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Begin transaction
mysqli_begin_transaction($con);

try {
    // First, clear existing schedules for the selected room
    $room = $schedule_data[0]['room'];
    if ($room !== 'all') {
        $clear_query = "DELETE FROM lab_schedules WHERE room_number = ?";
        $clear_stmt = $con->prepare($clear_query);
        $clear_stmt->bind_param("s", $room);
        $clear_stmt->execute();
    }

    // Insert new schedules
    $insert_query = "INSERT INTO lab_schedules (room_number, day_group, time_slot, status) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($insert_query);

    foreach ($schedule_data as $schedule) {
        $stmt->bind_param("ssss", 
            $schedule['room'],
            $schedule['day'],
            $schedule['time'],
            $schedule['status']
        );
        $stmt->execute();
    }

    // Commit transaction
    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Schedule saved successfully']);

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}