<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

$inTransaction = false; // Track transaction state

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['room']) || !isset($data['pcNumbers']) || !isset($data['status'])) {
        throw new Exception('Missing required data');
    }

    $room = $data['room'];
    $pcNumbers = $data['pcNumbers'];
    $status = $data['status'];

    $con->begin_transaction();
    $inTransaction = true; // Set flag when transaction starts

    $stmt = $con->prepare("INSERT INTO pc_status (room_number, pc_number, status, last_updated) 
                          VALUES (?, ?, ?, NOW()) 
                          ON DUPLICATE KEY UPDATE status = ?, last_updated = NOW()");

    foreach ($pcNumbers as $pcNumber) {
        $stmt->bind_param("siss", $room, $pcNumber, $status, $status);
        $stmt->execute();
    }

    $con->commit();
    $inTransaction = false; // Reset flag after successful commit
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);

} catch (Exception $e) {
    if ($inTransaction) {
        $con->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>