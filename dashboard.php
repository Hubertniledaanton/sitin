<?php
session_start();
include("connector.php");

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch the profile picture from the database
$username = $_SESSION['Username'];
$query = "SELECT FIRSTNAME, MIDNAME, LASTNAME, PROFILE_PIC FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $firstName = htmlspecialchars($row['FIRSTNAME']);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');
} else {
    $firstName = 'User';
    $profile_pic = 'default.jpg';
    $user_name = 'User';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Dashboard</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    display: flex;
    background: #f0f2f5;
    min-height: 100vh;
    position: relative;
}

/* Sidebar Styles */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    height: 100vh;
    padding: 25px;
    position: fixed;
    display: flex;
    flex-direction: column;
    transform: translateX(0); /* Remove the initial transform */
    transition: all 0.3s ease-in-out;
    box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
}

.dashboard-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.dashboard-header h2 {
    color: white;
    font-size: 26px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.nav-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.nav-links a {
    color: white;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-links a i {
    width: 20px;
    text-align: center;
    font-size: 1.1rem;
}

.nav-links a:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
}

.logout-button {
    margin-top: auto;
    background: rgba(220, 53, 69, 0.1) !important;
}

.logout-button:hover {
    background: rgba(220, 53, 69, 0.2) !important;
}

/* Content Area */
.content {
    flex-grow: 1;
    margin-left: 280px; /* Always show content with sidebar margin */
    padding: 40px;
    width: calc(100% - 280px);
    transition: all 0.3s ease-in-out;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: #f0f2f5;
}
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    width: 100%;
    max-width: 1400px;
    padding: 20px;
}
.left-column {
    display: flex;
    flex-direction: column;
    gap: 25px;
}
.right-column {
    display: flex;
    flex-direction: column;
    gap: 25px;
    width: 100%;
    max-width: 500px;   
}

.greeting-card {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    color: white;
}

.greeting-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.profile-pic-link {
    text-decoration: none;
    transition: transform 0.3s ease;
}

.profile-pic-link:hover {
    transform: scale(1.05);
}

.profile-pic {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.greeting-text h1 {
    color: white;
    font-size: 32px;
    margin-bottom: 5px;
    font-weight: 600;
}

.greeting-text .date {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
}
.welcome-container {
    margin: 0;
    width: 100%;
}
.right-column {
    display: flex;
    flex-direction: column;
    gap: 25px;
    width: 100%;
    max-width: 500px;   
}
.leaderboard-section {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 150px);
    display: flex;
    flex-direction: column;
}

.leaderboard-user {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.leaderboard-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}
.user-id {
    color: #718096;
    font-size: 0.85rem;
}

.points-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
}
.welcome-container {
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    text-align: center;
    max-width: aut;
    width: 100%;
    transition: transform 0.3s ease;
}

.welcome-container:hover {
    transform: translateY(-5px);
}

.welcome-container i {
    font-size: 70px;
    color: #14569b;
    margin-bottom: 25px;
    background: #e8f1f8;
    width: 120px;
    height: 120px;
    border-radius: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px auto;
}

.welcome-container h1 {
    font-size: 32px;
    color: #2d3748;
    margin-bottom: 20px;
    font-weight: 600;
}

.welcome-container p {
    color: #718096;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 30px;
}

