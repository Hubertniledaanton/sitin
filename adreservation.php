<?php
session_start();
include("connector.php");

// Debug logging
error_log("adreservation.php started");
error_log("Session data: " . print_r($_SESSION, true));

// Check if admin is logged in
if (!isset($_SESSION['Username'])) {
    error_log("Admin not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['MIDNAME'] . ' ' . $row['LASTNAME']);
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'Admin';
}

// Handle reservation approval
if(isset($_POST['approve'])) {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $room = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $seat_number = $_POST['seat_number'];
    
    // Update reservation status
    $update_query = "UPDATE reservations SET status = 'approved' WHERE id = ?";
    $update_stmt = $con->prepare($update_query);
    $update_stmt->bind_param("i", $reservation_id);
    
    if($update_stmt->execute()) {
        // Get student email for notification
        $email_query = "SELECT EMAIL FROM user WHERE IDNO = ?";
        $email_stmt = $con->prepare($email_query);
        $email_stmt->bind_param("s", $student_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        $student_email = mysqli_fetch_assoc($email_result)['EMAIL'];
        
        // Send email notification
        $to = $student_email;
        $subject = "Reservation Approved - Lab Room $room";
        $message = "Dear Student,\n\n";
        $message .= "Your reservation for Lab Room $room has been approved.\n";
        $message .= "Date: " . date('F j, Y', strtotime($date)) . "\n";
        $message .= "Time: " . date('h:i A', strtotime($time)) . "\n";
        $message .= "Seat Number: $seat_number\n\n";
        $message .= "Please arrive 30 minutes before your scheduled time.\n";
        $message .= "Thank you for using our lab reservation system.\n\n";
        $message .= "Best regards,\nLab Management Team";
        
        $headers = "From: labmanagement@example.com";
        
        mail($to, $subject, $message, $headers);
        
        echo "<script>alert('Reservation approved and notification sent to student.');</script>";
    } else {
        echo "<script>alert('Error approving reservation.');</script>";
    }
}

// Handle reservation rejection
if(isset($_POST['reject'])) {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $room = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // Update reservation status
    $update_query = "UPDATE reservations SET status = 'rejected' WHERE id = ?";
    $update_stmt = $con->prepare($update_query);
    $update_stmt->bind_param("i", $reservation_id);
    
    if($update_stmt->execute()) {
        // Get student email for notification
        $email_query = "SELECT EMAIL FROM user WHERE IDNO = ?";
        $email_stmt = $con->prepare($email_query);
        $email_stmt->bind_param("s", $student_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        $student_email = mysqli_fetch_assoc($email_result)['EMAIL'];
        
        // Send email notification
        $to = $student_email;
        $subject = "Reservation Rejected - Lab Room $room";
        $message = "Dear Student,\n\n";
        $message .= "We regret to inform you that your reservation for Lab Room $room has been rejected.\n";
        $message .= "Date: " . date('F j, Y', strtotime($date)) . "\n";
        $message .= "Time: " . date('h:i A', strtotime($time)) . "\n\n";
        $message .= "Please make a new reservation or contact the lab administrator for more information.\n";
        $message .= "Thank you for your understanding.\n\n";
        $message .= "Best regards,\nLab Management Team";
        
        $headers = "From: labmanagement@example.com";
        
        mail($to, $subject, $message, $headers);
        
        echo "<script>alert('Reservation rejected and notification sent to student.');</script>";
    } else {
        echo "<script>alert('Error rejecting reservation.');</script>";
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$room_filter = isset($_GET['room']) ? $_GET['room'] : '';

// Build the query based on filters
$query = "SELECT r.*, u.FIRSTNAME, u.LASTNAME, u.EMAIL 
          FROM reservations r 
          JOIN user u ON r.student_id = u.IDNO 
          WHERE 1=1";

if (!empty($status_filter)) {
    $query .= " AND r.status = '" . mysqli_real_escape_string($con, $status_filter) . "'";
}

if (!empty($room_filter)) {
    $query .= " AND r.room = '" . mysqli_real_escape_string($con, $room_filter) . "'";
}

$query .= " ORDER BY r.date DESC, r.time DESC";
$result = mysqli_query($con, $query);

// Debug logging
error_log("Reservations query: " . $query);
error_log("Number of reservations found: " . mysqli_num_rows($result));
if (mysqli_error($con)) {
    error_log("SQL Error: " . mysqli_error($con));
}

// Fetch the count of pending reservations
$pendingCount = 0;
$query = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
$result = $con->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    $pendingCount = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Manage Reservations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            background: #0a192f;
            min-height: 100vh;
            width: 100%;
            color: #a0aec0;
        }

        /* Top Navigation Bar Styles */
        .top-nav {
            background-color: #1a2942;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-left img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .nav-left .user-name {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .nav-right {
            display: flex;
            gap: 15px;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .nav-right a i {
            font-size: 1rem;
        }

        .nav-right a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-right .logout-button {
            background: rgba(220, 53, 69, 0.1);
            margin-left: 10px;
        }

        .nav-right .logout-button:hover {
            background: rgba(220, 53, 69, 0.2);
        }

        .content {
            margin-top: 80px;
            padding: 30px;
            min-height: calc(100vh - 80px);
            width: 100%;
        }

        .container {
            background: #1a2942;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .pending-count {
            color: #4a90e2;
            font-size: 1rem;
            margin-top: 5px;
        }

        /* Filter Section */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            background: #2a3b55;
            padding: 15px;
            border-radius: 12px;
            align-items: center;
            flex-wrap: wrap;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label {
            color: white;
            font-size: 0.9rem;
        }

        .filter-select {
            padding: 8px 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            min-width: 150px;
        }

        .filter-select:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .filter-select option {
            background: #1a2942;
            color: white;
        }

        /* Table Styles */
        .table-container {
            background: #1a2942;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: #a0aec0;
        }

        thead {
            background: #2a3b55;
        }

        th {
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-pending {
            background: rgba(255, 170, 0, 0.2);
            color: #ffa500;
        }

        .status-approved {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        /* Content Header */
        .content-header {
            background: #2a3b55;
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .content-header h1 {
            font-size: 24px;
            margin: 0;
            margin-bottom: 15px;
            color: white;
        }

        .nav-button {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .nav-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-button i {
            font-size: 16px;
        }

        .pc-management {
            background: #1a2942;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .control-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #2a3b55;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            gap: 15px;
        }

        .right-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .control-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            height: 36px;
            min-width: 120px;
            justify-content: center;
        }

        .control-btn:hover {
            transform: translateY(-2px);
        }

        .control-btn.select-all {
            background: #4a90e2;
        }

        .control-btn.available {
            background: #28a745;
        }

        .control-btn.used {
            background: #dc3545;
        }

        .control-btn.maintenance {
            background: #ffc107;
            color: #1a2942;
        }

        .room-select {
            padding: 8px 16px;
            border-radius: 6px;
            background: #1a2942;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            min-width: 200px;
            height: 36px;
        }

        .room-select:focus {
            outline: none;
            border-color: #4a90e2;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0a192f;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #2a3b55;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4a90e2;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                margin: 0 15px;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-select {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                padding: 10px;
            }
            
            .nav-left {
                margin-bottom: 10px;
            }
            
            .nav-right {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .nav-right a {
                font-size: 0.8rem;
                padding: 6px 10px;
            }
            
            .content {
                margin-top: 120px;
                padding: 15px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .pc-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 8px;
                padding: 10px;
            }

            .pc-item {
                height: 90px;
                padding: 10px;
            }

            .pc-item i {
                font-size: 24px;
                margin-bottom: 5px;
            }

            .control-panel {
                flex-direction: column;
                gap: 10px;
            }

            .right-controls {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                width: 100%;
            }

            .room-select {
                width: 100%;
            }

            .control-btn {
                width: 100%;
            }
        }

        .pc-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            padding: 20px;
            background: #2a3b55;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
        }

        .pc-item {
            background: #1a2942;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0aec0;
            position: relative;
            height: 120px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .pc-item i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .pc-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .pc-item.selected {
            border: 2px solid #4a90e2;
            background: rgba(74, 144, 226, 0.1);
        }

        .pc-item.available {
            color: #28a745;
            border-color: rgba(40, 167, 69, 0.3);
        }

        .pc-item.used {
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.3);
        }

        .pc-item.maintenance {
            color: #ffc107;
            border-color: rgba(255, 193, 7, 0.3);
        }

        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background: #28a745;
        }

        .notification.error {
            background: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="nav-left">
            <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.src='assets/default.png';">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
        </div>
        <div class="nav-right">
            <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
            <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
            <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Records</a>
            <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
            <a href="adreservation.php" style="position: relative;">
                <i class="fas fa-calendar-check"></i> Reservations
                <?php if ($pendingCount > 0): ?>
                    <span class="notification-badge"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
            <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
            <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback</a>
            <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </div>

    <div class="content">
    <div class="content-header">
        <h1><i class="fas fa-desktop"></i> COMPUTER LABORATORY MANAGEMENT</h1>
        <div class="action-buttons">
            <button class="nav-button" onclick="window.location.href='reservation_requests.php'">
                <i class="fas fa-calendar-check"></i> Reservation Requests
            </button>
            <button class="nav-button" onclick="window.location.href='reservation_logs.php'">
                <i class="fas fa-history"></i> Reservation Logs
            </button>
        </div>
    </div>

    <div class="pc-management">
        <div class="control-panel">
            <div class="left-controls">
                <select class="room-select" onchange="changeRoom(this.value)">
                    <option value="">Select Laboratory Room</option>
                    <option value="524">Room 524</option>
                    <option value="526">Room 526</option>
                    <option value="528">Room 528</option>
                    <option value="530">Room 530</option>
                    <option value="542">Room 542</option>
                    <option value="544">Room 544</option>
                </select>
            </div>
            <div class="right-controls">
                <button onclick="selectAllPCs()" class="control-btn select-all">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button onclick="updatePCStatus('available')" class="control-btn available">
                    <i class="fas fa-check-circle"></i> Set Available
                </button>
                <button onclick="updatePCStatus('used')" class="control-btn used">
                    <i class="fas fa-times-circle"></i> Set Used
                </button>
                <button onclick="updatePCStatus('maintenance')" class="control-btn maintenance">
                    <i class="fas fa-tools"></i> Set Maintenance
                </button>
            </div>
        </div>

        <div class="pc-grid">
    <?php
    $pcsPerRow = 5;
    $totalPCs = 40;

    for ($i = 1; $i <= $totalPCs; $i++) {
        echo '<div class="pc-item available" id="pcItem' . $i . '" onclick="togglePC(' . $i . ')">';
        echo '<i class="fas fa-desktop"></i><br>';
        echo 'PC' . $i;
        echo '</div>';
    }
    ?>
</div>
    </div>
</div>

    <script>
    let selectedRoom = '';

    // Toggle sidebar
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.content').classList.toggle('sidebar-active');
    }

    // PC selection
    function togglePC(pcNumber) {
        if (!selectedRoom) {
            alert('Please select a room first');
            return;
        }
        
        const pcItem = document.querySelector(`#pcItem${pcNumber}`);
        if (pcItem) {
            // Allow selection regardless of current status
            pcItem.classList.toggle('selected');
            console.log(`Toggled PC ${pcNumber} in room ${selectedRoom}`);
        }
    }

    // Update PC status (Available, Used, Maintenance)
    async function updatePCStatus(status) {
        if (!selectedRoom) {
            alert('Please select a room first');
            return;
        }

        const selectedPCs = document.querySelectorAll('.pc-item.selected');
        if (selectedPCs.length === 0) {
            alert('Please select at least one PC');
            return;
        }

        try {
            const pcNumbers = Array.from(selectedPCs).map(item => 
                parseInt(item.id.replace('pcItem', ''))
            );

            console.log('Updating PCs:', pcNumbers, 'to status:', status);

            const response = await fetch('update_pc_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    room: selectedRoom,
                    pcNumbers: pcNumbers,
                    status: status
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                selectedPCs.forEach(item => {
                    // Remove all status classes and add new status
                    item.classList.remove('selected', 'available', 'used', 'maintenance');
                    item.classList.add(status);
                });
                clearSelections();
                showNotification(`PCs updated to ${status} successfully`, 'success');
            } else {
                throw new Error(data.message || 'Failed to update PC status');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        }
    }

    // Room change handler
    async function changeRoom(roomId) {
        if (!roomId) {
            clearSelections();
            return;
        }

        selectedRoom = roomId;
        try {
            const response = await fetch(`get_pc_status.php?room=${roomId}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message);
            }

            // Reset all PCs to available state
            document.querySelectorAll('.pc-item').forEach(item => {
                item.className = 'pc-item available';
            });
            
            // Update PC statuses from database
            data.data.forEach(pc => {
                const pcItem = document.querySelector(`#pcItem${pc.pc_number}`);
                if (pcItem) {
                    pcItem.className = `pc-item ${pc.status}`;
                }
            });

            clearSelections();
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error loading room status: ' + error.message, 'error');
        }
    }

    // Select all PCs
    function selectAllPCs() {
        if (!selectedRoom) {
            alert('Please select a room first');
            return;
        }
        
        const pcItems = document.querySelectorAll('.pc-item');
        const anyUnselected = Array.from(pcItems).some(item => 
            !item.classList.contains('selected')
        );
        
        // Allow selection of all PCs regardless of status
        pcItems.forEach(item => {
            item.classList.toggle('selected', anyUnselected);
        });
    }

    // Clear selections
    function clearSelections() {
        document.querySelectorAll('.pc-item').forEach(item => {
            item.classList.remove('selected');
        });
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        const roomSelect = document.querySelector('.room-select');
        if (roomSelect.value) {
            changeRoom(roomSelect.value);
        }
    });
    // Add this to your existing script section
    function updatePendingCount() {
        fetch('get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (data.count > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('a[href="adreservation.php"]').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    }
                } else {
                    if (badge) {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error updating pending count:', error));
    }

    // Update count every 30 seconds
    setInterval(updatePendingCount, 30000);

    function updatePendingCount() {
        fetch('get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (data.count > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('a[href="adreservation.php"]').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    }
                } else {
                    if (badge) {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error updating pending count:', error));
    }

    // Update count every 30 seconds
    setInterval(updatePendingCount, 30000);

    // Initial update on page load
    document.addEventListener('DOMContentLoaded', updatePendingCount);
    </script>
</body>
</html>