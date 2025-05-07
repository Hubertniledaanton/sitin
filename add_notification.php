<?php
function addNotification($con, $user_id, $message, $type) {
    $query = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sss", $user_id, $message, $type);
    return $stmt->execute();
}

function checkAndAddSessionNotification($con, $user_id) {
    // Get current points
    $query = "SELECT POINTS FROM user WHERE IDNO = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // If points reach 3, add a session and reset points
    if ($user['POINTS'] >= 3) {
        // Begin transaction
        $con->begin_transaction();
        
        try {
            // Reset points
            $update_points = "UPDATE user SET POINTS = POINTS - 3 WHERE IDNO = ?";
            $stmt = $con->prepare($update_points);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            
            // Add session
            $update_sessions = "UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS + 1 WHERE IDNO = ?";
            $stmt = $con->prepare($update_sessions);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            
            // Add notification
            addNotification($con, $user_id, "You received 1 new session for collecting 3 points!", "session");
            
            $con->commit();
            return true;
        } catch (Exception $e) {
            $con->rollback();
            return false;
        }
    }
    return false;
}