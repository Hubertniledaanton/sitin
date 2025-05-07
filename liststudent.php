<?php
include("connector.php");

session_start();
// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
header("Location: login.php");
exit();
}

// Fetch user details
$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
$row = $result->fetch_assoc();
$profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
$user_name = htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['MIDNAME'] . ' ' . $row['LASTNAME']);
} else {
$profile_pic = 'default.jpg';
$user_name = 'Admin';
}

$query = "SELECT * FROM user WHERE POINTS > 0 OR REMAINING_SESSIONS > 0 ORDER BY POINTS DESC, LASTNAME ASC";

// Fetch students
$students_query = "SELECT IDNO, FIRSTNAME, MIDNAME, LASTNAME, COURSE, YEARLEVEL, REMAINING_SESSIONS FROM user WHERE IDNO IS NOT NULL";
$students_result = $con->query($students_query);

// Handle reset sessions
if (isset($_POST['reset_sessions'])) {
$reset_query = "UPDATE user SET REMAINING_SESSIONS = 30 WHERE IDNO IS NOT NULL";
if ($con->query($reset_query)) {
echo "<script>alert('All student sessions have been reset to 30!');</script>";
header("Location: liststudent.php");
exit();
} else {
echo "<script>alert('Error resetting sessions!');</script>";
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>List of Students</title>
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
    width: 100%;
    background-color: rgba(42, 63, 95, 0.9);
    padding: 15px 20px;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    z-index: 1000;
}

.sidebar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.sidebar .user-name {
    color: white;
    font-size: 1.1rem;
    margin-right: auto;
}

.sidebar a {
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 6px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.sidebar a i {
    width: 20px;
    text-align: center;
}

.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .logout-button {
    background: rgba(220, 53, 69, 0.1);
}

/* Content Area */
.content {
    margin-top: 70px;
    padding: 30px;
    min-height: calc(100vh - 70px);
    background: #f0f2f5;
    width: 100%;
}

.container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 60px);
    max-width: 1400px;
    margin: 0 auto;
    overflow: hidden;
}

.header {
    margin-bottom: 20px;
}

.header h1 {
    color: #14569b;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.controls-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    max-width: 400px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 10px 15px;
    padding-right: 45px; /* Make room for the button */
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.search-box button {
    position: absolute;
    right: 5px;
    padding: 8px 12px;
    background: #14569b;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-box button:hover {
    background: #0f4578;
    transform: translateY(-1px);
}

.search-box input:focus {
    border-color: #14569b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
}

.reset-button {
    background: #dc3545;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.reset-button:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.table-container {
    height: calc(100% - 180px);
    overflow-y: auto;
    border-radius: 12px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

thead {
    position: sticky;
    top: 0;
    z-index: 2;
}

th {
    background: #14569b;
    color: white;
    padding: 15px;
    font-weight: 500;
    text-align: left;
}

td {
    padding: 12px 15px;
    border-bottom: 1px solid #e2e8f0;
}

tbody tr:hover {
    background: #f8fafc;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.reset-btn, .delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.reset-btn {
    background: #ffc107;
    color: #000;
}

.delete-btn {
    background: #dc3545;
    color: white;
}

.reset-btn:hover, .delete-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Custom Scrollbar */
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

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        flex-wrap: wrap;
        padding: 10px;
    }
    
    .sidebar a {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
    
    .content {
        margin-top: 120px;
        padding: 15px;
    }
    
    .controls-container {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
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
    <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
    <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
    <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
    <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
    <a href="addaily.php"><i class="fas fa-chair"></i> Daily Sitin Records</a>
    <a href="viewReports.php"><i class="fas fa-eye"></i> View Sitin Reports</a>
    <a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
    <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
    <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
    <a href="viewReports.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
    <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>
<div class="content">
<div class="container">
<div class="header">
<h1>List of Students</h1>
</div>
<div class="controls-container">
<div class="search-box">
<input type="text" id="searchInput" placeholder="Search by ID, Name, or Course...">
<button type="button">
<i class="fas fa-search"></i>
</button>
</div>
<form method="POST" style="margin: 0;">
<button type="submit" name="reset_sessions" class="reset-button">
<i class="fas fa-sync-alt"></i> Reset All Sessions
</button>
</form>
</div>
<div class="table-container">
<table>
<thead>
<tr>
<th>ID Number</th>
<th>Last Name</th>
<th>First Name</th>
<th>Middle Name</th>
<th>Course</th>
<th>Year Level</th>
<th>Username</th>
<th>Email</th>
<th>Points</th>
<th>Remaining Sessions</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$query = "SELECT * FROM user ORDER BY LASTNAME ASC";
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // Determine points class based on value
    $points_class = '';
    if ($row['POINTS'] >= 2) {
        $points_class = 'points-high';
    } elseif ($row['POINTS'] == 1) {
        $points_class = 'points-medium';
    } else {
        $points_class = 'points-low';
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['IDNO']) . "</td>";
    echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
    echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
    echo "<td>" . htmlspecialchars($row['MIDNAME']) . "</td>";
    echo "<td>" . htmlspecialchars($row['COURSE']) . "</td>";
    echo "<td class='text-center'>" . htmlspecialchars($row['YEARLEVEL']) . "</td>";
    echo "<td>" . htmlspecialchars($row['USERNAME']) . "</td>";
    echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
    echo "<td class='points-column " . $points_class . "'>" . htmlspecialchars($row['POINTS']) . "</td>";
    echo "<td class='text-center'>" . htmlspecialchars($row['REMAINING_SESSIONS']) . "</td>";
    echo "<td class='action-buttons'>";
    echo "<button onclick='resetSessions(" . $row['IDNO'] . ")' class='reset-btn' title='Reset Sessions'><i class='fas fa-redo'></i></button>";
    echo "<button onclick='deleteStudent(" . $row['IDNO'] . ")' class='delete-btn' title='Delete Student'><i class='fas fa-trash'></i></button>";
    echo "</td>";
    echo "</tr>";
}
?>
</tbody>
</table>
</div>
</div>
</div>
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
let searchValue = this.value.toLowerCase();
document.querySelectorAll('tbody tr').forEach(row => {
let text = Array.from(row.cells).map(cell => cell.textContent.toLowerCase()).join(' ');
row.style.display = text.includes(searchValue) ? '' : 'none';
});
});

function toggleSidebar() {
document.querySelector('.sidebar').classList.toggle('active');
document.querySelector('.content').classList.toggle('sidebar-active');
}

function resetSessions(idno) {
if (confirm('Are you sure you want to reset this student\'s remaining sessions to 30?')) {
window.location.href = 'reset_sessions.php?id=' + idno;
}
}

function deleteStudent(idno) {
if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
window.location.href = 'delete_student.php?id=' + idno;
}
}
</script>
</body>
</html>