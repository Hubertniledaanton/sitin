<?php
include("connector.php");

session_start();
// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Add this function after your session_start() and before the HTML
function getFileIcon($fileType) {
    switch (strtolower($fileType)) {
        case 'pdf':
            return 'fas fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint';
        case 'zip':
        case 'rar':
            return 'fas fa-file-archive';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fas fa-file-image';
        default:
            return 'fas fa-file';
    }
}

$username = $_SESSION['Username'];
$query = "SELECT PROFILE_PIC, FIRSTNAME, MIDNAME, LASTNAME FROM user WHERE USERNAME = '$username'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_pic = !empty($row['PROFILE_PIC']) ? htmlspecialchars($row['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($row['LASTNAME'] . ' ' . substr($row['FIRSTNAME'], 0, 1) . '.');  
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'User';
}


// Fetch resources
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
// Update the query section at the top of your file
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';

// Modify the existing query
$query = "SELECT * FROM lab_resources WHERE 1=1";
if (!empty($category_filter)) {
    $query .= " AND category = '" . mysqli_real_escape_string($con, $category_filter) . "'";
}
if (!empty($search)) {
    $query .= " AND (title LIKE '%" . mysqli_real_escape_string($con, $search) . "%' 
                OR description LIKE '%" . mysqli_real_escape_string($con, $search) . "%')";
}
if (!empty($type_filter)) {
    switch($type_filter) {
        case 'link':
            $query .= " AND link IS NOT NULL AND link != ''";
            break;
        case 'file':
            $query .= " AND file_path IS NOT NULL AND file_path != ''";
            break;
    }
}
$query .= " ORDER BY upload_date DESC";

$result = mysqli_query($con, $query);

// Get unique categories
$categories_query = "SELECT DISTINCT category FROM lab_resources ORDER BY category";
$categories_result = mysqli_query($con, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Resources</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    display: flex;
    background: #0a192f;
    min-height: 100vh;
    position: relative;
}

/* Sidebar Styles */
.sidebar {
    width: 280px;
    background: #1a2942;
    height: 100vh;
    padding: 25px;
    position: fixed;
    display: flex;
    flex-direction: column;
    transform: translateX(0);
    box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.dashboard-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
    background: #2a3b55;
}

.profile-link:hover {
    background: #357abd;
    transform: translateY(-2px);
}

.profile-link img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #4a90e2;
    margin-bottom: 12px;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
    color: #a0aec0;
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
    color: #4a90e2;
}

.nav-links a:hover {
    background: #2a3b55;
    color: white;
    transform: translateX(5px);
}

.logout-button {
    margin-top: auto;
    background: rgba(220, 53, 69, 0.1) !important;
    color: #ff6b6b !important;
}

.logout-button:hover {
    background: rgba(220, 53, 69, 0.2) !important;
}

/* Content Area */
.content {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    min-height: 100vh;
    background: #0a192f;
}

