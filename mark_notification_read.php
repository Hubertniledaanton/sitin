<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['error' => 'Notification ID required']);
    exit;
}

$query = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $data['id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update notification']);
}