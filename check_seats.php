<?php
include("connector.php");

$data = json_decode(file_get_contents('php://input'), true);

$room = $data['room'];
$date = $data['date'];
$time = $data['time'];

$query = "SELECT seat_number FROM reservations 
          WHERE room = ? AND date = ? AND time = ? 
          AND status != 'rejected'";

$stmt = $con->prepare($query);
$stmt->bind_param("sss", $room, $date, $time);
$stmt->execute();
$result = $stmt->get_result();

$taken_seats = [];
while($row = $result->fetch_assoc()) {
    $taken_seats[] = $row['seat_number'];
}

header('Content-Type: application/json');
echo json_encode($taken_seats);
?>