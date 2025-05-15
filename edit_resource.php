<?php
session_start();
include("connector.php");

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

// Get the resource ID from the URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    
    // Fetch the resource details
    $query = "SELECT * FROM lab_resources WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $resource = mysqli_fetch_assoc($result);
    } else {
        die("Resource not found.");
    }
    mysqli_stmt_close($stmt);
} else {
    die("No resource selected.");
}

// Handle update
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $resource_type = mysqli_real_escape_string($con, $_POST['resource_type']);
    $link = mysqli_real_escape_string($con, $_POST['link']);

    // Handle file upload if a new file is selected
    $file_path = $resource['file_path']; // Keep existing file if no new one uploaded
    $file_name = $resource['file_name'];
    $file_type = $resource['file_type'];

    if (!empty($_FILES['resource_file']['name'])) {
        $upload_dir = 'uploads/resources/';
        $file_name = $_FILES['resource_file']['name'];
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = uniqid() . '_' . $file_name;
        $upload_path = $upload_dir . $unique_name;

        // Delete old file if exists
        if (!empty($resource['file_path'])) {
            $old_file = $upload_dir . $resource['file_path'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $upload_path)) {
            $file_path = $unique_name;
        } else {
            $error_message = "Error uploading file.";
        }
    }

    // Update the resource
    $query = "UPDATE lab_resources SET 
              title = ?, 
              description = ?, 
              category = ?, 
              resource_type = ?,
              link = ?, 
              file_path = ?,
              file_name = ?,
              file_type = ?
              WHERE id = ?";
              
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", 
        $title, 
        $description, 
        $category, 
        $resource_type,
        $link, 
        $file_path,
        $file_name,
        $file_type,
        $id
    );

    // Replace the existing success redirect
    if (mysqli_stmt_execute($stmt)) {
        header("Location: adlabresources.php?success=1&message=Resource updated successfully&t=" . time());
        exit();
    } else {
        $error_message = "Error updating resource: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resource</title>
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
        min-height: 100vh;
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

    /* Content Area */
    .content {
        margin-top: 80px;
        padding: 30px;
        min-height: calc(100vh - 80px);
        background: #0a192f;
        width: 100%;
    }

    .container {
        background: #1a2942;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: 0 auto;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    h1 {
        color: white;
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 25px;
        text-align: left;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: white;
        font-weight: 500;
    }

    input[type="text"],
    textarea,
    select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        font-size: 0.95rem;
        background: #2a3b55;
        color: white;
        transition: all 0.2s;
    }

    input[type="text"]:focus,
    textarea:focus,
    select:focus {
        border-color: #4a90e2;
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    textarea {
        min-height: 150px;
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
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    button:hover {
        background: #357abd;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        background: #2a3b55;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .success {
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .error {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .file-upload {
        position: relative;
        margin-bottom: 10px;
    }

    .file-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        font-size: 0.95rem;
        background: #2a3b55;
        color: white;
    }

    .current-file {
        margin-top: 8px;
        color: #718096;
        font-size: 0.9rem;
        font-style: italic;
    }

    .popup {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(150%);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    /* Custom Scrollbar */
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
            margin: 0;
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
            <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Records</a>
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
            <h1>Edit Resource</h1>

            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($resource['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="Programming" <?php echo $resource['category'] == 'Programming' ? 'selected' : ''; ?>>Programming</option>
                        <option value="Web Development" <?php echo $resource['category'] == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Database" <?php echo $resource['category'] == 'Database' ? 'selected' : ''; ?>>Database</option>
                        <option value="Networking" <?php echo $resource['category'] == 'Networking' ? 'selected' : ''; ?>>Networking</option>
                        <option value="Other" <?php echo $resource['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="link">Resource Link (Optional)</label>
                    <input type="text" id="link" name="link" value="<?php echo htmlspecialchars($resource['link']); ?>">
                </div>

                <div class="form-group">
                    <label for="resource_file">Resource File (Optional)</label>
                    <div class="file-upload">
                        <input type="file" id="resource_file" name="resource_file" class="file-input">
                        <?php if (!empty($resource['file_name'])): ?>
                            <p class="current-file">Current file: <?php echo htmlspecialchars($resource['file_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <div class="popup" id="successPopup">
        <i class="fas fa-check-circle"></i>
        <span>Resource updated successfully!</span>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('sidebar-active');
        }

        // Add this new code for popup handling
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            const popup = document.getElementById('successPopup');
            popup.classList.add('show');
            
            // Hide popup after 3 seconds
            setTimeout(() => {
                popup.classList.remove('show');
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
