<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['reservation_id'])) {
        throw new Exception('Reservation ID not provided');
    }

    $reservation_id = $data['reservation_id'];
    
    // Verify reservation belongs to current user
    $check_query = "SELECT student_id FROM reservations 
                   WHERE id = ? AND status = 'pending'";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Reservation not found or already processed');
    }
    
    $reservation = $result->fetch_assoc();
    
    // Update reservation status
    $update_query = "UPDATE reservations SET status = 'cancelled' WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("i", $reservation_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to cancel reservation');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>