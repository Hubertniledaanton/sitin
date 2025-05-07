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

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['MIDNAME'] . ' ' . $row['LASTNAME']);
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'Admin';
}

// Fetch the announcement details
if (isset($_GET['title'])) {
    $title = $_GET['title'];
    $stmt = $con->prepare("SELECT * FROM announcements WHERE TITLE = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $announcement = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Update the announcement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_title = $_POST["title"];
    $new_content = $_POST["content"];
    $stmt = $con->prepare("UPDATE announcements SET TITLE = ?, CONTENT = ? WHERE TITLE = ?");
    $stmt->bind_param("sss", $new_title, $new_content, $title);

    if ($stmt->execute()) {
        echo "<script>alert('Announcement updated successfully!'); window.location.href='adannouncement.php';</script>";
    } else {
        echo "<script>alert('Failed to update announcement!');</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Update Announcement</title>
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
    flex-grow: 1;
    margin-left: 250px;
    padding: 30px;
    min-height: 100vh;
    background: #f0f2f5;
    transition: margin-left 0.3s ease-in-out;
    width: calc(100% - 250px);
}

.container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

h1 {
    color: #14569b;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 25px;
    text-align: left;
}

input[type="text"], 
textarea {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

input[type="text"]:focus,
textarea:focus {
    border-color: #14569b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
}

textarea {
    min-height: 200px;
    resize: vertical;
}

button {
    background: #14569b;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    width: 100%;
    margin-top: 20px;
    font-size: 1rem;
}

button:hover {
    background: #0f4578;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        z-index: 1000;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .content {
        margin-left: 0;
        width: 100%;
        padding: 15px;
    }
    
    .container {
        padding: 20px;
    }
}

/* Burger Menu */
.burger {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    cursor: pointer;
    z-index: 1001;
}

.burger div {
    width: 25px;
    height: 3px;
    background-color: #14569b;
    margin: 5px;
    transition: 0.3s;
}

@media (max-width: 768px) {
    .burger {
        display: block;
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
<center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
<a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
<a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
<a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
<a href="addaily.php"><i class="fas fa-chair"></i> Daily Sitin Records</a>
<a href="viewReports.php"><i class="fas fa-eye"></i> View Sitin Reports</a>
<a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
   <!-- <a href="adlabreward.php"><i class="fas fa-chair"></i> Lab Reward</a>-->
   <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
<a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
<a href="viewReports.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
<a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>
<div class="content">
    <div class="container">
        <h1>Update Announcement</h1>
        <form action="update_announcement.php?title=<?php echo htmlspecialchars($title); ?>" method="POST">
            <input type="text" name="title" value="<?php echo htmlspecialchars($announcement['TITLE']); ?>" required>
            <textarea name="content" rows="5" required><?php echo htmlspecialchars($announcement['CONTENT']); ?></textarea>
            <button type="submit">Update Announcement</button>
        </form>
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