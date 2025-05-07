<?php
session_start();
include("connector.php");

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure MySQL is using the correct timezone
$con->query("SET time_zone = '+08:00'");
$con->query("SET @@session.time_zone = '+08:00'");

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Content Area */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .content-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .content-header {
            background: #14569b;
            color: white;
            padding: 15px 20px;
        }

        .content-header h1 {
            font-size: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-body {
            padding: 20px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }

        .requests-table th {
            background: #f8f9fa;
            color: #14569b;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }

        .requests-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .requests-table tr:hover {
            background: #f8f9fa;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .approve-btn {
            background: #22c55e;
            color: white;
        }

        .reject-btn {
            background: #ef4444;
            color: white;
            margin-left: 8px;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #14569b;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0f4578;
        }

        /* Keep your existing sidebar styles */
        .sidebar {
    width: 250px;
    background-color: rgba(42, 63, 95, 0.9);
    height: 100vh;
    padding: 20px;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 5px 0 10px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    transform: translateX(0);
}

.sidebar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 15px;
}

.sidebar a {
    width: 100%;
    color: white;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 8px;
    margin: 5px 0;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar a i {
    width: 20px;
    text-align: center;
}

.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.sidebar .logout-button {
    margin-top: auto;
    background: rgba(220, 53, 69, 0.1);
}

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
<img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
<center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
<a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
<a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
<a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
<a href="addaily.php"><i class="fas fa-chair"></i> Daily Sitin Records</a>
<a href="viewReports.php"><i class="fas fa-eye"></i> View Sitin Reports</a>
<a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
   <!-- <a href="adlabreward.php"><i class="fas fa-chair"></i> Lab Reward</a>-->
   <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
<a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
<a href="viewReports.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
<a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>

    <div class="content">
        <div class="content-wrapper">
            <div class="content-header">
                <h1><i class="fas fa-calendar-check"></i> Reservation Requests</h1>
            </div>
            
            <div class="content-body">
                <div class="table-container">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Room</th>
                                <th>Seat</th>
                                <th>Date & Time</th>
                                <th>Purpose</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Update the query to order by most recent first
                            $query = "SELECT r.*, u.FIRSTNAME, u.MIDNAME, u.LASTNAME 
                                     FROM reservations r 
                                     JOIN user u ON r.student_id = u.IDNO 
                                     WHERE r.status = 'pending' 
                                     ORDER BY r.id DESC";  // Changed to order by ID descending
                            $result = $con->query($query);

                            while($row = $result->fetch_assoc()):
                                $fullname = $row['LASTNAME'] . ', ' . $row['FIRSTNAME'] . ' ' . substr($row['MIDNAME'], 0, 1) . '.';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($fullname); ?></td>
                                <td>Room <?php echo htmlspecialchars($row['room']); ?></td>
                                <td>PC <?php echo htmlspecialchars($row['seat_number']); ?></td>
                                <td><?php 
                                   date_default_timezone_set('Asia/Manila');
                                   $time = new DateTime(); 
                                   echo $time->format('M d, Y, h:i A'); 
                                ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td>
                                    <button class="action-btn approve-btn" 
                                            onclick="processReservation(<?php echo $row['id']; ?>, 'approved')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="action-btn reject-btn" 
                                            onclick="processReservation(<?php echo $row['id']; ?>, 'rejected')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function processReservation(reservationId, action) {
        if (confirm(`Are you sure you want to ${action} this reservation?`)) {
            // Create form data
            const formData = new FormData();
            formData.append('reseid', reservationId);
            formData.append('action', action);
            
            fetch('process_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            // Update the success handler in the processReservation function
            .then(data => {
                if (data.success) {
                    // Show success toast/alert
                    const message = action === 'approved' ? 
                        'Reservation approved and notification sent to student.' :
                        'Reservation rejected and notification sent to student.';
                    alert(message);
                    
                    if (action === 'approved') {
                        window.location.href = 'adsitin.php';
                    } else {
                        location.reload();
                    }
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error:', error);
            });
        }
    }
    </script>
</body>
</html>