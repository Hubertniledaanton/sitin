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
    $stmt = $con->prepare("SELECT pc_number, status FROM pc_status WHERE room_number = ?");
    $stmt->bind_param("s", $room);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pc_statuses = [];
    while ($row = $result->fetch_assoc()) {
        $pc_statuses[] = [
            'pc_number' => $row['pc_number'],
            'status' => $row['status']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $pc_statuses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>