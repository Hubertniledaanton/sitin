<?php
include("connector.php");

function getUnreadNotifications($user_id) {
    global $con;
    $query = "SELECT * FROM notifications 
              WHERE user_id = ? 
              AND is_read = 0 
              ORDER BY created_at DESC";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function markNotificationAsRead($notification_id) {
    global $con;
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}