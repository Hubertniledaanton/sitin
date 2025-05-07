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
$query = "SELECT PROFILE_PIC FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? $row['PROFILE_PIC'] : 'default.jpg';
} else {
    // Default profile picture if not found
    $profile_pic = 'default.jpg';
}

// Fetch the user's name from the database
$query = "SELECT FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');  
} else {
    $user_name = 'User';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>Lab Rules & Regulations</title>
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

.rules-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 60px);
    width: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.rules-header {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.rules-header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rules-content {
    padding: 30px;
    overflow-y: auto;
    background: white;
}

.rules-content h2 {
    color: #14569b;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.rules-content p {
    margin-bottom: 15px;
    color: #4a5568;
    line-height: 1.8;
}

.rules-content p strong {
    color: #2d3748;
    display: inline-block;
    margin-right: 10px;
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

/* Scrollbar Styling */
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
    <div class="rules-container">
        
        <div class="rules-header">
            <h1>
                <i class="fas fa-flask"></i>
                Laboratory Rules and Regulations
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
        <div class="rules-content">
            <center><h2>UNIVERSITY OF CEBU</h2></center>
            <center><h2>LABORATORY RULES AND REGULATIONS</h2></center>
            <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
            <p><strong>1.</strong> Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans, and other personal pieces of equipment must be switched off.</p>
            <p><strong>2.</strong> Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</p>
            <p><strong>3.</strong> Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
            <p><strong>4.</strong> Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
            <p><strong>5.</strong> Deleting computer files and changing the set-up of the computer is a major offense.</p>
            <p><strong>6.</strong> Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
            <p><strong>7.</strong> Observe proper decorum while inside the laboratory.</p>
            <p><strong>8.</strong> Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p>
            <p><strong>9.</strong> Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
            <p><strong>10.</strong> Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
            <p><strong>11.</strong> For serious offenses, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
            <p><strong>12.</strong> Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant, or instructor immediately.</p>
            <h2>DISCIPLINARY ACTION</h2>
            <p style="display: flex; align-items: center;">
                <strong style="width: 150px; min-width: 130px;">First Offense:</strong> 
                <span>The Head, Dean, or OIC recommends to the Guidance Center for a suspension from classes for each offender.</span>
            </p>
            <p style="display: flex; align-items: center;">
                <strong style="width: 235px; min-width: 315px;">Second and Subsequent Offenses:</strong> 
                <span>A recommendation for a heavier sanction will be endorsed to the Guidance Center.</span>
            </p>
        </div>
    </div>
</div>
</body>
</html>