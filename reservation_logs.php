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
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Styles */
        .sidebar {
    width: 250px;
    background-color: rgba(42, 63, 95, 0.9);
    height: 100vh;
    padding: 20px;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 5px 0 10px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    transform: translateX(0);
}

.sidebar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 15px;
}

.sidebar a {
    width: 100%;
    color: white;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 8px;
    margin: 5px 0;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar a i {
    width: 20px;
    text-align: center;
}

.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.sidebar .logout-button {
    margin-top: auto;
    background: rgba(220, 53, 69, 0.1);
}
    
        /* Content Area */
        .content {
            margin-left: 250px;
            padding: 20px;
            background: #f0f2f5;
            min-height: 100vh;
            width: calc(100% - 250px);
        }

        .content-wrapper {
        max-width: 1300px;
        margin: 0 auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
    }
        .content-header {
            background: linear-gradient(135deg, #14569b, #2a3f5f);
            color: white;
            padding: 15px 20px;
        }


        .content-body {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 20px;
    }
        .table-container {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        position: relative;
    }

        .filter-container {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .date-filters {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .status-filters {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.all { background: #64748b; color: white; }
        .filter-btn.pending { background: #f59e0b; color: white; }
        .filter-btn.approved { background: #22c55e; color: white; }
        .filter-btn.rejected { background: #ef4444; color: white; }
        .filter-btn:hover { transform: translateY(-2px); }

        .table-container {
            overflow-x: auto;
        }

        .logs-table {
            width: 100%;
            margin-top: 10px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logs-table th {
            background: #14569b;
            color: white;
            padding: 12px 15px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: left;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }

        .logs-table td {
            padding: 10px 15px;
            font-size: 0.9rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            
        }

        .logs-table td:nth-child(7) {
            max-width: 200px;
            white-space: normal;
        }

        .logs-table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        /* Scrollbar */
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

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .content-wrapper {
                margin: 0;
                border-radius: 0;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .status-filters {
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
        <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
        <center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
        <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Reports</a>
        <a href="adreservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
      <!--  <a href="adlabreward.php"><i class="fas fa-chair"></i> Lab Reward</a>-->
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="content">
        <div class="content-wrapper">
            <div class="content-header">
                <h1><i class="fas fa-history"></i> Reservation Logs</h1>
            </div>

            <div class="content-body">
                <div class="filter-container">
                    <div class="date-filters">
                        <input type="date" id="filter_date" value="<?php echo $filter_date; ?>">
                    </div>
                    <div class="status-filters">
                        <button class="filter-btn all <?php echo $status_filter === 'all' ? 'active' : ''; ?>" 
                                onclick="filterStatus('all')">All</button>
                        <button class="filter-btn pending <?php echo $status_filter === 'pending' ? 'active' : ''; ?>"
                                onclick="filterStatus('pending')">Pending</button>
                        <button class="filter-btn approved <?php echo $status_filter === 'approved' ? 'active' : ''; ?>"
                                onclick="filterStatus('approved')">Approved</button>
                        <button class="filter-btn rejected <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>"
                                onclick="filterStatus('rejected')">Rejected</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Rm</th>
                                <th>PC</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Purpose</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $reservations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LASTNAME'] . ', ' . substr($row['FIRSTNAME'], 0, 1) . '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['room']); ?></td>
                                    <td><?php echo htmlspecialchars($row['seat_number']); ?></td>
                                    <td><?php echo date('m/d/y', strtotime($row['date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time'])); ?></td>
                                    <td title="<?php echo htmlspecialchars($row['purpose']); ?>"><?php echo strlen($row['purpose']) > 20 ? substr(htmlspecialchars($row['purpose']), 0, 20) . '...' : htmlspecialchars($row['purpose']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                            <?php 
                                                $status = htmlspecialchars($row['status']);
                                                echo !empty($status) ? ucfirst($status) : 'Unknown'; 
                                            ?>
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
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for date filter
    document.getElementById('filter_date').addEventListener('change', function() {
        const filterDate = this.value;
        const status = document.querySelector('.filter-btn.active').textContent.toLowerCase();
        window.location.href = `?status=${status}&date=${filterDate}`;
    });

    // Add listeners to status buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filterDate = document.getElementById('filter_date').value;
            const status = this.textContent.toLowerCase();
            window.location.href = `?status=${status}&date=${filterDate}`;
        });
    });
});

    function autoApplyFilters() {
        const filterDate = document.getElementById('filter_date').value;
        const status = document.querySelector('.filter-btn.active').textContent.toLowerCase();

        // Add loading indicator
        document.querySelector('.logs-table').style.opacity = '0.5';

        // Construct URL with filters
        const url = new URL(window.location.href);
        url.searchParams.set('status', status);
        if (filterDate) url.searchParams.set('date', filterDate);

        // Update URL and reload page
        window.location.href = url.toString();
    }

    // Pre-select date if it exists in URL
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('date')) {
            document.getElementById('filter_date').value = urlParams.get('date');
        }
    }
    </script>
</body>
</html>