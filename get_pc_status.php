<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

if (!isset($_GET['room'])) {
    echo json_encode(['success' => false, 'message' => 'Room number is required']);
    exit;
}

try {
    $room = $_GET['room'];
    
    // First get all PCs and set them as available by default
    $pc_statuses = array();
    for ($i = 1; $i <= 40; $i++) {
        $pc_statuses[] = [
            'pc_number' => $i,
            'status' => 'available'
        ];
    }

    // Then get actual statuses from database
    $stmt = $con->prepare("SELECT pc_number, status FROM pc_status WHERE room_number = ?");
    $stmt->bind_param("s", $room);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Update statuses for PCs that have records
    while ($row = $result->fetch_assoc()) {
        $index = $row['pc_number'] - 1;
        if (isset($pc_statuses[$index])) {
            $pc_statuses[$index]['status'] = $row['status'];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'data' => $pc_statuses,
        'room' => $room
    ]);

} catch (Exception $e) {
    error_log("Error in get_pc_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error loading PC status: ' . $e->getMessage()
    ]);
}
?>