<?php
    $con = mysqli_connect("localhost", "root", "", "sitinmonitoring") or die(mysql_error());
    
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Only declare the function if it hasn't been declared yet
    if (!function_exists('getPendingReservationsCount')) {
        function getPendingReservationsCount($con) {
            try {
                // Add time check to only count future reservations
                $query = "SELECT COUNT(*) as count 
                          FROM reservations 
                          WHERE status = 'pending'
                          AND date >= CURDATE() 
                          AND (date > CURDATE() 
                               OR (date = CURDATE() AND time > CURTIME()))";
                
                $stmt = $con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $row = $result->fetch_assoc();
                    $count = (int)$row['count'];
                    // Ensure we return exactly 0 if there are no pending reservations
                    return ($count > 0) ? $count : 0;
                }
                return 0;
            } catch (Exception $e) {
                error_log("Error getting pending count: " . $e->getMessage());
                return 0;
            }
        }
    }
?>