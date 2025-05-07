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

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #14569b;
        font-weight: 500;
    }

    input[type="text"],
    textarea,
    select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: #f8fafc;
    }

    input[type="text"]:focus,
    textarea:focus,
    select:focus {
        border-color: #14569b;
        outline: none;
        box-shadow: 0 0 0 3px rgba(20, 86, 155, 0.1);
    }

    textarea {
        min-height: 150px;
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
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    button:hover {
        background: #0f4578;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
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
            margin: 0;
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

    /* Add these styles to your existing <style> tag */
    .file-upload {
        position: relative;
        margin-bottom: 10px;
    }

    .file-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        background: #f8fafc;
    }

    .current-file {
        margin-top: 8px;
        color: #64748b;
        font-size: 0.9rem;
        font-style: italic;
    }

    .popup {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        background: #4caf50;
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(150%);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
    }

    .popup.show {
        transform: translateX(0);
    }

    .popup i {
        font-size: 1.2rem;
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
        <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.src='assets/default.png';">
        <center><div class="user-name" style="font-size: x-large; color: white;"><?php echo htmlspecialchars($user_name); ?></div></center>
        <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="adannouncement.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="adsitin.php"><i class="fas fa-chair"></i> Current Sitin</a>
        <a href="addaily.php"><i class="fas fa-calendar-day"></i> Daily Reports</a>
        <a href="adviewsitin.php"><i class="fas fa-eye"></i> Generate Reports</a>
        <a href="adreservation.php"><i class="fas fa-chair"></i> Reservation</a>
        <a href="adlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="adlabsched.php"><i class="fas fa-calendar"></i> Lab Schedule</a>
        <a href="adfeedback.php"><i class="fas fa-book-open"></i> Feedback Reports</a>
        <a href="admindash.php?logout=true" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
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