.resources-container {
    background: #1a2942;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    min-height: calc(100vh - 60px);
    width: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.resources-header {
    background: #2a3b55;
    color: white;
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.resources-header h1 {
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.back-button {
    background: #1a2942;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.back-button:hover {
    background: #357abd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Filters section */
.filters {
    background: #1a2942;
    padding: 20px;
    display: flex;
    gap: 20px;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    color: white;
    font-weight: 500;
}

.filter-group select,
.filter-group input[type="text"] {
    padding: 8px 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: #2a3b55;
    color: white;
    min-width: 200px;
}

.filter-group select:hover,
.filter-group input[type="text"]:hover {
    border-color: #4a90e2;
    background: #357abd;
}

.filter-group select:focus,
.filter-group input[type="text"]:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

/* Resources list */
.resources-list {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    overflow-y: auto;
}

.resource-card {
    background: linear-gradient(rgba(26, 41, 66, 0.95), rgba(26, 41, 66, 0.95)), url('assets/card-bg.jpg');
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    height: 320px;
    position: relative;
    backdrop-filter: blur(10px);
}

.resource-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    border-color: #4a90e2;
    background: linear-gradient(rgba(26, 41, 66, 0.85), rgba(26, 41, 66, 0.85)), url('assets/card-bg.jpg');
    background-size: cover;
    background-position: center;
}

.resource-header {
    padding: 20px;
    background: rgba(42, 59, 85, 0.8);
    backdrop-filter: blur(5px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.resource-body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: rgba(26, 41, 66, 0.7);
    backdrop-filter: blur(5px);
}

.resource-title {
    font-size: 1.1rem;
    color: white;
    font-weight: 600;
    margin-bottom: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.resource-description {
    color: #a0aec0;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: auto;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.resource-actions {
    padding: 20px;
    display: flex;
    gap: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(42, 59, 85, 0.8);
    backdrop-filter: blur(5px);
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(74, 144, 226, 0.9);
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    backdrop-filter: blur(5px);
}

.resource-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(5px);
}

.link-btn {
    background: rgba(74, 144, 226, 0.9);
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.link-btn:hover {
    background: rgba(53, 122, 189, 0.9);
    transform: translateY(-2px);
}

.download-btn {
    background: rgba(26, 41, 66, 0.8);
    color: #4a90e2 !important;
    border: 1px solid #4a90e2;
}

.download-btn:hover {
    background: rgba(42, 59, 85, 0.8);
    transform: translateY(-2px);
}

.view-btn {
    background: rgba(74, 144, 226, 0.9);
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.view-btn:hover {
    background: rgba(53, 122, 189, 0.9);
    transform: translateY(-2px);
}

.resource-date {
    color: #a0aec0;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.resource-icon {
    font-size: 24px;
    color: #4a90e2;
    background: rgba(74, 144, 226, 0.1);
    padding: 12px;
    border-radius: 10px;
    backdrop-filter: blur(5px);
}

/* Scrollbar Styling */
.resources-list::-webkit-scrollbar {
    width: 8px;
}

.resources-list::-webkit-scrollbar-track {
    background: #0a192f;
}

.resources-list::-webkit-scrollbar-thumb {
    background: #2a3b55;
    border-radius: 4px;
}

.resources-list::-webkit-scrollbar-thumb:hover {
    background: #4a90e2;
}

/* Update the filters container */
.search-box {
    flex: 1;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 12px 20px;
    padding-left: 45px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    font-size: 0.95rem;
    color: white;
    transition: all 0.3s ease;
    background: #2a3b55;
}

.search-box::before {
    content: '\f002';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #4a90e2;
    font-size: 1.1rem;
}

.search-box input:focus {
    outline: none;
    border-color: #4a90e2;
    background: #357abd;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.search-box input::placeholder {
    color: #a0aec0;
}

/* Update select boxes to match search design */
.category-filter select,
.type-filter select {
    padding: 12px 35px 12px 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    font-size: 0.95rem;
    min-width: 180px;
    color: white;
    background: #2a3b55;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234a90e2'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 20px;
}

.category-filter select:hover,
.type-filter select:hover {
    border-color: #4a90e2;
    background: #357abd;
}

.category-filter select:focus,
.type-filter select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.resources-grid {
    padding: 25px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    overflow-y: auto;
    background: #0a192f;
    flex: 1;
}

.no-resources {
    text-align: center;
    padding: 40px;
    color: #a0aec0;
}

.no-resources i {
    font-size: 3rem;
    color: #2a3b55;
    margin-bottom: 15px;
}

.no-actions {
    padding: 10px;
    text-align: center;
    color: #a0aec0;
    font-size: 0.9rem;
    background: #1a2942;
    border-radius: 8px;
    width: 100%;
}

/* Scrollbar Styling */
.resources-grid::-webkit-scrollbar {
    width: 8px;
}

.resources-grid::-webkit-scrollbar-track {
    background: #0a192f;
}

.resources-grid::-webkit-scrollbar-thumb {
    background: #2a3b55;
    border-radius: 4px;
}

.resources-grid::-webkit-scrollbar-thumb:hover {
    background: #4a90e2;
}
</style>
</head>
<body>

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
    <div class="resources-container">
        <div class="resources-header">
            <h1>
                <i class="fas fa-book"></i>
                Lab Resources
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
        
        <div class="filters">
            <div class="search-box">
                <input type="text" id="search" placeholder="Search resources..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="category-filter">
                <select id="category">
                    <option value="">All Categories</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                <?php echo $category_filter === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="type-filter">
                <select id="type">
                    <option value="">All Types</option>
                    <option value="link" <?php echo $type_filter === 'link' ? 'selected' : ''; ?>>Links</option>
                    <option value="file" <?php echo $type_filter === 'file' ? 'selected' : ''; ?>>Files</option>
                </select>
            </div>
        </div>
        
        <div class="resources-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="category-badge">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['category']); ?>
                            </div>
                            <div class="resource-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('F j, Y', strtotime($row['upload_date'])); ?>
                            </div>
                        </div>
                        
                        <div class="resource-body">
                            <h3 class="resource-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <p class="resource-description">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p>
                            
                            <?php if (!empty($row['file_name'])): ?>
                                <div class="file-info">
                                    <i class="<?php echo getFileIcon($row['file_type']); ?>"></i>
                                    <?php echo htmlspecialchars($row['file_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="resource-actions">
                            <?php 
                            $hasLink = isset($row['link']) && !empty(trim($row['link']));
                            $hasFile = isset($row['file_path']) && !empty(trim($row['file_path']));
                            ?>

                            <?php if ($hasLink): ?>
                                <a href="<?php echo htmlspecialchars($row['link']); ?>" 
                                   target="_blank" 
                                   class="resource-btn link-btn">
                                    <i class="fas fa-external-link-alt"></i> Visit Link
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($hasFile): ?>
                                <a href="uploads/resources/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                   download="<?php echo htmlspecialchars($row['file_name']); ?>"
                                   class="resource-btn download-btn">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            <?php endif; ?>

                            <?php if (!$hasLink && !$hasFile): ?>
                                <div class="resource-btn view-btn">
                                    <i class="fas fa-eye"></i> View Resource
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-resources">
                    <i class="fas fa-search"></i>
                    <p>No resources found. Please try a different search or category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <script>
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const typeSelect = document.getElementById('type');

    function updateResults() {
        const search = searchInput.value;
        const category = categorySelect.value;
        const type = typeSelect.value;
        window.location.href = `viewlabresources.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&type=${encodeURIComponent(type)}`;
    }

    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            updateResults();
        }
    });

    categorySelect.addEventListener('change', updateResults);
    typeSelect.addEventListener('change', updateResults);
</script>

</body>
</html>