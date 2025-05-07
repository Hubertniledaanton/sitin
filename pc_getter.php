<?php
include("connector.php");

$room = $_GET['room'];
$stmt = $con->prepare("SELECT pc_number, status FROM pc_status WHERE room_number = ?");
$stmt->bind_param("s", $room);
$stmt->execute();
$result = $stmt->get_result();

$statuses = [];
while ($row = $result->fetch_assoc()) {
    $statuses[] = $row;
}

header('Content-Type: application/json');
echo json_encode($statuses);
?>