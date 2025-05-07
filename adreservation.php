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
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            min-height: 100vh;
            width: 100%;
        }

        /* Top Navigation Bar Styles */
        .top-nav {
            background-color: rgba(42, 63, 95, 0.9);
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
            background: #f0f2f5;
        }

        /* Remove old sidebar styles */
        .sidebar {
            display: none;
        }

        .container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: calc(100vh - 60px);
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 20px;
        }

        .header h1 {
            color: #14569b;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        select {
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            min-width: 150px;
        }

        select:focus {
            border-color: #14569b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
        }

        .filter-section button {
            background: #14569b;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-section button:hover {
            background: #0f4578;
            transform: translateY(-1px);
        }

        .reservation-table {
            height: calc(100vh - 250px);
            overflow-y: auto;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        th {
            background: #14569b;
            color: white;
            padding: 15px;
            font-weight: 500;
            text-align: left;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-approve {
            background: #27ae60;
            color: white;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #14569b;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0f4578;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .filter-section {
                flex-direction: column;
            }
            
            select {
                width: 100%;
            }
        }

        /* Update these styles */
.content-header {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 20px;
    border-radius: 15px 15px 0 0;
    margin-bottom: 20px;
}

.content-header h1 {
    font-size: 24px;
    margin: 0;
    margin-bottom: 15px;
}

.action-buttons {
    display: flex;
    gap: 15px;
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
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.control-panel {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    flex-wrap: wrap;
    gap: 15px;
}

.left-controls, .right-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.control-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    color: white;
}

.control-btn.select-all {
    background: #64748b;
}

.control-btn.available {
    background: #22c55e;
}

.control-btn.used {
    background: #ef4444;
}

.control-btn.maintenance {
    background: #f59e0b;
}

.control-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.pc-item {
    position: relative;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.pc-item.selected {
    background: #3b82f6 !important;
    border-color: #2563eb !important;
    color: white;
}

.pc-item.available {
    background: #dcfce7;
    border-color: #22c55e;
}

.pc-item.used {
    background: #fee2e2;
    border-color: #ef4444;
}

.pc-item.maintenance {
    background: #fef3c7;
    border-color: #f59e0b;
}

.pc-label {
    pointer-events: none;
    font-weight: 500;
    display: block;
}

.pc-checkbox {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    margin: 0;
    cursor: pointer;
    z-index: 2;
}

.pc-label {
    pointer-events: none; /* Prevent label from interfering with clicks */
    font-weight: 500;
    color: #4a5568;
    display: block;
}

.pc-item.available {
    background: #dcfce7;
    border-color: #22c55e;
}

.pc-item.used {
    background: #fee2e2;
    border-color: #ef4444;
}

.pc-item.maintenance {
    background: #fef3c7;
    border-color: #f59e0b;
}

.pc-checkbox {
    position: absolute;
    opacity: 0;
}

.pc-label {
    cursor: pointer;
    font-weight: 500;
    color: #4a5568;
}

.pc-checkbox:checked + .pc-label {
    color: #14569b;
}

.pc-item.used {
    background: #fee2e2;
    border-color: #ef4444;
}

.pc-item.available {
    background: #dcfce7;
    border-color: #22c55e;
}

.status-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.status-btn {
    padding: 10px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-btn.available {
    background: #22c55e;
    color: white;
}

.status-btn.used {
    background: #ef4444;
    color: white;
}

.status-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Add these styles to your existing <style> section */
.pc-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
    padding: 20px;
    margin-top: 20px;
}

.pc-item {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
}

.pc-item {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pc-item.selected {
    background: #3b82f6 !important;
    border-color: #2563eb !important;
    color: white;
}

.pc-item.available {
    background: #dcfce7;
    border-color: #22c55e;
}

.pc-item.used {
    background: #fee2e2;
    border-color: #ef4444;
}

.pc-item.maintenance {
    background: #fef3c7;
    border-color: #f59e0b;
}

.control-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 0 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.control-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Add to your existing <style> section in adreservation.php */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 8px;
    color: white;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background: #22c55e;
}

.notification.error {
    background: #ef4444;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.pc-item {
    cursor: pointer;
    transition: all 0.3s ease;
}

.pc-item.selected {
    background: #3b82f6 !important;
    border-color: #2563eb !important;
    color: white;
    transform: scale(1.05);
}

.pc-item.available {
    background: #dcfce7;
    border-color: #22c55e;
}

.pc-item.used {
    background: #fee2e2;
    border-color: #ef4444;
}

.pc-item.maintenance {
    background: #fef3c7;
    border-color: #f59e0b;
}

.notification-badge {
    background: #ef4444;
    color: white;
    border-radius: 50%;
    padding: 1px 5px;
    font-size: 12px;
    position: absolute;
    top: 15px;
    right: 10px;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
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
        <h1><i class="fas fa-desktop"></i> COMPLAB MANAGEMENT</h1>
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