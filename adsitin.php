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
<title>Admin Sit-in Management</title>
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
    width: 100%;
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    background: #1a2942;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    height: calc(100vh - 140px);
    display: flex;
    flex-direction: column;
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
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    gap: 1.5rem;
}

/* Form Styles */
.form-container {
    background: #2a3b55;
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: white;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: #1a2942;
    color: #a0aec0;
    font-size: 0.95rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

.submit-btn {
    background: #4a90e2;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: #357abd;
    transform: translateY(-2px);
}

/* Table Styles */
.table-container {
    flex: 1;
    overflow: auto;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: #2a3b55;
}

.sitin-table {
    width: 100%;
    border-collapse: collapse;
}

.sitin-table th {
    background: #1a2942;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 500;
    position: sticky;
    top: 0;
    z-index: 10;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sitin-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    color: #a0aec0;
}

.sitin-table tr:hover {
    background: #1a2942;
}

/* Action Buttons */
.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.logout-btn {
    background: #f56565;
    color: white;
}

.points-btn {
    background: #48bb78;
    color: white;
    margin-left: 0.5rem;
}

.action-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

/* Alert Messages */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: rgba(72, 187, 120, 0.2);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.3);
}

.alert-error {
    background: rgba(245, 101, 101, 0.2);
    color: #f56565;
    border: 1px solid rgba(245, 101, 101, 0.3);
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

    .form-container {
        padding: 1rem;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
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
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="adreservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </nav>

    <div class="content">
        <div class="content-wrapper">
            <div class="content-header">
                <h1><i class="fas fa-users"></i> Current Sit-in Students</h1>
            </div>
            <div class="content-body">
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Student successfully logged in!
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php 
                            if($_GET['error'] == 'no_sessions') echo "Student has no remaining sessions.";
                            else if($_GET['error'] == 'db_error') echo "Database error occurred.";
                            else if($_GET['error'] == 'logout_failed') echo "Failed to log out student.";
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['point_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                            echo "Point added successfully! ";
                            if(isset($_GET['reward']) && $_GET['reward'] == 'session') {
                                echo "Student earned an extra session for reaching " . $_GET['points'] . " points!";
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="searchId">Student ID:</label>
                            <input type="text" id="searchId" name="searchId" required>
                        </div>
                        <div class="form-group">
                            <label for="purpose">Purpose:</label>
                            <select id="purpose" name="purpose" required>
                                <option value="">Select Purpose</option>
                                <option value="Research">Research</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Project">Project</option>
                                <option value="Self-Study">Self-Study</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="labRoom">Lab Room:</label>
                            <select id="labRoom" name="labRoom" required>
                                <option value="">Select Room</option>
                                <option value="524">524</option>
                                <option value="526">526</option>
                                <option value="528">528</option>
                                <option value="530">530</option>
                                <option value="542">542</option>
                                <option value="544">544</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Log In Student</button>
                    </form>
                </div>

                <div class="table-container">
                    <table class="sitin-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Room</th>
                                <th>Purpose</th>
                                <th>Time In</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM login_records WHERE TIME_OUT IS NULL ORDER BY TIME_IN DESC";
                            $result = mysqli_query($con, $query);
                            while($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['IDNO']); ?></td>
                                <td><?php echo htmlspecialchars($row['FULLNAME']); ?></td>
                                <td>Room <?php echo htmlspecialchars($row['LAB_ROOM']); ?></td>
                                <td><?php echo htmlspecialchars($row['PURPOSE']); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['TIME_IN'])); ?></td>
                                <td>
                                    <a href="?id=<?php echo $row['IDNO']; ?>" class="action-btn logout-btn">
                                        <i class="fas fa-sign-out-alt"></i> Log Out
                                    </a>
                                    <a href="?addpoint=<?php echo $row['IDNO']; ?>" class="action-btn points-btn">
                                        <i class="fas fa-plus-circle"></i> Add Point
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>