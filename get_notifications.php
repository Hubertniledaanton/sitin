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

// Get notifications
$notif_query = "SELECT id, message, type, created_at, is_read 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10";
$stmt = $con->prepare($notif_query);
$stmt->bind_param("s", $user['IDNO']);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'type' => $row['type'],
        'created_at' => $row['created_at'],
        'is_read' => (bool)$row['is_read']
    ];
}

// Get unread count
$count_query = "SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = FALSE";
$stmt = $con->prepare($count_query);
$stmt->bind_param("s", $user['IDNO']);
$stmt->execute();
$count_result = $stmt->get_result();
$count = $count_result->fetch_assoc()['count'];

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $count
]);