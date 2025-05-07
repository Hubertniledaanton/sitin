<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$room = $data['room'];
$date = $data['date'];
$time = $data['time'];

try {
    // Get PC statuses from admin settings
    $pc_query = "SELECT pc_number, status FROM pc_status WHERE room_number = ?";
    $pc_stmt = $con->prepare($pc_query);
    $pc_stmt->bind_param("s", $room);
    $pc_stmt->execute();
    $pc_result = $pc_stmt->get_result();
    $pc_statuses = $pc_result->fetch_all(MYSQLI_ASSOC);

    // Get reservations for the selected time slot
    $res_query = "SELECT seat_number, status FROM reservations 
                  WHERE room = ? AND date = ? AND time = ?";
    $res_stmt = $con->prepare($res_query);
    $res_stmt->bind_param("sss", $room, $date, $time);
    $res_stmt->execute();
    $res_result = $res_stmt->get_result();
    $reservations = $res_result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'pcStatus' => $pc_statuses,
        'reservations' => $reservations
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>