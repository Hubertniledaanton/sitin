<?php
include("connector.php");

session_start();
// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
header("Location: login.php");
exit();
}

// Fetch user details
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
$user_name = 'User';
}
$stmt->close();

// Get the lab room and time from URL parameters
$lab_room = isset($_GET['lab_room']) ? $_GET['lab_room'] : '';
$time_in = isset($_GET['time_in']) ? $_GET['time_in'] : '';

// If no URL parameters, get user's recent sit-in room
if (empty($lab_room) || empty($time_in)) {
$username = $_SESSION['Username'];
$room_query = "SELECT lr.LAB_ROOM, lr.TIME_IN
               FROM login_records lr
               INNER JOIN user u ON lr.IDNO = u.IDNO
               WHERE u.USERNAME = ?
               ORDER BY lr.TIME_IN DESC
               LIMIT 1";
$room_stmt = $con->prepare($room_query);
$room_stmt->bind_param("s", $username);
$room_stmt->execute();
$room_result = $room_stmt->get_result();

if ($room_result && $room_result->num_rows > 0) {
$room_row = $room_result->fetch_assoc();
$lab_room = $room_row['LAB_ROOM'];
$time_in = $room_row['TIME_IN'];
}
$room_stmt->close();
}

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback'])) {
$feedback = $_POST["feedback"];
$submit_lab_room = $_POST["lab_room"];
$submit_time_in = $_POST["time_in"];

// Get the user's ID from the database using their username
$username = $_SESSION['Username'];
$id_query = "SELECT IDNO FROM user WHERE USERNAME = ?";
$id_stmt = $con->prepare($id_query);
$id_stmt->bind_param("s", $username);
$id_stmt->execute();
$id_result = $id_stmt->get_result();
$user_row = mysqli_fetch_assoc($id_result);
$user_id = $user_row['IDNO'];
$id_stmt->close();

// Check if feedback already exists for this sit-in session
$check_query = "SELECT * FROM feedback WHERE USER_ID = ? AND LAB_ROOM = ? AND DATE(CREATED_AT) = DATE(?)";
$check_stmt = $con->prepare($check_query);
$check_stmt->bind_param("iss", $user_id, $submit_lab_room, $submit_time_in);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
echo "<script>
        alert('You have already submitted feedback for this session!');
        window.location.href = 'history.php';
      </script>";
} else {
// Insert the feedback
$stmt = $con->prepare("INSERT INTO feedback (FEEDBACK, USER_ID, LAB_ROOM, CREATED_AT) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $feedback, $user_id, $submit_lab_room, $submit_time_in);

if ($stmt->execute()) {
echo "<script>
        alert('Feedback submitted successfully!');
        window.location.href = 'history.php';
      </script>";
exit();
} else {
echo "<script>
        alert('Failed to submit feedback!');
        window.location.href = 'feedback.php';
      </script>";
}
$stmt->close();
}
$check_stmt->close();
}

// Fetch feedback for admin view
$feedback_query = "SELECT FEEDBACK, LAB_ROOM, CREATED_AT FROM feedback ORDER BY CREATED_AT DESC";
$feedback_result = mysqli_query($con, $feedback_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Feedback</title>
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
    background: #0a192f;
}

.container {
    background: #1a2942;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.header {
    background: #2a3b55;
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    color: white;
}

.feedback-form {
    padding: 25px;
}

.room-info {
    background: #2a3b55;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 10px;
    color: #a0aec0;
}

.room-info i {
    color: #4a90e2;
    font-size: 1.2rem;
}

textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    resize: vertical;
    min-height: 150px;
    margin: 15px 0;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #2a3b55;
    color: #a0aec0;
}

textarea:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

textarea::placeholder {
    color: #718096;
}

.submit-btn {
    background: #4a90e2;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.submit-btn:hover {
    background: #357abd;
    transform: translateY(-2px);
}

.warning-message {
    background: rgba(255, 243, 205, 0.1);
    color: #ffd700;
    padding: 15px 20px;
    border-radius: 10px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255, 215, 0, 0.2);
}

.warning-message i {
    font-size: 1.2rem;
    color: #ffd700;
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
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="viewAnnouncement.php"><i class="fas fa-bullhorn"></i> View Announcement</a>
            <a href="sitinrules.php"><i class="fas fa-book"></i> Sit-in Rules</a>
            <a href="labRules&Regulations.php"><i class="fas fa-flask"></i> Lab Rules & Regulations</a>
            <a href="history.php"><i class="fas fa-history"></i> Sit-in History</a>
            <a href="reservation.php"><i class="fas fa-calendar-alt"></i> Reservation</a>
            <a href="viewremaining.php"><i class="fas fa-clock"></i> View Remaining Session</a>
            <a href="login.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-comments"></i> Submit Feedback</h1>
            </div>
            
            <div class="feedback-form">
                <?php if (!empty($lab_room)): ?>
                    <div class="room-info">
                        <i class="fas fa-door-open"></i>
                        <p>Submitting feedback for Lab Room <?php echo htmlspecialchars($lab_room); ?></p>
                    </div>
                    
                    <form action="feedback.php" method="POST">
                        <input type="hidden" name="time_in" value="<?php echo htmlspecialchars($time_in); ?>">
                        <input type="hidden" name="lab_room" value="<?php echo htmlspecialchars($lab_room); ?>">
                        <textarea name="feedback" placeholder="Share your experience with the laboratory..." required></textarea>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Submit Feedback
                        </button>
                    </form>
                <?php else: ?>
                    <div class="warning-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>You need to have a sit-in session before submitting feedback.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>