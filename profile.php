<?php
session_start();
include("connector.php");

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$username = $_SESSION['Username'];
$query = "SELECT IDNO, LASTNAME, FIRSTNAME, MIDNAME, COURSE, YEARLEVEL, EMAIL, PROFILE_PIC FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    $profile_pic = !empty($user_data['PROFILE_PIC']) ? $user_data['PROFILE_PIC'] : 'default.jpg';
    $user_name = htmlspecialchars($user_data['LASTNAME'] . ' ' . substr($user_data['FIRSTNAME'], 0, 1) . '.');
} else {
    echo "User data not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Profile</title>
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

        /* Content Area */
        .content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        .profile-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: calc(100vh - 60px);
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .profile-header {
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
        }

        .profile-header h1 {
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

        .profile-content {
            padding: 30px;
            background: white;
        }

        .profile-content img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #14569b;
            margin-bottom: 20px;
            object-fit: cover;
        }

        table {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        table td:first-child {
            font-weight: 600;
            color: #2d3748;
            width: 30%;
        }

        button {
            background: #14569b;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #0f4578;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 280px;
                padding: 20px;
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
        <div class="profile-container">
            <div class="profile-header">
                <h1>
                    <i class="fas fa-user"></i>
                    Profile Information
                </h1>
                <a href="dashboard.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <div class="profile-content">
                <center>
                    <img src="uploads/<?php echo htmlspecialchars($user_data['PROFILE_PIC']); ?>" alt="Profile Picture">
                    <h2>Student Information</h2>
                </center>
                
                <table>
                    <tr>
                        <td>ID Number:</td>
                        <td><?php echo htmlspecialchars($user_data['IDNO']); ?></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><?php echo htmlspecialchars($user_data['LASTNAME']); ?></td>
                    </tr>
                    <tr>
                        <td>First Name:</td>
                        <td><?php echo htmlspecialchars($user_data['FIRSTNAME']); ?></td>
                    </tr>
                    <tr>
                        <td>Middle Name:</td>
                        <td><?php echo htmlspecialchars($user_data['MIDNAME']); ?></td>
                    </tr>
                    <tr>
                        <td>Course:</td>
                        <td><?php echo htmlspecialchars($user_data['COURSE']); ?></td>
                    </tr>
                    <tr>
                        <td>Year Level:</td>
                        <td><?php echo htmlspecialchars($user_data['YEARLEVEL']); ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo htmlspecialchars($user_data['EMAIL']); ?></td>
                    </tr>
                </table>
                
                <center>
                    <button type="button" onclick="window.location.href='edit.php'">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </center>
            </div>
        </div>
    </div>
</body>
</html>