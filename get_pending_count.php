<?php
include("connector.php");

header('Content-Type: application/json');

$query = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
$result = $con->query($query);
$count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
}

echo json_encode(['count' => $count]);
?>