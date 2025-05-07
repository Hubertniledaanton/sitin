<?php
session_start();
include("connector.php");

// Check if user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user info
$username = $_SESSION['Username'];
$query = "SELECT IDNO, LASTNAME, FIRSTNAME, MIDNAME, COURSE, YEARLEVEL, EMAIL, PROFILE_PIC FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo "User data not found.";
    exit();
}

// Fetch the profile picture from the database
$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? $row['PROFILE_PIC'] : 'default.jpg';
} else {
    // Default profile picture if not found
    $profile_pic = 'default.jpg';
}

// Update User Info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lastname = $_POST['LASTNAME'];
    $firstname = $_POST['FIRSTNAME'];
    $midname = $_POST['MIDNAME'];
    $course = $_POST['COURSE'];
    $yearlevel = $_POST['YEARLEVEL'];
    $email = $_POST['EMAIL'];

    // Handle file upload
    if (isset($_FILES['PROFILE_PIC']) && $_FILES['PROFILE_PIC']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["PROFILE_PIC"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["PROFILE_PIC"]["tmp_name"]);
        if ($check !== false) {
            // Check file size (limit to 5MB)
            if ($_FILES["PROFILE_PIC"]["size"] <= 5000000) {
                // Allow certain file formats
                if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($_FILES["PROFILE_PIC"]["tmp_name"], $target_file)) {
                        $profile_pic = basename($_FILES["PROFILE_PIC"]["name"]);
                    } else {
                        echo "Sorry, there was an error uploading your file.";
                    }
                } else {
                    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                echo "Sorry, your file is too large.";
            }
        } else {
            echo "File is not an image.";
        }
    } else {
        $profile_pic = $user_data['PROFILE_PIC'];
    }

    $update_query = "UPDATE user SET
    LASTNAME='$lastname', FIRSTNAME='$firstname',
    MIDNAME='$midname', COURSE='$course', YEARLEVEL='$yearlevel', EMAIL='$email', PROFILE_PIC='$profile_pic'
    WHERE USERNAME='$username'";

    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Profile Updated Successfully'); window.location.href='profile.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

$query = "SELECT FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');  
} else {
    $user_name = 'User';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<title>Edit Student Profile</title>
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
    width: 100%;
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

.edit-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    height: calc(100vh - 60px);
    width: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.edit-header {
    background: linear-gradient(135deg, #14569b, #2a3f5f);
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.edit-header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.edit-content {
    padding: 30px;
    overflow-y: auto;
}

.profile-image-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 30px;
    cursor: pointer;
}

.profile-image-container img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #14569b;
}

.profile-image-container:hover::after {
    content: 'Change Photo';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(20, 86, 155, 0.8);
    color: white;
    padding: 8px;
    border-radius: 0 0 75px 75px;
    text-align: center;
    font-size: 14px;
}

.edit-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2d3748;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #14569b;
    box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
    outline: none;
}

.readonly-input {
    background: #f8fafc !important;
    cursor: not-allowed;
}

.submit-btn {
    background: #14569b;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    width: 100%;
    margin-top: 30px;
}

.submit-btn:hover {
    background: #0f4578;
    transform: translateY(-2px);
}

#profile_pic_input {
    display: none;
}

/* Add these styles to your existing CSS */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.profile-section {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e2e8f0;
}

/* Replace the existing back-button styles in your CSS */
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
    <div class="edit-container">
        <div class="edit-header">
            <h1>
                <i class="fas fa-user-edit"></i>
                Edit Profile
            </h1>
            <a href="profile.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Profile
            </a>
        </div>
        
        <div class="edit-content">
            <form class="edit-form" action="" method="POST" enctype="multipart/form-data">
                <div class="profile-section">
                    <div class="profile-image-container" onclick="document.getElementById('profile_pic_input').click()">
                        <img id="profile_preview" src="uploads/<?php echo htmlspecialchars($user_data['PROFILE_PIC']); ?>" alt="Profile Picture">
                        <input type="file" id="profile_pic_input" name="PROFILE_PIC" accept="image/*" style="display: none;">
                    </div>
                </div>

                <!-- ID Number - Full Width -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>ID Number</label>
                        <input type="text" name="IDNO" value="<?php echo htmlspecialchars($user_data['IDNO']); ?>" readonly class="readonly-input">
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="LASTNAME" value="<?php echo htmlspecialchars($user_data['LASTNAME']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="FIRSTNAME" value="<?php echo htmlspecialchars($user_data['FIRSTNAME']); ?>" required>
                    </div>
                </div>

                <!-- Middle Name and Course -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="MIDNAME" value="<?php echo htmlspecialchars($user_data['MIDNAME']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="COURSE" required>
                            <option value="BSCS" <?php if($user_data['COURSE'] == 'BSCS') echo 'selected'; ?>>BSCS</option>
                            <option value="BSIT" <?php if($user_data['COURSE'] == 'BSIT') echo 'selected'; ?>>BSIT</option>
                            <option value="BSHM" <?php if($user_data['COURSE'] == 'BSHM') echo 'selected'; ?>>BSHM</option>
                            <option value="BEED" <?php if($user_data['COURSE'] == 'BEED') echo 'selected'; ?>>BEED</option>
                            <option value="BSCRIM" <?php if($user_data['COURSE'] == 'BSCRIM') echo 'selected'; ?>>BSCRIM</option>
                            <option value="BSED" <?php if($user_data['COURSE'] == 'BSED') echo 'selected'; ?>>BSED</option>
                            <option value="BSBA" <?php if($user_data['COURSE'] == 'BSBA') echo 'selected'; ?>>BSBA</option>
                        </select>
                    </div>
                </div>

                <!-- Year Level and Email -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="YEARLEVEL" required>
                            <option value="1" <?php if($user_data['YEARLEVEL'] == '1') echo 'selected'; ?>>1st Year</option>
                            <option value="2" <?php if($user_data['YEARLEVEL'] == '2') echo 'selected'; ?>>2nd Year</option>
                            <option value="3" <?php if($user_data['YEARLEVEL'] == '3') echo 'selected'; ?>>3rd Year</option>
                            <option value="4" <?php if($user_data['YEARLEVEL'] == '4') echo 'selected'; ?>>4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="EMAIL" value="<?php echo htmlspecialchars($user_data['EMAIL']); ?>" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('profile_pic_input').onchange = function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile_preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>