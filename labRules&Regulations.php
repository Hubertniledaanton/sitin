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
    background: #0a192f;
    min-height: 100vh;
    position: relative;
}

/* Sidebar Styles */
.sidebar {
    width: 280px;
    background: #1a2942;
    height: 100vh;
    padding: 25px;
    position: fixed;
    display: flex;
    flex-direction: column;
    transform: translateX(0);
    box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.dashboard-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
    background: #2a3b55;
}

.profile-link:hover {
    background: #357abd;
    transform: translateY(-2px);
}

.profile-link img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #4a90e2;
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
    color: #a0aec0;
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
    color: #4a90e2;
}

.nav-links a:hover {
    background: #2a3b55;
    color: white;
    transform: translateX(5px);
}

.logout-button {
    margin-top: auto;
    background: rgba(220, 53, 69, 0.1) !important;
    color: #ff6b6b !important;
}

.logout-button:hover {
    background: rgba(220, 53, 69, 0.2) !important;
}

/* Scrollbar Styles */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #1a2942;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb {
    background: #4a90e2;
    border-radius: 5px;
    transition: background 0.3s ease;
}

::-webkit-scrollbar-thumb:hover {
    background: #357abd;
}

/* Content Area */
.content {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    min-height: 100vh;
    background: #0a192f;
    overflow-x: hidden;
}

.rules-container {
    background: #1a2942;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    height: calc(100vh - 60px);
    width: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.rules-header {
    background: #2a3b55;
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.rules-header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.rules-content {
    padding: 40px;
    color: #a0aec0;
    overflow-y: auto;
    flex-grow: 1;
}

.title-section {
    text-align: center;
    margin-bottom: 40px;
}

.title-section h2 {
    color: white;
    margin-bottom: 10px;
    font-size: 28px;
}

.intro-text {
    margin-bottom: 30px;
    font-size: 16px;
    line-height: 1.6;
}

.rules-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.rule-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #2a3b55;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: transform 0.2s ease;
}

.rule-item:hover {
    transform: translateX(5px);
}

.rule-number {
    color: #4a90e2;
    font-weight: bold;
    font-size: 16px;
    min-width: 25px;
}

.rule-item p {
    margin: 0;
    line-height: 1.6;
}

.disciplinary-section {
    margin-top: 40px;
    padding: 30px;
    background: #1a2942;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.section-title {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.section-title i {
    font-size: 24px;
    color: #4a90e2;
}

.section-title h2 {
    color: white;
    font-size: 24px;
    margin: 0;
}

.disciplinary-grid {
    display: grid;
    gap: 20px;
}

.disciplinary-item {
    background: #2a3b55;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: transform 0.2s ease;
}

.disciplinary-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.offense-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.offense-header i {
    font-size: 20px;
    color: #4a90e2;
}

.offense-header h3 {
    color: white;
    font-size: 18px;
    margin: 0;
}

.offense-content {
    color: #a0aec0;
    line-height: 1.6;
}

.offense-content p {
    margin: 0;
}

.back-button {
    background: #2a3b55;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.back-button:hover {
    background: #357abd;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 280px;
        transform: translateX(0);
    }
    
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
            <div class="title-section">
                <h2>UNIVERSITY OF CEBU</h2>
                <h2>LABORATORY RULES AND REGULATIONS</h2>
            </div>
            
            <div class="intro-text">
                <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
            </div>

            <div class="rules-list">
                <div class="rule-item">
                    <span class="rule-number">1.</span>
                    <p>Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans, and other personal pieces of equipment must be switched off.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">2.</span>
                    <p>Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">3.</span>
                    <p>Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">4.</span>
                    <p>Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">5.</span>
                    <p>Deleting computer files and changing the set-up of the computer is a major offense.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">6.</span>
                    <p>Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">7.</span>
                    <p>Observe proper decorum while inside the laboratory.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">8.</span>
                    <p>Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">9.</span>
                    <p>Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">10.</span>
                    <p>Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">11.</span>
                    <p>For serious offenses, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
                </div>

                <div class="rule-item">
                    <span class="rule-number">12.</span>
                    <p>Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant, or instructor immediately.</p>
                </div>
            </div>

            <div class="disciplinary-section">
                <div class="section-title">
                    <i class="fas fa-gavel"></i>
                    <h2>DISCIPLINARY ACTION</h2>
                </div>

                <div class="disciplinary-grid">
                    <div class="disciplinary-item">
                        <div class="offense-header">
                            <i class="fas fa-exclamation-circle"></i>
                            <h3>First Offense</h3>
                        </div>
                        <div class="offense-content">
                            <p>The Head, Dean, or OIC recommends to the Guidance Center for a suspension from classes for each offender.</p>
                        </div>
                    </div>

                    <div class="disciplinary-item">
                        <div class="offense-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Second and Subsequent Offenses</h3>
                        </div>
                        <div class="offense-content">
                            <p>A recommendation for a heavier sanction will be endorsed to the Guidance Center.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>