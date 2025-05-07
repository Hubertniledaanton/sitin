<?php
function addNotification($con, $user_id, $message, $type) {
    $query = "INSERT INTO notifications (user_id, message, type, created_at) 
              VALUES (?, ?, ?, NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sss", $user_id, $message, $type);
    return $stmt->execute();
}

function addPointsNotification($con, $user_id, $points, $reason = '') {
    $message = "You received {$points} point" . ($points > 1 ? 's' : '');
    if ($reason) {
        $message .= " for {$reason}";
    }
    return addNotification($con, $user_id, $message, 'points');
}

function addSessionNotification($con, $user_id) {
    $message = "Congratulations! You've earned a new session for collecting 3 points!";
    return addNotification($con, $user_id, $message, 'session');
}