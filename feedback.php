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

// Get user's recent sit-in room
$username = $_SESSION['Username'];
$room_query = "SELECT lr.LAB_ROOM
FROM login_records lr
INNER JOIN user u ON lr.IDNO = u.IDNO
WHERE u.USERNAME = ?
ORDER BY lr.TIME_IN DESC
LIMIT 1";
$room_stmt = $con->prepare($room_query);
$room_stmt->bind_param("s", $username);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$recent_room = '';
if ($room_result && $room_result->num_rows > 0) {
$room_row = $room_result->fetch_assoc();
$recent_room = $room_row['LAB_ROOM'];
}
$room_stmt->close();

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$feedback = $_POST["feedback"];
$time_in = $_POST["time_in"];
$lab_room = $_POST["lab_room"];

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
$check_query = "SELECT * FROM feedback WHERE USER_ID = ? AND DATE(CREATED_AT) = DATE(?)";
$check_stmt = $con->prepare($check_query);
$check_stmt->bind_param("is", $user_id, $time_in);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
echo "<script>alert('You have already submitted feedback for this session!');</script>";
} else {
// Insert the feedback with the user's ID, lab room, and time
$stmt = $con->prepare("INSERT INTO feedback (FEEDBACK, USER_ID, LAB_ROOM, CREATED_AT) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $feedback, $user_id, $lab_room, $time_in);

if ($stmt->execute()) {
echo "<script>alert('Feedback submitted successfully!'); window.location.href='history.php';</script>";
} else {
echo "<script>alert('Failed to submit feedback!');</script>";
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

body {
    display: flex;
    min-height: 100vh;
    background: #f0f2f5;
}

.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 20px;
    transition: all 0.3s ease;
    position: fixed;
    left: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: block;
    margin: 0 auto 20px;
    border: 3px solid rgba(255, 255, 255, 0.2);
}

.sidebar a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-radius: 8px;
    margin: 8px 0;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.sidebar a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.content {
    flex: 1;
    margin-left: 280px;
    padding: 40px;
    background: #f0f2f5;
}

.container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.header {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
}

.header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.feedback-form {
    padding: 25px;
}

.room-info {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

room-info i {
    color: #14569b;
    font-size: 1.2rem;
}

textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    resize: vertical;
    min-height: 150px;
    margin: 15px 0;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

textarea:focus {
    outline: none;
    border-color: #14569b;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
}

.submit-btn {
    background: #14569b;
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

submit-btn:hover {
    background: #0f4578;
    transform: translateY(-2px);
}

.warning-message {
    background: #fff3cd;
    color: #856404;
    padding: 15px 20px;
    border-radius: 10px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.warning-message i {
    font-size: 1.2rem;
}

</style>
</head>
<body>
<div class="burger" onclick="toggleSidebar()">
<div></div>
<div></div>
<div></div>
</div>
<div class="sidebar">
<img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
<center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
<a href="profile.php"><i class="fas fa-user"></i> Profile</a>
<a href="viewAnnouncement.php"><i class="fas fa-bullhorn"></i> View Announcement</a>
<a href="sitinrules.php"><i class="fas fa-book"></i> Sit-in Rules</a>
<a href="labRules&Regulations.php"><i class="fas fa-flask"></i> Lab Rules & Regulations</a>
<a href="history.php"><i class="fas fa-history"></i> Sit-in History</a>
<a href="reservation.php"><i class="fas fa-calendar-alt"></i> Reservation</a>
<a href="viewremaining.php"><i class="fas fa-clock"></i> View Remaining Session</a>
<a href="login.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>
<div class="content">
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-comments"></i> Submit Feedback</h1>
        </div>
        
        <div class="feedback-form">
            <?php if (!empty($recent_room)): ?>
                <div class="room-info">
                    <i class="fas fa-door-open"></i>
                    <p>Submitting feedback for Lab Room <?php echo htmlspecialchars($recent_room); ?></p>
                </div>
                
                <form action="feedback.php" method="POST">
                    <input type="hidden" name="time_in" value="<?php echo isset($_GET['time_in']) ? htmlspecialchars($_GET['time_in']) : ''; ?>">
                    <input type="hidden" name="lab_room" value="<?php echo isset($_GET['lab_room']) ? htmlspecialchars($_GET['lab_room']) : ''; ?>">
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
<script>
function toggleSidebar() {
document.querySelector('.sidebar').classList.toggle('active');
document.querySelector('.content').classList.toggle('sidebar-active');
}
</script>
</body>
</html>