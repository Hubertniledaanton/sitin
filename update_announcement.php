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
    width: 100%;
}

.container {
    background: #1a2942;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

h1 {
    color: white;
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
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    transition: all 0.2s;
}

input[type="text"]::placeholder,
textarea::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

input[type="text"]:focus,
textarea:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

textarea {
    min-height: 200px;
    resize: vertical;
}

button {
    background: #4a90e2;
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
    background: #357abd;
    transform: translateY(-2px);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #0a192f;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #2a3b55;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #4a90e2;
}

/* Responsive Design */
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
    
    .container {
        padding: 20px;
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
    <div class="container">
        <h1>Update Announcement</h1>
        <form action="update_announcement.php?title=<?php echo htmlspecialchars($title); ?>" method="POST" onsubmit="return confirmUpdate()">
            <input type="text" name="title" value="<?php echo htmlspecialchars($announcement['TITLE']); ?>" required maxlength="100">
            <textarea name="content" required maxlength="1000"><?php echo htmlspecialchars($announcement['CONTENT']); ?></textarea>
            <div class="char-counter">
                <span id="title-counter">0/100</span>
                <span id="content-counter">0/1000</span>
            </div>
            <button type="submit">Update Announcement</button>
        </form>
    </div>
</div>

<script>
function confirmUpdate() {
    return confirm('Are you sure you want to update this announcement?');
}

// Character counter
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.querySelector('input[name="title"]');
    const contentInput = document.querySelector('textarea[name="content"]');
    const titleCounter = document.getElementById('title-counter');
    const contentCounter = document.getElementById('content-counter');

    function updateCounter(input, counter) {
        counter.textContent = `${input.value.length}/${input.maxLength}`;
    }

    // Initial count
    updateCounter(titleInput, titleCounter);
    updateCounter(contentInput, contentCounter);

    // Update on input
    titleInput.addEventListener('input', function() {
        updateCounter(this, titleCounter);
    });

    contentInput.addEventListener('input', function() {
        updateCounter(this, contentCounter);
    });
});
</script>
</body>
</html>