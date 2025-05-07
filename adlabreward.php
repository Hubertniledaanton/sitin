<?php
session_start();
include("connector.php");

// Check if admin is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Fetch admin details for sidebar
$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['MIDNAME'] . ' ' . $row['LASTNAME']);
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'Admin';
}

// Create reward_points table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS reward_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50),
    points INT DEFAULT 0,
    last_reward_date DATE,
    FOREIGN KEY (student_id) REFERENCES user(IDNO)
)";
mysqli_query($con, $create_table_query);

// Handle point assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['give_point'])) {
    $student_id = $_POST['student_id'];
    $current_date = date('Y-m-d');

    // Check if student already received a point today
    $check_query = "SELECT * FROM reward_points WHERE student_id = ? AND last_reward_date = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("ss", $student_id, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert or update points
        $update_query = "INSERT INTO reward_points (student_id, points, last_reward_date) 
                        VALUES (?, 1, ?) 
                        ON DUPLICATE KEY UPDATE 
                        points = points + 1,
                        last_reward_date = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("sss", $student_id, $current_date, $current_date);
        $stmt->execute();

        // Check if student has reached 3 points
        $check_points_query = "SELECT points FROM reward_points WHERE student_id = ?";
        $stmt = $con->prepare($check_points_query);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $points_result = $stmt->get_result()->fetch_assoc();

        if ($points_result['points'] >= 3) {
            // Add 1 session and reset points
            $update_session_query = "UPDATE user SET REMAINING_SESSIONS = REMAINING_SESSIONS + 1 WHERE IDNO = ?";
            $stmt = $con->prepare($update_session_query);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();

            // Reset points
            $reset_points_query = "UPDATE reward_points SET points = 0 WHERE student_id = ?";
            $stmt = $con->prepare($reset_points_query);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();

            $success_message = "Student rewarded with an extra session! Points reset to 0.";
        } else {
            $success_message = "Point added successfully!";
        }
    } else {
        $error_message = "Student already received a point today.";
    }
}

// Fetch students who logged out today
$sitin_query = "SELECT 
    lr.IDNO, 
    lr.FULLNAME,
    COALESCE(rp.points, 0) as reward_points,
    rp.last_reward_date,
    lr.TIME_OUT
FROM login_records lr 
LEFT JOIN reward_points rp ON lr.IDNO = rp.student_id
WHERE lr.TIME_OUT IS NOT NULL 
AND DATE(lr.TIME_OUT) = CURDATE()
ORDER BY lr.TIME_OUT DESC";
$sitin_result = mysqli_query($con, $sitin_query);

// Add date display at the top
echo '<div style="text-align: center; margin-bottom: 20px; font-size: 1.2em; color: #14569b;">
        <i class="fas fa-calendar-day"></i> ' . date('F d, Y') . '
      </div>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>Lab Reward System</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Georgia, 'Times New Roman', Times, serif;
}
html, body {
    height: 100%;
    background: linear-gradient(to right, #14569b, #14569b);
    display: flex;
}
.sidebar {
    width: 250px;
    background-color: #2a3f5f;
    height: 100vh;
    padding: 20px;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 5px 0 10px rgba(0, 0, 0, 0.2);
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
}
.sidebar.active {
    transform: translateX(0);
}
.sidebar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid white;
    margin-bottom: 15px;
}
.sidebar a {
    width: 100%;
    color: white;
    text-decoration: none;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    margin: 5px 0;
    transition: 0.3s;
}
.sidebar a:hover {
    background-color: #1b2b45;
}
.burger {
    position: absolute;
    top: 20px;
    left: 20px;
    cursor: pointer;
    z-index: 1000;
}
.burger div {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 5px;
    transition: 0.3s;
}
.content {
    flex-grow: 1;
    margin-left: 270px;
    padding: 40px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
}
.container {
    width: 980px;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    max-height: 80vh;
    overflow: hidden;
}
.table-wrapper {
    max-height: calc(80vh - 150px);
    overflow-y: auto;
    border-radius: 8px;
}
.reward-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    position: relative;
}
.reward-table thead {
    position: sticky;
    top: 0;
    z-index: 1;
}
.reward-table th {
    background: linear-gradient(135deg, #578FCA, #4a7db8);
    color: white;
    padding: 15px;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 2;
}
.reward-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}
.reward-table tr:hover {
    background-color: #f5f8fa;
}
.give-point-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.3s;
}
.give-point-btn:hover {
    background-color: #218838;
}
.give-point-btn:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}
.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    text-align: center;
}
.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    text-align: center;
}
.points-badge {
    background-color: #14569b;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.9em;
}
.table-wrapper::-webkit-scrollbar {
    width: 8px;
}
.table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.table-wrapper::-webkit-scrollbar-thumb {
    background: #14569b;
    border-radius: 4px;
}
.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #0f4276;
}
h1 {
    text-align: center; /* Center the heading */
    color: #0f4276; /* Change the color to blue */
    margin-bottom: 20px; /* Add some space below the heading */
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
    <center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
    <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
    <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
    <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Reports</a>
    <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
    <a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
    <a href="adlabreward.php"><i class="fas fa-chair"></i> Lab Reward</a>
    <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
    <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
    <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
    <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>
<div class="content">
    <div class="container">
        <h1><i class="fas fa-award"></i> Lab Rewards</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="reward-table">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Current Points</th>
                        <th>Last Reward Date</th>
                        <th>Logout Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($sitin_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['IDNO']); ?></td>
                            <td><?php echo htmlspecialchars($row['FULLNAME']); ?></td>
                            <td><span class="points-badge"><?php echo htmlspecialchars($row['reward_points']); ?>/3</span></td>
                            <td><?php echo $row['last_reward_date'] ? htmlspecialchars($row['last_reward_date']) : 'No rewards yet'; ?></td>
                            <td><?php echo date('h:i A', strtotime($row['TIME_OUT'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['IDNO']); ?>">
                                    <button type="submit" name="give_point" class="give-point-btn"
                                        <?php echo ($row['last_reward_date'] == date('Y-m-d')) ? 'disabled' : ''; ?>>
                                        Give Point
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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