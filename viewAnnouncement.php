<?php
session_start();
include("connector.php");

// Check if user is logged in
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

// Fetch announcements
$announcements = $con->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Announcements</title>
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

        /* Content Area */
        .content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
            min-height: 100vh;
            background: #0a192f;
        }

        .announcement-container {
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            height: calc(100vh - 60px);
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .announcement-title {
            background: #2a3b55;
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .announcement-title h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .announcements-list {
            padding: 25px;
            overflow-y: auto;
            flex-grow: 1;
            background: #0a192f;
        }

        .announcement {
            background: #1a2942;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0aec0;
        }

        .announcement:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .announcement h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .announcement p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .announcement .meta {
            color: #4a90e2;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .announcement .meta i {
            color: #4a90e2;
        }

        /* Scrollbar Styling */
        .announcements-list::-webkit-scrollbar {
            width: 8px;
        }

        .announcements-list::-webkit-scrollbar-track {
            background: #0a192f;
        }

        .announcements-list::-webkit-scrollbar-thumb {
            background: #2a3b55;
            border-radius: 4px;
        }

        .announcements-list::-webkit-scrollbar-thumb:hover {
            background: #4a90e2;
        }

        /* Remove these styles */
        .burger,
        .sidebar.active,
        .content.sidebar-active {
            display: none;
        }

        /* Update responsive design */
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    <div class="announcement-container">
        <div class="announcement-title">
            <h1>
                <i class="fas fa-bullhorn"></i>
                Announcements
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
        <div class="announcements-list">
            <?php while ($announcement = $announcements->fetch_assoc()) { ?>
                <div class="announcement">
                    <h2><?php echo htmlspecialchars($announcement['TITLE']); ?><i class="fas fa-bullhorn"></i></h2>
                    <p><?php echo nl2br(htmlspecialchars($announcement['CONTENT'])); ?></p>
                    <small><i class="fas fa-clock"></i><?php echo htmlspecialchars($announcement['CREATED_AT']); ?></small>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>