<?php
session_start();
include("connector.php");

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Get username
$username = $_SESSION['Username'];

// Fetch profile picture, remaining sessions, and user's name
$query = "SELECT PROFILE_PIC, REMAINING_SESSIONS, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
$remaining_sessions = $row['REMAINING_SESSIONS'] ?? 30; // Default to 30 if NULL
$user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>View Remaining Sessions</title>
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }
        html, body {
            background: url('b1.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            overflow: hidden;
        }
.sidebar {
    width: 250px;
    background-color: #578FCA;
    color: white;
    height: 100vh;
    padding: 20px;
    box-sizing: border-box;
    overflow-y: auto;
    position: fixed;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
    -webkit-box-shadow:0px 0px 105px 45px rgba(30,139,247,0.9);
    -moz-box-shadow: 0px 0px 105px 45px rgba(30,139,247,0.9);
    box-shadow: 0px 0px 105px 45px rgba(30,139,247,0.9);
}
.sidebar.active {
    transform: translateX(0);
}
.sidebar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: block;
    margin: 0 auto 20px;
}
.sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px 0;
    margin: 10px 0;
    text-align: left;
}
.sidebar a:hover {
    background-color: #2e9ecf;
}
.burger {
    position: absolute;
    top: 20px;
    left: 20px;
    cursor: pointer;
    z-index: 1000;
}
.burger div {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 5px;
    transition: 0.3s;
}
.content {
    flex-grow: 1;
    margin-left: 300px;
    padding: 20px;
    width: calc(100% - 250px);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
}
.content.sidebar-active {
    margin-left: 420px;
    width: 100%;
}
.announcement-container {
    background: rgba(255, 255, 255, 0.2);
    margin: 40px auto;
    padding: 0px;
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    width: 900px;
    max-width: 1000px;
    text-align: center;
}
.announcement-title {
    background: #1D5AA8;
    color: white;
    padding: 10px;
    font-size: 20px;
    font-weight: bold;
    border-radius: 6px 6px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.announcement-title i {
    margin-left: 310px;
}
.announcement-item {
    margin-top: 15px;
    padding: 15px;
    font-size: 18px;
}
.announcement-content {
    background: rgba(245, 244, 244, 0.59);
    padding: 15px;
    border-radius: 4px;
}
.back-button {
    background-color: #ff4d4d;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
}
.back-button:hover {
    background-color: #e60000;
}
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .content {
        margin-left: 0;
        width: 100%;
    }
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
    <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture">
    <center><div class="user-name"><?php echo $user_name; ?></div></center>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <a href="viewAnnouncement.php"><i class="fas fa-bullhorn"></i> View Announcement</a>
    <a href="sitinrules.php"><i class="fas fa-book"></i> Sit-in Rules</a>
    <a href="labRules&Regulations.php"><i class="fas fa-flask"></i> Lab Rules & Regulations</a>
    <a href="history.php"><i class="fas fa-history"></i> Sit-in History</a>
    <a href="reservation.php"><i class="fas fa-calendar-alt"></i> Reservation</a>
    <a href="viewremaining.php"><i class="fas fa-clock"></i> View Remaining Session</a>
    <a href="viewremaining.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>

<div class="content">
    <div class="announcement-container">
        <div class="announcement-title">
            <i class="fas fa-clock">&nbsp;&nbsp;View Remaining Sessions</i> 
            <a href="dashboard.php" class="back-button">X</a>
        </div>
        <div class="announcement-item">
            <div class="announcement-content">
                <p>You have <strong><?php echo $remaining_sessions; ?></strong> remaining sessions.</p>
            </div>
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