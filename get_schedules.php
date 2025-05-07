<?php
session_start();
include("connector.php");

if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$room = isset($_GET['room']) ? $_GET['room'] : 'all';
$query = "SELECT * FROM lab_schedules";
if ($room !== 'all') {
    $query .= " WHERE room_number = ?";
}

try {
    $stmt = $con->prepare($query);
    if ($room !== 'all') {
        $stmt->bind_param("s", $room);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $schedules = [];
    
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    
    echo json_encode(['success' => true, 'schedules' => $schedules]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}