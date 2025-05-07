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

// Fetch schedules
$schedules = mysqli_query($con, "SELECT * FROM lab_schedules ORDER BY last_updated DESC");

// Define available rooms
$rooms = ['524', '526', '528', '530', '542', '544'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedule</title>
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
            background: #f8fafc;
        }

        .schedule-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: calc(100vh - 60px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #e2e8f0;
            transform: translateX(-5px);
        }

        .schedule-header h1 {
            color: #14569b;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .header-controls select {
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #2d3748;
            cursor: pointer;
        }

        .schedule-table {
            flex: 1;
            overflow: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .schedule-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            background: #14569b;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .schedule-table td {
            padding: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .time-slot {
            background: #f8fafc;
            font-weight: 500;
            color: #2d3748;
            position: sticky;
            left: 0;
            z-index: 5;
        }

        .status-btn {
            padding: 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
        }

        .status-btn.available {
            background: #dcfce7;
            color: #166534;
        }

        .status-btn.occupied {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-btn.available .status-indicator {
            background: #166534;
        }

        .status-btn.occupied .status-indicator {
            background: #991b1b;
        }

        /* Replace the existing header styles with these */
        .resources-header {
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
        }

        .resources-header h1 {
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

        /* Move the room filter to match resources filter style */
        .header-controls {
            padding: 20px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .header-controls select {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            min-width: 200px;
            color: #4a5568;
        }

        /* Add to your existing styles */
        .filter-section {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: auto; /* This will push it to the right */
        }

        .filter-group label {
            color: #4a5568;
            font-weight: 500;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #2d3748;
            min-width: 200px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-group select:hover {
            border-color: #cbd5e0;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #14569b;
            box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
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
    <div class="schedule-container">
        <div class="resources-header">
            <h1>
                <i class="fas fa-calendar-alt"></i>
                Laboratory Schedule
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="filter-section">
            <div class="filter-group">
                <select id="room_filter" onchange="filterRoom(this.value)">
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room; ?>" <?php echo (isset($_GET['room']) && $_GET['room'] == $room) || (!isset($_GET['room']) && $room == '524') ? 'selected' : ''; ?>>
                            Room <?php echo $room; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="schedule-table">
            <table>
                <thead>
                    <tr>
                        <th>Time Slot</th>
                        <th>Monday/Wednesday</th>
                        <th>Tuesday/Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $time_slots = [
                        '7:30AM-9:00AM',
                        '9:00AM-10:30AM',
                        '10:30AM-12:00PM',
                        '12:00PM-1:00PM',
                        '1:00PM-3:00PM',
                        '3:00PM-4:30PM',
                        '4:30PM-6:00PM',
                        '6:00PM-7:30PM',
                        '7:30PM-9:00PM'
                    ];
                    
                    foreach ($time_slots as $time_slot): ?>
                        <tr>
                            <td class="time-slot"><?php echo $time_slot; ?></td>
                            <?php foreach (['MW', 'TTH', 'F', 'S'] as $day): ?>
                                <td class="status-cell">
                                    <?php
                                    $query = "SELECT status FROM lab_schedules 
                                              WHERE room_number = ? 
                                              AND day_group = ? 
                                              AND time_slot = ?";
                                    $stmt = $con->prepare($query);
                                    $current_room = isset($_GET['room']) ? $_GET['room'] : '524';
                                    $stmt->bind_param("sss", $current_room, $day, $time_slot);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $schedule = $result->fetch_assoc();
                                    $status = $schedule ? $schedule['status'] : 'Available';
                                    $statusClass = strtolower($status);
                                    ?>
                                    <div class="status-btn <?php echo $statusClass; ?>">
                                        <span class="status-indicator"></span>
                                        <?php echo $status; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterRoom(room) {
    window.location.href = 'labschedule.php?room=' + room;
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const room = urlParams.get('room') || '524';
    document.getElementById('room_filter').value = room;
});
</script>
</body>
</html>