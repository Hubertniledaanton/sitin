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

        html, body {
            background: #0a192f;
            display: flex;
            flex-direction: column;
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
            color: #a0aec0;
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
            color: white;
        }

        .nav-right .logout-button {
            background: rgba(220, 53, 69, 0.1);
            margin-left: 10px;
        }

        .nav-right .logout-button:hover {
            background: rgba(220, 53, 69, 0.2);
        }

        /* Content Area */
        .content {
            margin-top: 80px;
            padding: 30px;
            min-height: calc(100vh - 80px);
        }

        .content-wrapper {
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 1400px;
            margin: 0 auto;
        }

        .content-header {
            background: #2a3b55;
            color: white;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .content-header h1 {
            font-size: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .content-body {
            padding: 1.5rem;
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
            background: #2a3b55;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .requests-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .requests-table tr:hover {
            background: #2a3b55;
        }

        /* Action Buttons */
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .approve-btn {
            background: #22c55e;
            color: white;
        }

        .reject-btn {
            background: #ef4444;
            color: white;
            margin-left: 0.5rem;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .nav-right {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-right a {
                font-size: 0.8rem;
                padding: 6px 12px;
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
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left">
            <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
        </div>
        <div class="nav-right">
            <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
            <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
            <a href="addaily.php"><i class="fas fa-chair"></i> Daily Records</a>
            <a href="viewReports.php"><i class="fas fa-eye"></i> Sitin Reports</a>
            <a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
            <a href="adlabresources.php"><i class="fas fa-book"></i> Resources</a>
            <a href="adlabsched.php"><i class="fas fa-calendar"></i> Schedule</a>
            <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </nav>

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