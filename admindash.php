<?php
session_start();
include("connector.php");

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
    $user_name = 'Admin';
}
$stmt->close();

// Handle search
$search_result = null;
$user_not_found = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_id = $_POST['search_id'];
    $search_query = "SELECT * FROM user WHERE IDNO = ?";
    $stmt = $con->prepare($search_query);
    $stmt->bind_param("s", $search_id);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$search_result) {
        $user_not_found = true;
    }
}

// Fetch session value
$session_value = 30; // Default value
if ($search_result) {
    $session_query = "SELECT REMAINING_SESSIONS FROM user WHERE IDNO = ?";
    $stmt = $con->prepare($session_query);
    $stmt->bind_param("s", $search_result['IDNO']);
    $stmt->execute();
    $session_result = $stmt->get_result()->fetch_assoc();
    $session_value = $session_result['REMAINING_SESSIONS'];
    $stmt->close();
}

// Decrease session value on logout
if (isset($_GET['logout'])) {
    $update_session_query = "UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS - 1 WHERE USERNAME = ?";
    $stmt = $con->prepare($update_session_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    session_destroy();
    header("Location: logout.php");
    exit();
}

// Fetch sit-in records for different rooms
$room_query = "SELECT LAB_ROOM, COUNT(*) as count FROM login_records WHERE TIME_OUT IS NULL GROUP BY LAB_ROOM";
$room_result = mysqli_query($con, $room_query);

$rooms = [];
$counts = [];
if ($room_result) {
    while ($row = mysqli_fetch_assoc($room_result)) {
        $rooms[] = $row['LAB_ROOM'];
        $counts[] = $row['count'];
    }
    // Add rooms with zero counts
    $all_rooms = ["524", "526", "528", "530", "542", "544"];
    foreach ($all_rooms as $room) {
        if (!in_array($room, $rooms)) {
            $rooms[] = $room;
            $counts[] = 0;
        }
    }
} else {
    echo "Error: " . mysqli_error($con);
}

// Fetch purpose distribution
$purpose_query = "SELECT PURPOSE, COUNT(*) as count FROM login_records WHERE TIME_OUT IS NULL GROUP BY PURPOSE";
$purpose_result = mysqli_query($con, $purpose_query);

$purposes = [];
$purpose_counts = [];
if ($purpose_result) {
    while ($row = mysqli_fetch_assoc($purpose_result)) {
        $purposes[] = $row['PURPOSE'];
        $purpose_counts[] = $row['count'];
    }
} else {
    echo "Error: " . mysqli_error($con);
}

// Fetch the most active student
$most_active_query = "SELECT 
    IDNO,
    CONCAT(FIRSTNAME, ' ', MIDNAME, ' ', LASTNAME) as FULLNAME,
    (30 - REMAINING_SESSIONS) as SESSIONS_USED,
    POINTS as TOTAL_POINTS,
    PROFILE_PIC
FROM user 
WHERE REMAINING_SESSIONS < 30
ORDER BY REMAINING_SESSIONS ASC
LIMIT 1";

$most_active_result = mysqli_query($con, $most_active_query);
$most_active_student = $most_active_result ? mysqli_fetch_assoc($most_active_result) : null;

// Update the leaderboard query to fetch all students with points
$leaderboardQuery = "SELECT 
    u.IDNO,
    CONCAT(u.FIRSTNAME, ' ', u.MIDNAME, ' ', u.LASTNAME) as FULLNAME,
    (30 - u.REMAINING_SESSIONS) as SESSIONS_USED,
    u.POINTS as TOTAL_POINTS,
    u.PROFILE_PIC
FROM user u 
WHERE u.POINTS > 0
ORDER BY u.POINTS DESC, (30 - u.REMAINING_SESSIONS) DESC";

$leaderboardResult = mysqli_query($con, $leaderboardQuery);
$leaderboard_data = [];
if ($leaderboardResult) {
    while ($row = mysqli_fetch_assoc($leaderboardResult)) {
        $leaderboard_data[] = $row;
    }
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<title>Admin Dashboard</title>
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
    color:rgb(255, 255, 255);
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

.content {
    margin-top: 80px;
    padding: 30px 30px 30px 450px;
    min-height: calc(100vh - 80px);
    background: #0a192f;
    display: flex;
    justify-content: center;
}

/* Remove old sidebar styles */
.sidebar {
    display: none;
}

/* Update notification badge position */
.notification-badge {
    position: relative;
    top: -2px;
    right: -5px;
    margin-left: 5px;
}

/* Responsive adjustments */
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
    }
}

