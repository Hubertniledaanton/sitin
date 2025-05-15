<?php
session_start();
include("connector.php");

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check admin login
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Get admin info
$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && mysqli_num_rows($result) > 0) {
    $admin = $result->fetch_assoc();
    $profile_pic = !empty($admin['PROFILE_PIC']) ? htmlspecialchars($admin['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($admin['FIRSTNAME'] . ' ' . $admin['MIDNAME'] . ' ' . $admin['LASTNAME']);
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'Admin';
}

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$filter_date = $_GET['date'] ?? date('Y-m-d'); // Default to today

// Build query based on filters
$query = "SELECT r.*, u.FIRSTNAME, u.MIDNAME, u.LASTNAME 
          FROM reservations r 
          JOIN user u ON r.student_id = u.IDNO 
          WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND r.status = '$status_filter'";
}

if ($filter_date) {
    $query .= " AND DATE(r.date) = '$filter_date'";
}

$query .= " ORDER BY r.time DESC";
$reservations = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            color: #a0aec0;
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
    
        /* Content Area */
        .content {
            margin-top: 80px;
            padding: 30px;
            min-height: calc(100vh - 80px);
            width: 100%;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: calc(100vh - 140px);
            display: flex;
            flex-direction: column;
        }

        .content-header {
            background: #2a3b55;
            color: white;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .content-header h1 {
            font-size: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .content-body {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
        }

        .filter-container {
            background: #2a3b55;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .date-filters {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        input[type="date"] {
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
            background: #1a2942;
            color: #a0aec0;
        }

        .status-filters {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-btn.all { 
            background: #4a5568;
            color: white;
        }
        .filter-btn.pending { 
            background: #ed8936;
            color: white;
        }
        .filter-btn.approved { 
            background: #48bb78;
            color: white;
        }
        .filter-btn.rejected { 
            background: #f56565;
            color: white;
        }
        .filter-btn:hover { 
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .table-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #2a3b55;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logs-table th {
            background: #1a2942;
            color: white;
            padding: 15px;
            font-size: 0.95rem;
            font-weight: 500;
            text-align: left;
            white-space: nowrap;
            position: sticky;
            top: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logs-table td {
            padding: 12px 15px;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0aec0;
        }

        .logs-table tr:hover {
            background: #1a2942;
        }

        /* Status badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge.pending {
            background: rgba(237, 137, 54, 0.2);
            color: #ed8936;
        }

        .status-badge.approved {
            background: rgba(72, 187, 120, 0.2);
            color: #48bb78;
        }

        .status-badge.rejected {
            background: rgba(245, 101, 101, 0.2);
            color: #f56565;
        }

        /* Responsive Design */
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
                padding: 15px;
            }

            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .date-filters, .status-filters {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left">
            <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
        </div>
        <div class="nav-right">
            <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
            <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
            <a href="addaily.php"><i class="fas fa-chair"></i> Daily Records</a>
            <a href="viewReports.php"><i class="fas fa-eye"></i> Sitin Reports</a>
            <a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
            <a href="adlabresources.php"><i class="fas fa-book"></i> Resources</a>
            <a href="adlabsched.php"><i class="fas fa-calendar"></i> Schedule</a>
            <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </nav>

    <div class="content">
        <div class="content-wrapper">
            <div class="content-header">
                <h1><i class="fas fa-history"></i> Reservation Logs</h1>
            </div>
            <div class="content-body">
                <div class="filter-container">
                    <div class="date-filters">
                        <label style="color: white;">Filter by date:</label>
                        <input type="date" id="date-filter" value="<?php echo $filter_date; ?>" onchange="applyFilters()">
                    </div>
                    <div class="status-filters">
                        <button class="filter-btn all <?php echo $status_filter === 'all' ? 'active' : ''; ?>" onclick="filterStatus('all')">
                            <i class="fas fa-list"></i> All
                        </button>
                        <button class="filter-btn pending <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" onclick="filterStatus('pending')">
                            <i class="fas fa-clock"></i> Pending
                        </button>
                        <button class="filter-btn approved <?php echo $status_filter === 'approved' ? 'active' : ''; ?>" onclick="filterStatus('approved')">
                            <i class="fas fa-check"></i> Approved
                        </button>
                        <button class="filter-btn rejected <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>" onclick="filterStatus('rejected')">
                            <i class="fas fa-times"></i> Rejected
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Room</th>
                                <th>Seat</th>
                                <th>Date & Time</th>
                                <th>Purpose</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $reservations->fetch_assoc()): 
                                $fullname = $row['LASTNAME'] . ', ' . $row['FIRSTNAME'] . ' ' . substr($row['MIDNAME'], 0, 1) . '.';
                                $status_class = strtolower($row['status']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($fullname); ?></td>
                                <td>Room <?php echo htmlspecialchars($row['room']); ?></td>
                                <td>PC <?php echo htmlspecialchars($row['seat_number']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['date'] . ' ' . $row['time'])); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="fas fa-<?php 
                                            echo $row['status'] === 'pending' ? 'clock' : 
                                                ($row['status'] === 'approved' ? 'check' : 'times'); 
                                        ?>"></i>
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function applyFilters() {
        const date = document.getElementById('date-filter').value;
        const status = '<?php echo $status_filter; ?>';
        window.location.href = `reservation_logs.php?date=${date}&status=${status}`;
    }

    function filterStatus(status) {
        const date = document.getElementById('date-filter').value;
        window.location.href = `reservation_logs.php?date=${date}&status=${status}`;
    }
    </script>
</body>
</html>