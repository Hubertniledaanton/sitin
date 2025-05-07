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
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
$row = mysqli_fetch_assoc($result);
$profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
$user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');
} else {
$profile_pic = 'default.jpg';
$user_name = 'User';
}

// Fetch sit-in history with feedback status (one feedback per sit-in session)
$history_query = "SELECT DISTINCT lr.IDNO, lr.FULLNAME, lr.PURPOSE, lr.LAB_ROOM,
lr.TIME_IN, lr.TIME_OUT,
CASE WHEN f.FEEDBACK IS NOT NULL THEN 'Submitted' ELSE 'Not Submitted' END as FEEDBACK_STATUS
FROM login_records lr
LEFT JOIN feedback f ON lr.IDNO = f.USER_ID AND DATE(lr.TIME_IN) = DATE(f.CREATED_AT)
WHERE lr.IDNO = (SELECT IDNO FROM user WHERE USERNAME = '$username')
GROUP BY lr.IDNO, lr.TIME_IN, lr.TIME_OUT, lr.LAB_ROOM, lr.PURPOSE
ORDER BY lr.TIME_IN DESC";
$history_result = mysqli_query($con, $history_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>History</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

.profile-link {
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
}

.profile-link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.profile-link img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.3);
    margin-bottom: 12px;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.profile-link .user-name {
    color: white;
    font-size: 18px;
    font-weight: 500;
    text-align: center;
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
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    min-height: 100vh;
}

.history-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 60px);
    width: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.history-title {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.history-title h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.back-button {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.back-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

th {
    background: #f8fafc;
    color: #2d3748;
    font-weight: 600;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    color: #4a5568;
}

.feedback-button {
    background: #14569b;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    font-size: 0.9rem;
}

.feedback-button:hover {
    background: #0f4578;
    transform: translateY(-1px);
}

.feedback-disabled {
    color: #a0aec0;
    font-size: 0.9rem;
}

tbody tr:hover {
    background: #f8fafc;
}

@media (max-width: 768px) {
    .content {
        margin-left: 280px;
        padding: 20px;
        width: calc(100% - 280px);
    }
}
</style>
</head>
<body>
<div class="sidebar">
    <a href="profile.php" class="profile-link">
        <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture">
        <div class="user-name"><?php echo $user_name; ?></div>
    </a>
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
<div class="history-container">
<div class="history-title">
    <h1>
        <i class="fas fa-history"></i>
        Sit-in History
    </h1>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
    </a>
</div>
<table>
<thead>
<tr>
<th>Name</th>
<th>Purpose</th>
<th>Room</th>
<th>Date Sit-in</th>
<th>Date Logout</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
if (mysqli_num_rows($history_result) > 0) {
while ($history_row = mysqli_fetch_assoc($history_result)) { ?>
<tr>
<td><?php echo htmlspecialchars($history_row['FULLNAME']); ?></td>
<td><?php echo htmlspecialchars($history_row['PURPOSE']); ?></td>
<td><?php echo htmlspecialchars($history_row['LAB_ROOM']); ?></td>
<td><?php echo htmlspecialchars($history_row['TIME_IN']); ?></td>
<td><?php echo htmlspecialchars($history_row['TIME_OUT']); ?></td>
<td>
<?php if ($history_row['FEEDBACK_STATUS'] === 'Not Submitted') { ?>
<a href="feedback.php?id=<?php echo htmlspecialchars($history_row['IDNO']); ?>&time_in=<?php echo urlencode($history_row['TIME_IN']); ?>&lab_room=<?php echo urlencode($history_row['LAB_ROOM']); ?>" class="feedback-button">Feedback</a>
<?php } else { ?>
<span class="feedback-disabled">Already Submitted</span>
<?php } ?>
</td>
</tr>
<?php }
} else { ?>
<tr>
<td colspan="7" style="text-align: center;">No History Yet!</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</body>
</html>