/* Update the stat-card styles */
.stat-card {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    padding: 25px;
    border-radius: 12px;
    text-align: left;
    border: none;
    transition: transform 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h3 {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-card .value {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
}

/* Remove burger menu styles */
.burger {
    display: none;
}

/* Update responsive styles */
@media (max-width: 1024px) {
    .quick-stats {
        grid-template-columns: 1fr;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 260px;
    }
    
    .content {
        padding: 20px;
        margin-left: 0;
        width: 100%;
    }
    
    .welcome-container {
        padding: 30px;
    }
}

/* Remove backdrop-filter properties since we don't need them anymore */
.sidebar, .burger {
    backdrop-filter: none;
}

/* Add to your existing <style> section */
.notification-icon {
    position: fixed;
    top: 20px;
    right: 30px;
    z-index: 1000;
    cursor: pointer;
}

.notification-bell {
    font-size: 24px;
    color: #14569b;
    position: relative;
    transition: transform 0.2s;
}

.notification-bell:hover {
    transform: scale(1.1);
}

.notification-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: bold;
}

.notification-dropdown {
    display: none;
    position: absolute;
    top: 40px;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
}

.notification-header {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #14569b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-list {
    padding: 0;
    margin: 0;
    list-style: none;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.unread {
    background: #f0f7ff;
}

.notification-content {
    flex: 1;
}

.notification-message {
    color: #2d3748;
    margin-bottom: 4px;
}

.notification-time {
    font-size: 0.8rem;
    color: #718096;
}

.notification-type {
    font-size: 20px;
    color: #14569b;
}

.mark-read-btn {
    background: none;
    border: none;
    color: #14569b;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
}

.mark-read-btn:hover {
    background: rgba(20, 86, 155, 0.1);
}

/* Add these new styles to your existing CSS */
.leaderboard-list {
    flex: 1;
    overflow-y: auto;
    padding-right: 10px;
}

.leaderboard-list::-webkit-scrollbar {
    width: 6px;
}

.leaderboard-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.leaderboard-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

/* Updated Rankings Design */
.leaderboard-item {
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.leaderboard-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.leaderboard-item:nth-child(1) {
    background: linear-gradient(to right, rgba(108, 162, 220, 0.9), rgba(255, 255, 255, 0.7));
    border-left: 4px solid #FFD700;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.2);
}

.leaderboard-item:nth-child(2) {
    background: linear-gradient(to right, rgba(65, 145, 231, 0.7), rgba(255, 255, 255, 0.7));
    border-left: 4px solid #C0C0C0;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.15);
}

.leaderboard-item:nth-child(3) {
    background: linear-gradient(to right, rgba(25, 122, 225, 0.5), rgba(255, 255, 255, 0.7));
    border-left: 4px solid #CD7F32;
    box-shadow: 0 4px 15px rgba(20, 86, 155, 0.1);
}
.leaderboard-title {
    font-size: 1.5rem;
    font-weight: 600;
    color:rgb(255, 255, 255);
    margin-bottom: 15px;
}

.right-column {
    display: flex;
    
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
/* Rank Icons */
.rank {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #64748b;
}

.rank i {
    font-size: 1.5rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

/* Enhanced Points Badge */
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


/* Top 3 Rank Animations */
@keyframes sparkle {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.leaderboard-item:nth-child(-n+3) .rank i {
    animation: sparkle 2s infinite;
}
</style>
</head>
<body>
<div class="sidebar">
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="viewAnnouncement.php"><i class="fas fa-bullhorn"></i> Announcement</a>
        <a href="labRules&Regulations.php"><i class="fas fa-flask"></i> Rules & Regulations</a>
        <a href="sitinrules.php"><i class="fas fa-book"></i> Sit-in Rules</a>
        <a href="history.php"><i class="fas fa-history"></i> History</a>
        <a href="reservation.php"><i class="fas fa-calendar-alt"></i> Reservation</a>
        <a href="labschedule.php"><i class="fas fa-calendar-alt"></i> Lab Schedules</a>
        <a href="viewlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="login.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<div class="content">
    <div class="dashboard-grid">
        <!-- Left Column -->
        <div class="left-column">
            <!-- Greeting Box -->
            <div class="greeting-card">
                <div class="greeting-content">
                    <a href="profile.php" class="profile-pic-link">
                        <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-pic">
                    </a>
                    <div class="greeting-text">
                        <h1>Hi <?php echo htmlspecialchars($firstName); ?>! 👋</h1>
                        <p class="date" id="current-date"></p>
                    </div>
                </div>
            </div>

            <!-- Existing Welcome Container -->
            <div class="welcome-container">
                <i class="fas fa-chalkboard-teacher"></i>
                <h1>Welcome to Sit-In Monitoring</h1>
                <p>Track your laboratory sessions, manage reservations, and stay updated with the latest announcements.</p>
                
                <div class="quick-stats">
                    <div class="stat-card">
                        <h3>Remaining Sessions</h3>
                        <div class="value">
                            <?php 
                                $sessionQuery = "SELECT REMAINING_SESSIONS FROM user WHERE USERNAME = '$username'";
                                $sessionResult = mysqli_query($con, $sessionQuery);
                                $sessions = mysqli_fetch_assoc($sessionResult);
                                echo $sessions['REMAINING_SESSIONS'] ?? '0';
                            ?> Sessions
                        </div>
                    </div>
                    <!-- Update the stat-card for Current Points to be visible only to the current user -->
                    <div class="stat-card">
                        <h3>Current Points</h3>
                        <div class="value">
                            <?php 
                            $pointsQuery = "SELECT POINTS FROM user WHERE USERNAME = '$username'";
                            $pointsResult = mysqli_query($con, $pointsQuery);
                            $points = mysqli_fetch_assoc($pointsResult);
                            $currentPoints = $points['POINTS'] ?? '0';
                            // Only show points to the current user
                            echo $currentPoints . ' ' . ($currentPoints <= 1 ? 'Point' : 'Points');
                            ?>
                        </div>
                    </div>
                </div>
                <!-- ... keep existing welcome container content ... -->
            </div>
        </div>

        <!-- Right Column - Existing Leaderboard -->
        <div class="right-column">
        <div class="leaderboard-section">
        <div class="leaderboard-header">
            <div class="leaderboard-title">
                <i class="fas fa-trophy"></i>
                Top Students
            </div>
        </div>
        <div class="leaderboard-list">
<?php
// Fetch all students by points with consistent ordering (same as admindash)
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
        $isCurrentUser = ($user['USERNAME'] === $username);
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
                    <div class="user-name">
                        <?php 
                        if ($isCurrentUser) {
                            echo '<strong style="color: #0369a1;">YOU</strong>';
                        } else {
                            echo htmlspecialchars($user['LASTNAME'] . ', ' . $user['FIRSTNAME'] . $middleInitial);
                        }
                        ?>
                    </div>
                    <div class="user-points">
                        <?php echo htmlspecialchars($user['IDNO']); ?>
                    </div>
                </div>
            </div>
            <div class="points-badge">
                <?php 
                if ($isCurrentUser) {
                    echo $user['POINTS'] . ' ' . ($user['POINTS'] <= 1 ? 'Point' : 'Points');
                } else {
                    if ($user['POINTS'] >= 100) {
                        echo '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
                    } elseif ($user['POINTS'] >= 50) {
                        echo '<i class="fas fa-star"></i><i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="fas fa-star"></i>';
                    }
                }
                ?>
            </div>
        </div>
        <?php
        $rank++;
    }
}
?>
</div>


<div class="notification-icon">
    <div class="notification-bell" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="notification-count">0</span>
    </div>
    <div class="notification-dropdown">
        <div class="notification-header">
            <span>Notifications</span>
            <button onclick="markAllRead()" class="mark-read-btn">
                <i class="fas fa-check-double"></i>
            </button>
        </div>
        <ul class="notification-list">
            <!-- Notifications will be inserted here -->
        </ul>
    </div>
</div>

<script>
function updateDateTime() {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        timeZone: 'Asia/Manila'
    };
    
    const now = new Date();
    const philippineDate = now.toLocaleDateString('en-US', options);
    document.getElementById('current-date').textContent = philippineDate;
}

// Update immediately and then every second
updateDateTime();
setInterval(updateDateTime, 1000);
</script>

<script>
let notifications = [];
let lastNotificationCount = 0;
const notificationSound = new Audio('notification.mp3');

// Update the playNotificationSound function
function playNotificationSound() {
    notificationSound.currentTime = 0; // Reset sound to start
    notificationSound.play().catch(error => console.log('Error playing sound:', error));
}

async function fetchNotifications() {
    try {
        const response = await fetch('get_notifications.php');
        const data = await response.json();
        
        if (data.error) {
            console.error(data.error);
            return;
        }
        
        // Check if there are new notifications
        if (data.unread_count > lastNotificationCount) {
            playNotificationSound();
        }
        
        lastNotificationCount = data.unread_count;
        notifications = data.notifications;
        updateNotificationUI(data);
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

function getNotificationIcon(type) {
    switch (type) {
        case 'points':
            return 'fa-star';
        case 'session':
            return 'fa-clock';
        case 'reservation':
            return 'fa-calendar-check';
        case 'warning':
            return 'fa-exclamation-triangle';
        default:
            return 'fa-bell';
    }
}

function updateNotificationUI(data) {
    const listElement = document.querySelector('.notification-list');
    listElement.innerHTML = notifications.map(notif => `
        <li class="notification-item ${!notif.is_read ? 'unread' : ''}" 
            onclick="markAsRead(${notif.id})">
            <div class="notification-type">
                <i class="fas ${getNotificationIcon(notif.type)}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-message">
                    ${notif.message}
                </div>
                <div class="notification-time">
                    ${formatTime(notif.created_at)}
                </div>
            </div>
        </li>
    `).join('');
    
    // Update count
    const countElement = document.querySelector('.notification-count');
    countElement.textContent = data.unread_count;
    countElement.style.display = data.unread_count > 0 ? 'block' : 'none';
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // difference in seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    return date.toLocaleDateString();
}

function toggleNotifications() {
    const dropdown = document.querySelector('.notification-dropdown');
    const currentDisplay = dropdown.style.display;
    dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
}

function markAllRead() {
    fetch('mark_all_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Add this function to your script section
async function markAsRead(notificationId) {
    try {
        const response = await fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: notificationId })
        });
        
        if (response.ok) {
            fetchNotifications();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

fetchNotifications();
setInterval(fetchNotifications, 30000);

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.notification-dropdown');
    const bell = document.querySelector('.notification-bell');
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>

</body>
</html>