.parent {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;
    gap: 25px;
    max-width: 1600px;
    width: 100%;
    margin: 0;
}

.div1 {
    grid-column: 1 / -1;
    background: #1a2942;
    border-radius: 15px;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 20px;
}

.profile-section img {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    border: 3px solid rgba(255, 255, 255, 0.2);
    object-fit: cover;
}

.welcome-text {
    color: white;
}

.welcome-text p:first-child {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.date {
    font-size: 0.9rem;
    opacity: 0.8;
}

.search-section {
    display: flex;
    gap: 15px;
    align-items: center;
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-container {
    display: flex;
    gap: 5px;
}

.search-container input {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    width: 250px;
    font-size: 0.95rem;
}

.search-container input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.search-container button,
.list-button {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background: #4a90e2;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-container button:hover,
.list-button:hover {
    background: #357abd;
    transform: translateY(-2px);
}

.div2, .div3 {
    background: #1a2942;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    height: 580px;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.div2::before, .div3::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(to right, #14569b, #2a3f5f);
    border-radius: 15px 15px 0 0;
}

.div2 h2, .div3 h2 {
    color: white;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.div2 h2 i, .div3 h2 i {
    color: #4a90e2;
}

.div2 canvas, .div3 canvas {
    flex: 1;
    width: 100% !important;
    height: 100% !important;
    padding: 15px;
}

.div2:hover, .div3:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

@media (max-width: 1200px) {
    .div4 {
        position: relative;
        left: 0;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .content {
        padding: 30px;
    }
    
    .parent {
        grid-template-columns: 1fr;
    }
    
    .div1 {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .profile-section {
        flex-direction: column;
    }
    
    .search-section {
        width: 100%;
        justify-content: center;
    }
    
    .search-form {
        flex-direction: column;
        width: 100%;
        max-width: 400px;
    }
    
    .search-container {
        width: 100%;
    }
    
    .search-container input {
        width: 100%;
    }
    
    .div2, .div3 {
        height: 450px;
    }
}

@media (max-width: 768px) {
    .div1 {
        padding: 20px;
    }
    
    .welcome-text p:first-child {
        font-size: 1.2rem;
    }
    
    .search-container button,
    .list-button {
        width: 100%;
        justify-content: center;
    }
    
    .div2, .div3 {
        height: 400px;
        padding: 25px;
    }
    
    .div2 h2, .div3 h2 {
        font-size: 1.2rem;
        margin-bottom: 20px;
        padding-bottom: 12px;
    }
}

.div4 {
    grid-column: 1;
    grid-row: 1 / 3;
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    border-radius: 15px;
    padding: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: calc(100vh - 100px);
    max-height: 700px;
    margin-left: 0;
    width: 400px;
    position: fixed;
    left: 30px;
}
.points-badge {
    background: linear-gradient(135deg, #0369a1, #0284c7);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(3, 105, 161, 0.3);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.points-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(3, 105, 161, 0.4);
}
@keyframes sparkle {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.leaderboard-item:nth-child(-n+3) .rank i {
    animation: sparkle 2s infinite;
}
/* Rank Icons */
.rank {
    min-width:25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
}

.rank i {
    font-size: 1.1rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.leaderboard {
    flex: 1;
    overflow-y: auto;
    padding-right: 10px;
    margin-top: 15px;
}
.leaderboard-user {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
.points-badge {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    min-width: 100px;
}

.points-badge div {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.leaderboard-item .user-name {
    color: white !important;
    font-size: medium;
}

.leaderboard-item .user-points {
    color: rgba(255, 255, 255, 0.7) !important;
}

.name {
    color: white;
    font-weight: 600;
}

.student-id {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
}

.sessions, .points {
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.85rem;
    color: white;
    text-align: center;
}

.rank-number {
    color: white;
    font-size: 0.9rem;
    font-weight: bold;
}

.leaderboard-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: white;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.student-details {
    display: flex;
    flex-direction: column;
    color: white;
}

.points-badge div {
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
}

.points-badge div:last-child {
    background: #4a90e2 !important;
    color: white !important;
}

.leaderboard-item .user-info .user-name strong {
    background: white;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
    font-size: 1.1rem;
}

/* Chart Legend Text Color */
#roomPieChart, #purposePieChart {
    color: white !important;
}

/* Updated Rankings Design */
.leaderboard-item {
    background: linear-gradient(to right, rgba(25, 122, 225, 0.3), rgba(26, 41, 66, 0.95));
    padding: 12px 15px;
    border-radius: 12px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.leaderboard-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    filter: brightness(1.1);
}

.leaderboard-item:nth-child(1) {
    background: linear-gradient(to right, rgba(108, 162, 220, 0.9), rgba(26, 41, 66, 0.95));
    border-left: 4px solid #FFD700;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.2);
}

.leaderboard-item:nth-child(2) {
    background: linear-gradient(to right, rgba(65, 145, 231, 0.7), rgba(26, 41, 66, 0.95));
    border-left: 4px solid #C0C0C0;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.15);
}

.leaderboard-item:nth-child(3) {
    background: linear-gradient(to right, rgba(25, 122, 225, 0.5), rgba(26, 41, 66, 0.95));
    border-left: 4px solid #CD7F32;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.1);
}

/* Ensure text is visible on darker backgrounds */
.leaderboard-item:nth-child(-n+3) .user-name,
.leaderboard-item:nth-child(-n+3) .user-points,
.leaderboard-item:nth-child(-n+3) .rank {
    color: white !important;
}

.leaderboard-item:nth-child(-n+3) .points-badge div {
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
}

.leaderboard-item:nth-child(-n+3) .points-badge div:last-child {
    background: #4a90e2 !important;
    color: white !important;
}

.leaderboard-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
    flex-shrink: 0;
}
.leaderboard-item:hover .leaderboard-avatar {
    transform: scale(1.1);
    border-color: #4a90e2;
}
.leaderboard-list {
    flex: 1;
    overflow-y: auto;
    padding-right: 10px;
}
.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.search-bar {
    position: fixed;
    top: 20px;
    right: 30px;
    display: flex;
    gap: 10px;
    z-index: 1000;
}
.search-bar input[type="text"] {
    padding: 8px 15px;
    border: none;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 200px;
}
.search-bar button {
    padding: 8px 15px;
    border: none;
    border-radius: 8px;
    background: #14569b;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}
.search-bar button:hover {
    background: #0f4578;
}
@media (max-width: 1200px) {
    .div4 {
        position: relative;
        left: 0;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .content {
        padding: 30px;
    }
    
    .parent {
        grid-template-columns: 1fr;
    }
    
    .div1 {
        grid-column: 1;
    }
    
    .div2, .div3 {
        grid-column: 1;
    }
}
@media (max-width: 768px) {
    .content {
        margin-left: 0;
        padding: 15px;
    }
    
    .parent {
        grid-template-columns: 1fr;
    }
    
    .div1, .div2, .div3, .div4 {
        grid-column: 1;
    }
    
    .search-bar {
        position: relative;
        top: 0;
        right: 0;
        margin-bottom: 20px;
    }
}

/* Add this CSS in your <style> tag */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s;
}

.modal-content {
    background: #1a2942;
    color: #a0aec0;
    margin: 5% auto;
    padding: 25px;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    color: #a0aec0;
    cursor: pointer;
    transition: color 0.2s;
}

.close:hover {
    color: white;
}

.modal-content h3 {
    color: white;
    margin-bottom: 20px;
    font-size: 1.5rem;
    font-weight: 600;
}

.modal-content p {
    margin: 10px 0;
    color: #a0aec0;
}

.modal-content label {
    display: block;
    margin: 15px 0 5px;
    color: white;
    font-weight: 500;
}

.modal-content select,
.modal-content input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.modal-content button {
    background: #4a90e2;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    width: 100%;
    margin-top: 20px;
    transition: background 0.2s;
}

.modal-content button:hover {
    background: #357abd;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        transform: translateY(-10%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.chart-area {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
}
.chart-area canvas {
    max-width: 340px;
    max-height: 340px;
    width: 100% !important;
    height: 100% !important;
    margin: 0 auto;
    display: block;
    background: #1a2942;
    border-radius: 12px;
    padding: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
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
    <div class="parent">
        <div class="div1">
            <div class="profile-section">
                <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Admin Picture">
                <div class="welcome-text">
                    <p>Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
                    <p class="date"><?php date_default_timezone_set("Asia/Manila"); echo date("F j, Y"); ?></p>
                </div>
            </div>
            <div class="search-section">
                <form method="POST" action="" class="search-form">
                    <div class="search-container">
                        <input type="text" name="search_id" placeholder="Search by ID..." required>
                        <button type="submit" name="search"><i class="fas fa-search"></i></button>
                    </div>
                    <button type="button" class="list-button" onclick="window.location.href='liststudent.php'">
                        <i class="fas fa-list"></i> List Students
                    </button>
                </form>
            </div>
        </div>
        <div class="div2">
            <h2><i class="fas fa-door-open"></i> Room Distribution</h2>
            <div class="chart-area"><canvas id="roomPieChart"></canvas></div>
        </div>
        <div class="div3">
            <h2><i class="fas fa-tasks"></i> Purpose Distribution</h2>
            <div class="chart-area"><canvas id="purposePieChart"></canvas></div>
        </div>
        <div class="div4">
            <h2><i class="fas fa-trophy"></i> Student Rankings</h2>
            <div class="leaderboard">
<?php
// Fetch all students by points with consistent ordering (same as dashboard.php)
$leaderboardQuery = "SELECT 
    u.IDNO, 
    u.FIRSTNAME, 
    u.MIDNAME,
    u.LASTNAME, 
    u.PROFILE_PIC, 
    u.POINTS, 
    u.USERNAME,
    u.REMAINING_SESSIONS 
FROM user u 
WHERE u.USERNAME != 'admin' 
ORDER BY u.POINTS DESC, u.REMAINING_SESSIONS ASC, u.LASTNAME ASC";

$leaderboardResult = mysqli_query($con, $leaderboardQuery);
$rank = 1;

if ($leaderboardResult) {
    while ($user = mysqli_fetch_assoc($leaderboardResult)) {
        $profile_pic = !empty($user['PROFILE_PIC']) ? htmlspecialchars($user['PROFILE_PIC']) : 'default.jpg';
        $isCurrentUser = ($user['USERNAME'] === $username); // $username should be set
        $middleInitial = !empty($user['MIDNAME']) ? ' ' . substr($user['MIDNAME'], 0, 1) . '.' : '';
        ?>
        <div class="leaderboard-item">
            <div class="rank">
                <?php 
                if ($rank <= 3) {
                    switch($rank) {
                        case 1:
                            echo '<i class="fas fa-crown" style="color: #FFD700;"></i>';
                            break;
                        case 2:
                            echo '<i class="fas fa-medal" style="color: #C0C0C0;"></i>';
                            break;
                        case 3:
                            echo '<i class="fas fa-award" style="color: #CD7F32;"></i>';
                            break;
                    }
                } else {
                    echo $rank;
                }
                ?>
            </div>
            <div class="leaderboard-user">
                <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile" class="leaderboard-avatar">
                <div class="user-info">
                    <div class="user-name" style="color: white; font-size: medium;">
                        
                        <?php 
                        if ($isCurrentUser) {
                            echo '<strong style="color: #0369a1;">YOU</strong>';
                        } else {
                            echo htmlspecialchars($user['LASTNAME'] . ', ' . $user['FIRSTNAME'] . $middleInitial);
                        }
                        ?>
                    </div>
                    <div class="user-points" style="color: rgba(255, 255, 255, 0.7);">
                        <?php echo htmlspecialchars($user['IDNO']); ?>
                    </div>
                </div>
            </div>
            <div class="points-badge" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
    <?php 
    $sessionsUsed = 30 - (int)$user['REMAINING_SESSIONS'];
    $points = (int)$user['POINTS'];
    ?>
    <div style="
        background-color: rgba(255, 255, 255, 0.1); 
        color: white; 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-size: 13px;
        font-weight: 500;
        display: inline-block;
    ">
        Sessions Used: <?php echo $sessionsUsed; ?>
    </div>
    <div style="
        background-color: #4a90e2; 
        color: white; 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-size: 13px;
        font-weight: 500;
        display: inline-block;
    ">
        <?php echo $points . ' ' . ($points <= 1 ? 'Point' : 'Points'); ?>
    </div>
</div>



        </div>
        <?php
        $rank++;
    }
}
?>
</div>

            </div>
        </div>
    </div>
</div>

<?php if ($user_not_found): ?>
<script>
alert("User does not exist");
</script>
<?php endif; ?>

<?php if ($search_result): ?>
    <div id="searchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('searchModal')">&times;</span>
        <h3>User Information</h3>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($search_result['IDNO']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($search_result['FIRSTNAME'] . ' ' . $search_result['MIDNAME'] . ' ' . $search_result['LASTNAME']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($search_result['COURSE']); ?></p>
        <p><strong>Year Level:</strong> <?php echo htmlspecialchars($search_result['YEARLEVEL']); ?></p>
        <label for="purpose">Purpose:</label>
        <select name="purpose" id="purpose">
            <option value="Select">Select</option>
            <option value="C Programming">C Programming</option>
            <option value="Java Programming">Java Programming</option>
            <option value="C# Programming">C# Programming</option>
            <option value="System Integration & Architecture">System Integration & Architecture</option>
            <option value="Embedded System & IoT">Embedded System & IoT</option>
            <option value="Digital logic & Design">Digital logic & Design</option>
            <option value="Computer Application">Computer Application</option>
            <option value="Database">Database</option>
            <option value="Project Management">Project Management</option>
            <option value="Python Programming">Python Programming</option>
            <option value="Mobile Application">Mobile Application</option>
            <option value="Others...">Others...</option>
            
        </select>

        <label for="lab_room">Laboratory Room:</label>
        <select name="lab_room" id="lab_room">
            <option value="524">524</option>
            <option value="526">526</option>
            <option value="528">528</option>
            <option value="530">530</option>
            <option value="542">542</option>
            <option value="544">544</option>
        </select>
        <label for="session">Session:</label>
        <input type="text" name="session" id="session" value="<?php echo htmlspecialchars($session_value); ?>" readonly>
        <button type="submit" onclick="submitSitIn()">Sit In</button>
    </div>
</div>
<script>
document.getElementById('searchModal').style.display = 'block';

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.animation = 'fadeOut 0.3s';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = '';
    }, 300);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'block';
}

// Make sure this runs after the page loads
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('searchModal')) {
        openModal('searchModal');
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('searchModal');
    if (event.target == modal) {
        closeModal('searchModal');
    }
}

function toggleActivityFields() {
    const activity = document.getElementById('activity').value;
    const programmingFields = document.getElementById('programmingFields');
    const lectureFields = document.getElementById('lectureFields');
    if (activity === 'programming') {
        programmingFields.style.display = 'block';
        lectureFields.style.display = 'none';
    } else if (activity === 'lecture') {
        programmingFields.style.display = 'none';
        lectureFields.style.display = 'block';
    } else {
        programmingFields.style.display = 'none';
        lectureFields.style.display = 'none';
    }
}

// Add this new function to check active sessions
async function checkActiveSession(studentId) {
    try {
        const response = await fetch(`check_active_session.php?id=${studentId}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        return { error: true, message: 'Error checking session status' };
    }
}

// Update the submitSitIn function
async function submitSitIn() {
    const searchId = "<?php echo htmlspecialchars($search_result['IDNO']); ?>";
    const purpose = document.getElementById('purpose').value;
    const labRoom = document.getElementById('lab_room').value;
    const session = document.getElementById('session').value;

    // Validate fields
    if (!purpose || purpose === 'Select') {
        alert('Please select a purpose');
        return;
    }

    try {
        // Check if student has active session
        const sessionStatus = await checkActiveSession(searchId);
        if (sessionStatus.error) {
            alert(sessionStatus.message);
            return;
        }
        if (sessionStatus.hasActiveSession) {
            alert('This student already has an active sit-in session');
            return;
        }

        // If checks pass, submit the form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'adsitin.php';

        const fields = {
            searchId,
            purpose,
            labRoom,
            session
        };

        for (const key in fields) {
            if (fields.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();

    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
    document.querySelector('.content').classList.toggle('sidebar-active');
}

// Room Distribution Chart
const roomCtx = document.getElementById('roomPieChart').getContext('2d');
const roomPieChart = new Chart(roomCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($rooms); ?>,
        datasets: [{
            label: 'Current Sit-ins',
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    color: 'white',
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return `${tooltipItem.label}: ${tooltipItem.raw} student${tooltipItem.raw !== 1 ? 's' : ''}`;
                    }
                }
            }
        },
        cutout: '60%'
    }
});

// Purpose Distribution Chart
const purposeCtx = document.getElementById('purposePieChart').getContext('2d');
const purposePieChart = new Chart(purposeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($purposes); ?>,
        datasets: [{
            label: 'Current Sit-ins',
            data: <?php echo json_encode($purpose_counts); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)',
                'rgba(255, 99, 255, 0.8)',
                'rgba(99, 255, 132, 0.8)',
                'rgba(255, 159, 132, 0.8)',
                'rgba(132, 99, 255, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(199, 199, 199, 1)',
                'rgba(83, 102, 255, 1)',
                'rgba(255, 99, 255, 1)',
                'rgba(99, 255, 132, 1)',
                'rgba(255, 159, 132, 1)',
                'rgba(132, 99, 255, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    color: 'white',
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return `${tooltipItem.label}: ${tooltipItem.raw} student${tooltipItem.raw !== 1 ? 's' : ''}`;
                    }
                }
            }
        },
        cutout: '60%'
    }
});

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
            } else if (badge) {
                badge.style.display = 'none';
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