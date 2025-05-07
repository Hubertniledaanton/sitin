<?php
session_start();
date_default_timezone_set('Asia/Manila');
include("connector.php");

if (!isset($_SESSION['Username'])) {
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

// Modify the POST handler section for sit-in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchId = $_POST["searchId"];
    $purpose = $_POST["purpose"];
    $labRoom = $_POST["labRoom"];
    date_default_timezone_set("Asia/Manila");
    $time_in = date("Y-m-d H:i:s");

    // Start transaction
    $con->begin_transaction();

    try {
        // Fetch user details and check remaining sessions
        $user_query = "SELECT FIRSTNAME, MIDNAME, LASTNAME, REMAINING_SESSIONS FROM user WHERE IDNO = ?";
        $stmt = $con->prepare($user_query);
        $stmt->bind_param("s", $searchId);
        $stmt->execute();
        $user_result = $stmt->get_result()->fetch_assoc();

        if ($user_result && $user_result['REMAINING_SESSIONS'] > 0) {
            $fullname = $user_result['FIRSTNAME'] . ' ' . $user_result['MIDNAME'] . ' ' . $user_result['LASTNAME'];
            
            // Insert into login records (removed session deduction)
            $stmt = $con->prepare("INSERT INTO login_records (IDNO, FULLNAME, TIME_IN, PURPOSE, LAB_ROOM) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $searchId, $fullname, $time_in, $purpose, $labRoom);
            $stmt->execute();

            $con->commit();
            header("Location: adsitin.php?success=1");
            exit;
        } else {
            $con->rollback();
            header("Location: adsitin.php?error=no_sessions");
            exit;
        }
    } catch (Exception $e) {
        $con->rollback();
        header("Location: adsitin.php?error=db_error");
        exit;
    }
}

// Modify the logout handler to remove the session deduction
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    date_default_timezone_set("Asia/Manila");
    $time_out = date("Y-m-d H:i:s");

    $con->begin_transaction();

    try {
        // Update TIME_OUT and deduct session
        $stmt = $con->prepare("UPDATE login_records SET TIME_OUT = ? WHERE IDNO = ? AND TIME_OUT IS NULL");
        $stmt->bind_param("ss", $time_out, $id);
        $stmt->execute();

        // Deduct session
        $stmt = $con->prepare("UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS - 1 WHERE IDNO = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();

        $con->commit();
        header("Location: adsitin.php?logout_success=1");
        exit;
    } catch (Exception $e) {
        $con->rollback();
        header("Location: adsitin.php?error=logout_failed");
        exit;
    }
}

// Make sure this is at the top of your file
require_once('includes/notification_helper.php');

// Update the points addition handler
if (isset($_GET['addpoint'])) {
    $id = $_GET['addpoint'];
    date_default_timezone_set("Asia/Manila");
    $time_out = date("Y-m-d H:i:s");
    
    $con->begin_transaction();

    try {
        // Get user data first
        $stmt = $con->prepare("SELECT POINTS, REMAINING_SESSIONS, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE IDNO = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (!$user_data || $user_data['REMAINING_SESSIONS'] <= 0) {
            throw new Exception("No sessions available");
        }

        $current_points = $user_data['POINTS'];
        $fullname = $user_data['FIRSTNAME'] . ' ' . $user_data['MIDNAME'] . ' ' . $user_data['LASTNAME'];

        // Update TIME_OUT first
        $stmt = $con->prepare("UPDATE login_records SET TIME_OUT = ? WHERE IDNO = ? AND TIME_OUT IS NULL");
        $stmt->bind_param("ss", $time_out, $id);
        $stmt->execute();

        // Deduct session
        $stmt = $con->prepare("UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS - 1 WHERE IDNO = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();

        // Add point
        $stmt = $con->prepare("UPDATE user SET POINTS = POINTS + 1 WHERE IDNO = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();

        // Add point notification
        addPointsNotification($con, $id, 1, "completing your lab session");

        // Check if points reach a multiple of 3
        $new_points = $current_points + 1;
        if ($new_points % 3 == 0) {
            // Add session
            $stmt = $con->prepare("UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS + 1 WHERE IDNO = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();

            // Add session notification
            addSessionNotification($con, $id);

            // Record in points history
            $stmt = $con->prepare("INSERT INTO points_history (IDNO, FULLNAME, POINTS_EARNED, CONVERTED_TO_SESSION, CONVERSION_DATE) 
                                 VALUES (?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssi", $id, $fullname, $new_points);
            $stmt->execute();
            
            $reward_message = "&reward=session";
        } else {
            // Record in points history
            $stmt = $con->prepare("INSERT INTO points_history (IDNO, FULLNAME, POINTS_EARNED, CONVERSION_DATE) 
                                 VALUES (?, ?, 1, NOW())");
            $stmt->bind_param("ss", $id, $fullname);
            $stmt->execute();
            
            $reward_message = "&reward=point";
        }

        $con->commit();
        header("Location: adsitin.php?point_success=1" . $reward_message . "&points=" . $new_points);
        exit;

    } catch (Exception $e) {
        $con->rollback();
        header("Location: adsitin.php?error=no_sessions");
        exit;
    }
}

// Add this where you handle adding points
if(isset($_POST['add_points'])) {
    $student_id = $_POST['student_id'];
    $points = $_POST['points'];
    $reason = $_POST['reason'] ?? ''; // Add a reason field in your form
    
    $con->begin_transaction();
    
    try {
        // Add points
        $update_query = "UPDATE user SET POINTS = POINTS + ? WHERE IDNO = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("is", $points, $student_id);
        $stmt->execute();
        
        // Send notification
        addPointsNotification($con, $student_id, $points, $reason);
        
        // Check if points can be converted to sessions
        checkAndConvertPoints($con, $student_id);

        // Function to check and convert points to sessions
        function checkAndConvertPoints($con, $student_id) {
            $stmt = $con->prepare("SELECT POINTS FROM user WHERE IDNO = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $current_points = $user_data['POINTS'];

            if ($current_points % 3 == 0) {
                // Add session
                $stmt = $con->prepare("UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS + 1 WHERE IDNO = ?");
                $stmt->bind_param("s", $student_id);
                $stmt->execute();

                // Add session notification
                addSessionNotification($con, $student_id);
            }
        }
        
        $con->commit();
        echo "<script>alert('Points added successfully!');</script>";
    } catch (Exception $e) {
        $con->rollback();
        echo "<script>alert('Error adding points!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Admin Sit-in Management</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

html, body {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    display: flex;
    flex-direction: column;
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

h1 {
    color: #14569b;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 25px;
}

.table-container {
    height: calc(100% - 140px);
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

.w3-button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9em;
}

.w3-red {
    background: #dc3545;
    color: white;
}

.w3-green {
    background: #28a745;
    color: white;
}

.w3-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.5s ease;
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
    }
    
    .container {
        padding: 15px;
    }
}

/* Add to your existing styles */
.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.5s ease;
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
        <a href="addaily.php"><i class="fas fa-chair"></i> Daily Records</a>
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="adreservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<div class="content">
    <div class="container">
    <h1>Sit-in Management</h1>
    <?php
    if (isset($_GET['success'])) {
        echo "<div class='success-message'>
                ✅ User information successfully added to login records table.
              </div>";
    }
    if (isset($_GET['logout_success'])) {
        echo "<div class='success-message'>
                ✅ Student successfully logged out
              </div>";
    }
    if (isset($_GET['point_success'])) {
        $reward_type = isset($_GET['reward']) ? $_GET['reward'] : '';
        $reward_msg = '';
        
        // Get the user's current points
        if (isset($_GET['points'])) {
            $current_points = intval($_GET['points']);
            if ($reward_type === 'point') {
                $reward_msg = "Student logged out and earned 1 point! (Total points: {$current_points})";
            } else if ($reward_type === 'session') {
                $reward_msg = "Student logged out and earned 1 new session for reaching {$current_points} points!";
            }
        } else {
            $reward_msg = "Student logged out and earned 1 point!";
        }
        echo "<div class='success-message'>✅ " . $reward_msg . "</div>";
    }
    if (isset($_GET['error'])) {
        $error_msg = '';
        switch($_GET['error']) {
            case 'no_sessions':
                $error_msg = "❌ Student has no remaining sessions";
                break;
            case 'db_error':
                $error_msg = "❌ Database error occurred";
                break;
            case 'logout_failed':
                $error_msg = "❌ Failed to logout student";
                break;
        }
        if ($error_msg) {
            echo "<div class='error-message'>$error_msg</div>";
        }
    }
    ?>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID No</th>
                    <th>Full Name</th>
                    <th>Purpose</th>
                    <th>Room</th>
                    <th>Date & Time</th>
                    <th>Sessions</th>
                    <th>Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sitin_result = mysqli_query($con, "SELECT login_records.IDNO, FULLNAME, 
                           TIME_IN, REMAINING_SESSIONS, PURPOSE, LAB_ROOM, 
                           POINTS, CONVERT_TZ(TIME_IN, 'UTC', 'Asia/Manila') as local_time 
                           FROM login_records 
                           JOIN user ON login_records.IDNO = user.IDNO 
                           WHERE TIME_OUT IS NULL 
                           ORDER BY TIME_IN DESC");

while ($sitin_row = mysqli_fetch_assoc($sitin_result)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($sitin_row['IDNO']); ?></td>
        <td><?php echo htmlspecialchars($sitin_row['FULLNAME']); ?></td>
        <td class="purpose-column"><?php echo htmlspecialchars($sitin_row['PURPOSE']); ?></td>
        <td class="room-column"><?php echo htmlspecialchars($sitin_row['LAB_ROOM']); ?></td>
        <td><?php 
            $timestamp = strtotime($sitin_row['TIME_IN']);
            echo date('M d, Y h:i A', $timestamp); 
        ?></td>
        <td><?php echo htmlspecialchars($sitin_row['REMAINING_SESSIONS']); ?></td>
        <td><?php echo htmlspecialchars($sitin_row['POINTS']); ?></td>
        <td>
            <div style="display: flex; gap: 5px;">
                <a href="adsitin.php?addpoint=<?php echo htmlspecialchars($sitin_row['IDNO']); ?>" 
                   class="w3-button w3-green" 
                   title="Add 1 point">Reward</a>
                <a href="adsitin.php?id=<?php echo htmlspecialchars($sitin_row['IDNO']); ?>" 
                   class="w3-button w3-red">Logout</a>
            </div>
        </td>
    </tr>
<?php } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
if (window.location.href.indexOf("success=1") > -1) {
    setTimeout(function() {
        window.history.replaceState(null, null, window.location.pathname);
    }, 3000); // Clears success message after 3 seconds
}
</script>
</body>
</html>