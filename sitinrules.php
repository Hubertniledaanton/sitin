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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in Rules</title>
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
            transform: translateX(0);
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

        .announcement-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: calc(100vh - 60px);
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .announcement-title {
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
        }

        .announcement-title h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .announcement-item {
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
        }

        .announcement-item strong {
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: block;
        }

        .announcement-content {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 10px;
            color: #4a5568;
            line-height: 1.6;
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
        <a href="sitinrules.php"><i class="fas fa-book"></i> Sit-in Rules</a>
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
                <i class="fas fa-book-open"></i>
                Sit-in Rules
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
        <div class="announcement-item">
            <strong>Rule No.1 </strong>
            <div class="announcement-content">
                <b>Use Internet Responsibly:</b> No browsing inappropriate content, downloading large files, or using illegal sites.
            </div>
        </div>
        <div class="announcement-item">
            <strong>Rule No.2</strong>
            <div class="announcement-content">
                <b>Respect Privacy: </b> Do not access or attempt to access another user's files or personal information.
            </div>
        </div>
        <div class="announcement-item">
            <strong>Rule No.3</strong>
            <div class="announcement-content">
                <b>Keep Your Area Clean:</b> Dispose of any waste properly and leave the workstation tidy.
            </div>
        </div>
        <div class="announcement-item">
            <strong>Rule No.4</strong>
            <div class="announcement-content">
                <b>Report Issues:</b> Inform lab staff immediately if you notice any hardware or software issues.
            </div>
        </div>
        <div class="announcement-item">
            <strong>Rule No.5</strong>
            <div class="announcement-content">
                <b>Follow University Policies:</b> Violating lab rules may result in temporary or permanent suspension from the lab.
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