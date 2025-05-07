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

// Handle search
$search_result = null;
$user_not_found = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_id = $_POST['search_id'];
    $search_query = "SELECT * FROM user WHERE IDNO = ?";
    $stmt = $con->prepare($search_query);
    $stmt->bind_param("s", $search_id);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$search_result) {
        $user_not_found = true;
    }
}

// Prepare and execute the SQL statement to insert announcement
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['search'])) {
    $title = $_POST["title"];
    $content = $_POST["content"];

    // Prepare the SQL statement
    $stmt = $con->prepare("INSERT INTO announcements (TITLE, CONTENT) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        echo "<script>alert('Announcement posted successfully!');</script>";
    } else {
        echo "<script>alert('Failed to post announcement!');</script>";
    }

    $stmt->close();
}

// Handle delete announcement
if (isset($_GET['delete'])) {
    $title_to_delete = $_GET['delete'];
    $stmt = $con->prepare("DELETE FROM announcements WHERE TITLE = ?");
    $stmt->bind_param("s", $title_to_delete);

    if ($stmt->execute()) {
        echo "<script>alert('Announcement deleted successfully!');</script>";
    } else {
        echo "<script>alert('Failed to delete announcement!');</script>";
    }

    $stmt->close();
}

// Fetch announcements
$announcements_query = "SELECT * FROM announcements ORDER BY CREATED_AT DESC";
$announcements_result = $con->query($announcements_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Admin Announcements</title>
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
    flex-direction: column;
    width: 100%;
}

/* Top Navigation Bar Styles */
.top-nav {
    background-color: rgba(42, 63, 95, 0.9);
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
    color: white;
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
}

.nav-right .logout-button {
    background: rgba(220, 53, 69, 0.1);
    margin-left: 10px;
}

.nav-right .logout-button:hover {
    background: rgba(220, 53, 69, 0.2);
}

.content {
    margin-top: 80px;
    padding: 30px;
    min-height: calc(100vh - 80px);
    background: #f0f2f5;
}

/* Remove old sidebar styles */
.sidebar {
    display: none;
}

/* Rest of your existing styles for announcements, forms, etc. */
form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

input[type="text"], textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1em;
    background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s;
}

input[type="text"]:focus, textarea:focus {
    border-color: #14569b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
}

textarea {
    min-height: 150px;
    resize: vertical;
}

button[type="submit"] {
    background: #14569b;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

button[type="submit"]:hover {
    background: #0f4578;
    transform: translateY(-1px);
}

.announcement {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: transform 0.2s;
}

.announcement:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.announcement h3 {
    color: #14569b;
    margin-bottom: 12px;
    font-size: 1.2em;
    font-weight: 600;
}

.announcement p {
    color: #4a5568;
    margin-bottom: 15px;
    line-height: 1.6;
}

.announcement .timestamp {
    color: #718096;
    font-size: 0.9em;
    margin-bottom: 15px;
}

.actions {
    display: flex;
    gap: 10px;
}

.update, .delete {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.update {
    background: #14569b;
    color: white;
}

.delete {
    background: #dc3545;
    color: white;
}

.update:hover, .delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.div2 {
    max-height: 800px;
    overflow-y: auto;
}

/* Custom scrollbar */
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
@media (max-width: 1200px) {
    .parent {
        grid-template-columns: 1fr;
        padding: 15px;
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
    }
}
</style>
</head>
<body>
<div class="top-nav">
    <div class="nav-left">
        <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.src='assets/default.png';">
        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
    </div>
    <div class="nav-right">
        <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
        <a href="addaily.php"><i class="fas fa-chair"></i> Daily Records</a>
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="adreservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<div class="content">
    <div class="parent">
        <div class="div1">
            <h1>Post Announcement</h1>
            <form action="adannouncement.php" method="POST">
                <input type="text" name="title" placeholder="Announcement Title" required>
                <textarea name="content" placeholder="Announcement Content" required></textarea>
                <button type="submit">Post Announcement</button>
            </form>
        </div>
        <div class="div2">
            <h2>Announcements</h2>
            <?php while ($announcement = $announcements_result->fetch_assoc()): ?>
                <div class="announcement">
                    <h3><?php echo htmlspecialchars($announcement['TITLE']); ?></h3>
                    <p><?php echo htmlspecialchars($announcement['CONTENT']); ?></p>
                    <span class="timestamp"><?php echo htmlspecialchars($announcement['CREATED_AT']); ?></span>
                    <div class="actions">
                        <button class="update" onclick="updateAnnouncement('<?php echo htmlspecialchars($announcement['TITLE']); ?>')">
                            <i class="fas fa-edit"></i> Update
                        </button>
                        <button class="delete" onclick="deleteAnnouncement('<?php echo htmlspecialchars($announcement['TITLE']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
function updateAnnouncement(title) {
    window.location.href = `update_announcement.php?title=${title}`;
}

function deleteAnnouncement(title) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        window.location.href = `adannouncement.php?delete=${title}`;
    }
}
</script>
</body>
</html>