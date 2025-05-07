<?php
session_start();
include("connector.php");

header('Content-Type: application/json');

if (!isset($_SESSION['Username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$username = $_SESSION['Username'];

// Get user ID
$query = "SELECT IDNO FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Mark all as read
$update = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
$stmt = $con->prepare($update);
$stmt->bind_param("s", $user['IDNO']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update notifications']);
}