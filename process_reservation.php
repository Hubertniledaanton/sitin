<?php
session_start();
date_default_timezone_set('Asia/Manila');
include("connector.php");

header('Content-Type: application/json');

// Debug logging
error_log("Process reservation started");
error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION['Username'])) {
    error_log("Not authorized - session username missing");
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if(isset($_POST['action']) && isset($_POST['reseid'])) {
    $reservation_id = $_POST['reseid'];
    $status = $_POST['action'];
    
    error_log("Processing reservation ID: $reservation_id with status: $status");
    
    try {
        $con->begin_transaction();
        
        if($status === 'approved') {
            // Get reservation details
            $stmt = $con->prepare("SELECT r.*, u.FIRSTNAME, u.MIDNAME, u.LASTNAME 
                                 FROM reservations r 
                                 JOIN user u ON r.student_id = u.IDNO 
                                 WHERE r.id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows === 0) {
                error_log("Reservation not found: $reservation_id");
                throw new Exception("Reservation not found");
            }
            
            $reservation = $result->fetch_assoc();

            // Format student name
            $fullname = $reservation['LASTNAME'] . ', ' . $reservation['FIRSTNAME'] . ' ' . substr($reservation['MIDNAME'], 0, 1) . '.';
            
            error_log("Approving reservation for: $fullname");
            
            // Update reservation status
            $stmt = $con->prepare("UPDATE reservations SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            
            if($stmt->affected_rows === 0) {
                error_log("Failed to update reservation status");
                throw new Exception("Failed to update reservation status");
            }

            // Insert into login_records
            $stmt = $con->prepare("INSERT INTO login_records 
                                (IDNO, FULLNAME, PURPOSE, LAB_ROOM, TIME_IN) 
                                VALUES (?, ?, ?, ?, ?)");
            $current_time = (new DateTime())->format('Y-m-d H:i:s');  // Get current Manila time
            $stmt->bind_param("sssss", 
                $reservation['student_id'],
                $fullname,
                $reservation['purpose'],
                $reservation['room'],
                $current_time  // Use current time instead of reservation time
            );
            $stmt->execute();
            
            if($stmt->affected_rows === 0) {
                error_log("Failed to insert into login_records");
                throw new Exception("Failed to insert login record");
            }

            // Update PC status to 'used' since admin approved the reservation
            $update_pc_stmt = $con->prepare("UPDATE pc_status 
                                           SET status = 'used', 
                                           last_updated = NOW() 
                                           WHERE room_number = ? 
                                           AND pc_number = ?");
            $update_pc_stmt->bind_param("si", 
                $reservation['room'],
                $reservation['seat_number']
            );
            $update_pc_stmt->execute();

            // If no pc_status record exists, create one
            if($update_pc_stmt->affected_rows === 0) {
                $insert_pc_stmt = $con->prepare("INSERT INTO pc_status 
                                               (room_number, pc_number, status, last_updated) 
                                               VALUES (?, ?, 'used', NOW())");
                $insert_pc_stmt->bind_param("si", 
                    $reservation['room'],
                    $reservation['seat_number']
                );
                $insert_pc_stmt->execute();
            }

            // Send notification for approved reservation
            $notif_message = "Your reservation has been approved";
            $notif_details = json_encode([
                'room' => $reservation['room'],
                'pc_number' => $reservation['seat_number'],
                'date' => date('M d, Y', strtotime($reservation['date'])),
                'time' => date('h:i A', strtotime($reservation['time']))
            ]);

            $insert_notif = $con->prepare("INSERT INTO notifications (user_id, message, type, details, is_read, created_at) 
                                          VALUES (?, ?, 'approval', ?, 0, NOW())");
            $insert_notif->bind_param("sss", 
                $reservation['student_id'], 
                $notif_message, 
                $notif_details
            );
            $insert_notif->execute();

            $con->commit();
            error_log("Reservation $reservation_id approved successfully");
            echo json_encode([
                'success' => true,
                'message' => 'Reservation approved successfully'
            ]);

        } else if($status === 'pending') {
            // Update PC status to 'reserved' when pending
            $update_pc_stmt = $con->prepare("UPDATE pc_status 
                                           SET status = 'reserved', 
                                           last_updated = NOW() 
                                           WHERE room_number = ? 
                                           AND pc_number = ?");
            $update_pc_stmt->bind_param("si", 
                $reservation['room'],
                $reservation['seat_number']
            );
            $update_pc_stmt->execute();

            // If no pc_status record exists, create one with 'reserved' status
            if($update_pc_stmt->affected_rows === 0) {
                $insert_pc_stmt = $con->prepare("INSERT INTO pc_status 
                                               (room_number, pc_number, status, last_updated) 
                                               VALUES (?, ?, 'reserved', NOW())");
                $insert_pc_stmt->bind_param("si", 
                    $reservation['room'],
                    $reservation['seat_number']
                );
                $insert_pc_stmt->execute();
            }
        } else {
            // Get reservation details first
            $stmt = $con->prepare("SELECT r.*, u.FIRSTNAME, u.MIDNAME, u.LASTNAME 
                                  FROM reservations r 
                                  JOIN user u ON r.student_id = u.IDNO 
                                  WHERE r.id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows === 0) {
                error_log("Reservation not found: $reservation_id");
                throw new Exception("Reservation not found");
            }
            
            $reservation = $result->fetch_assoc();

            // Update reservation status to rejected
            $update_stmt = $con->prepare("UPDATE reservations SET status = 'rejected' WHERE id = ?");
            $update_stmt->bind_param("i", $reservation_id);
            $update_stmt->execute();
            
            if($update_stmt->affected_rows === 0) {
                error_log("Failed to reject reservation");
                throw new Exception("Failed to reject reservation");
            }

            // Send notification for rejected reservation
            $notif_message = "Your reservation has been rejected";
            $notif_details = json_encode([
                'room' => $reservation['room'],
                'pc_number' => $reservation['seat_number'],
                'date' => date('M d, Y', strtotime($reservation['date'])),
                'time' => date('h:i A', strtotime($reservation['time']))
            ]);

            $insert_notif = $con->prepare("INSERT INTO notifications (user_id, message, type, details, is_read, created_at) 
                                          VALUES (?, ?, 'rejection', ?, 0, NOW())");
            $insert_notif->bind_param("sss", 
                $reservation['student_id'], 
                $notif_message, 
                $notif_details
            );
            $insert_notif->execute();
            
            $con->commit();
            error_log("Reservation $reservation_id rejected successfully");
            echo json_encode([
                'success' => true,
                'message' => 'Reservation rejected successfully'
            ]);
        }
        
    } catch (Exception $e) {
        $con->rollback();
        error_log("Error processing reservation: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

error_log("Invalid request to process_reservation.php");
echo json_encode(['success' => false, 'message' => 'Invalid request']);