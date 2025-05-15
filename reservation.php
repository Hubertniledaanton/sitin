<?php
session_start();
include("connector.php");

// Check if the user is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$username = $_SESSION['Username'];
$user_query = "SELECT IDNO, FIRSTNAME, MIDNAME, LASTNAME, REMAINING_SESSIONS, PROFILE_PIC 
               FROM user 
               WHERE USERNAME = ?";
$stmt = $con->prepare($user_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $profile_pic = !empty($user_data['PROFILE_PIC']) ? htmlspecialchars($user_data['PROFILE_PIC']) : 'default.jpg';
    $user_name = htmlspecialchars($user_data['LASTNAME'] . ' ' . substr($user_data['FIRSTNAME'], 0, 1) . '.');
} else {
    $profile_pic = 'default.jpg';
    $user_name = 'User';
    // Redirect if user data not found
    header("Location: login.php");
    exit();
}

// Fetch purposes from database
$purpose_query = "SELECT DISTINCT PURPOSE FROM login_records WHERE PURPOSE IS NOT NULL";
$purpose_result = $con->query($purpose_query);
$purposes = [];
while($row = $purpose_result->fetch_assoc()) {
    if (!empty($row['PURPOSE'])) {
        $purposes[] = $row['PURPOSE'];
    }
}

// Include the database connection
include 'connector.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$taken_seats = [];
$reservation_date = '';
$time_slot = '';
$lab_classroom = '';

// Check Availability functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_availability'])) {
    $reservation_date = $_POST['reservation_date'];
    $time_slot = $_POST['time_slot'];
    $lab_classroom = $_POST['lab_classroom'];

    // Query to get reserved seats for the selected date, time slot, and classroom
    $sql = "SELECT seat_number FROM lab_reservations
            WHERE reservation_date = '$reservation_date'
            AND time_slot = '$time_slot'
            AND lab_classroom = '$lab_classroom'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $taken_seats[] = $row['seat_number'];
        }
    }
}

// Reserve Now functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve'])) {
    $student_id = "12345"; // Placeholder: Replace this with the actual logged-in user ID
    $reservation_date = $_POST['reservation_date'];
    $time_slot = $_POST['time_slot'];
    $lab_classroom = $_POST['lab_classroom'];
    $seat_number = $_POST['seat_number'];
    $status = "pending";

    echo "<script>alert('Reserve button clicked! Processing reservation...');</script>";

    // Insert the reservation
    $sql = "INSERT INTO lab_reservations (student_id, lab_classroom, seat_number, reservation_date, time_slot, status)
            VALUES ('$student_id', '$lab_classroom', '$seat_number', '$reservation_date', '$time_slot', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>Reservation submitted successfully! Status: Pending</p>";
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
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
            height: 100vh;
            overflow: hidden;
            background: #0a192f;
        }

        .reservation-container {
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .reservation-title {
            background: #2a3b55;
            color: white;
            padding: 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .reservation-title h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
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

        /* Form Styles */
        .reservation-form {
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: #1a2942;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: #2a3b55;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #4a90e2;
            background: #357abd;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
        }

        .form-group input::placeholder {
            color: #a0aec0;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: #4a90e2;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-primary:hover {
            background: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: #1a2942;
            color: #4a90e2;
            border: 1px solid #4a90e2;
        }

        .btn-secondary:hover {
            background: #2a3b55;
            transform: translateY(-2px);
        }

        /* Seat Layout Styles */
        .seat-layout {
            padding: 25px;
            background: #1a2942;
            border-radius: 0 0 15px 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            gap: 15px;
            padding: 20px;
            background: #2a3b55;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .seat {
            aspect-ratio: 1;
            border: none;
            border-radius: 8px;
            background: #1a2942;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .seat:hover:not(.taken) {
            background: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .seat.selected {
            background: #4a90e2;
            color: white;
            border-color: white;
        }

        .seat.taken {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            cursor: not-allowed;
            border-color: rgba(220, 53, 69, 0.3);
        }

        /* Scrollbar Styling */
        .reservation-container::-webkit-scrollbar {
            width: 8px;
        }

        .reservation-container::-webkit-scrollbar-track {
            background: #0a192f;
        }

        .reservation-container::-webkit-scrollbar-thumb {
            background: #2a3b55;
            border-radius: 4px;
        }

        .reservation-container::-webkit-scrollbar-thumb:hover {
            background: #4a90e2;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 20px;
        }

        .seat {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            background: #4ade80;
            color: white;
            transition: all 0.3s ease;
        }

        .seat.taken {
            background: #ef4444;
            cursor: not-allowed;
        }

        .seat:hover:not(.taken) {
            transform: scale(1.05);
        }

        /* Add these CSS rules to your existing styles */
        .seat-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .seat-input-group input[type="number"] {
            flex: 1;
            background: #2a3b55;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .seat-input-group input[type="number"]:hover {
            border-color: #4a90e2;
        }

        .seat-input-group input[type="number"]:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
        }

        .select-seat-btn {
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .select-seat-btn:hover {
            background: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .select-seat-btn i {
            font-size: 1rem;
        }

        .submit-btn {
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 25px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Reservation History Styles */
        .reservation-history {
            margin-top: 0;
            padding: 0;
            box-shadow: none;
            background: transparent;
        }

        .history-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .history-header h2 {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-table-container {
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .history-table th {
            background: #2a3b55;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .history-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0aec0;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-approved {
            background: rgba(74, 222, 128, 0.2);
            color: #4ade80;
        }

        .status-pending {
            background: rgba(250, 204, 21, 0.2);
            color: #facc15;
        }

        .status-rejected {
            background: rgba(248, 113, 113, 0.2);
            color: #f87171;
        }

        .no-records {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
        }

        .history-table tr:hover {
            background: #2a3b55;
        }

        /* Add to your existing styles in reservation.php */
        .seat {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .seat.available {
            background: #dcfce7;
            border: 2px solid #22c55e;
            color: #166534;
        }

        .seat.used {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
            cursor: not-allowed;
        }

        .seat.maintenance {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            cursor: not-allowed;
        }

        .seat.reserved {
            background: #e0e7ff;
            border: 2px solid #6366f1;
            color:rgb(48, 71, 163);
            cursor: not-allowed;
        }

        .seat:hover:not(.used):not(.maintenance):not(.reserved) {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Update the legend style */
        .seat-legend {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #2a3b55;
            border-radius: 8px;
            flex-wrap: wrap;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Modal and Grid Styles */
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 1000px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h2 {
            color: #fff;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .close {
            color: #a0aec0;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close:hover {
            color: #fff;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 30px;
            padding: 15px;
            background: #2a3b55;
            border-radius: 10px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #a0aec0;
        }

        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 6px;
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 12px;
            padding: 20px;
            background: #2a3b55;
            border-radius: 10px;
            margin: 0 auto;
            max-width: 1000px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .seat {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .seat small {
            font-size: 0.75rem;
            margin-top: 4px;
            opacity: 0.8;
        }

        /* Status-specific styles */
        .seat.available {
            background: #dcfce7;
            border: 2px solid #22c55e;
            color: #166534;
        }

        .seat.used {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
            cursor: not-allowed;
        }

        .seat.maintenance {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
            cursor: not-allowed;
        }

        .seat.reserved {
            background: #e0e7ff;
            border: 2px solid rgb(3, 7, 202);
            color:rgb(90, 178, 255);
            cursor: not-allowed;
        }

        .seat:hover:not(.used):not(.maintenance):not(.reserved) {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .seat-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        @media (max-width: 768px) {
            .seat-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 480px) {
            .seat-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            
            .seat {
                font-size: 0.8rem;
            }
        }

        /* Add to your existing styles */
        .status-filters {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .filter-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #2a3b55;
            color: #a0aec0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .filter-btn.active {
            background: #4a90e2;
            color: white;
        }

        .filter-btn:hover {
            background: #357abd;
            color: white;
        }

        .cancel-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            transition: all 0.3s ease;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .cancel-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Add these CSS rules to your existing style section */
        .reservation-form-container {
            display: flex;
            gap: 25px;
            padding: 25px;
            height: calc(100vh - 90px);
            overflow: hidden;
        }

        .form-section {
            flex: 1;
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            overflow-y: auto;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .requests-section {
            flex: 1;
            background: #1a2942;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            overflow-y: auto;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Student Info Section */
        .student-info {
            background: #2a3b55;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .student-info-title {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remaining-sessions input {
            color: #4ade80;
            font-weight: 600;
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
        <a href="history.php"><i class="fas fa-history"></i> History</a>
        <a href="reservation.php"><i class="fas fa-calendar-alt"></i> Reservation</a>
        <a href="labschedule.php"><i class="fas fa-calendar-alt"></i> Lab Schedules</a>
        <a href="viewlabresources.php"><i class="fas fa-book"></i> Lab Resources</a>
        <a href="login.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<div class="content">
    <div class="reservation-container">
        <div class="reservation-title">
            <h1>
                <i class="fas fa-calendar-alt"></i>
                Reservation
            </h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
        <div class="reservation-form-container">
            <div class="form-section">
                <form method="POST" action="" class="reservation-form" onsubmit="return validateForm()">
                    <!-- Student Info Section -->
                    <div class="student-info">
                        <div class="student-info-title">
                            <i class="fas fa-user-graduate"></i>
                            Student Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Student ID</label>
                                <input type="text" 
                                    value="<?php echo isset($user_data['IDNO']) ? htmlspecialchars($user_data['IDNO']) : ''; ?>" 
                                    readonly>
                            </div>
                            <div class="form-group">
                                <label>Student Name</label>
                                <input type="text" 
                                    value="<?php 
                                        echo isset($user_data['FIRSTNAME']) ? 
                                            htmlspecialchars($user_data['FIRSTNAME'] . ' ' . 
                                            $user_data['MIDNAME'] . ' ' . 
                                            $user_data['LASTNAME']) : ''; 
                                    ?>" 
                                    readonly>
                            </div>
                            <div class="form-group remaining-sessions">
                                <label>Available Sessions</label>
                                <input type="text" 
                                    value="<?php echo isset($user_data['REMAINING_SESSIONS']) ? htmlspecialchars($user_data['REMAINING_SESSIONS']) : '0'; ?>" 
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="room">Room:</label>
                            <select name="room" id="room" required>
                                <option value="">Select Room</option>
                                <option value="524">Room 524</option>
                                <option value="526">Room 526</option>
                                <option value="528">Room 528</option>
                                <option value="530">Room 530</option>
                                <option value="542">Room 542</option>
                                <option value="544">Room 544</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Date:</label>
                            <input type="date" name="date" id="date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="time">Time:</label>
                            <select name="time" id="time" required>
                                <option value="">Select Time</option>
                                <option value="07:30">7:30 AM</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="seat_number">Seat Number:</label>
                            <div class="seat-input-group">
                                <input type="number" name="seat_number" id="seat_number" min="1" max="40" required readonly>
                                <button type="button" onclick="openSeatModal()" class="select-seat-btn">
                                    <i class="fas fa-chair"></i> Select Seat
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="purpose">Purpose:</label>
                        <select name="purpose" id="purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="Make a Project">Make a Project</option>
                            <option value="Continuing the Activity">Continuing the Activity</option>
                            <option value="Do a Laboratory work">Do a Laboratory work</option>
                            <option value="Do a Laboratory work">Programming</option>
                            <option value="Do a Laboratory work">Database</option>
                            <?php foreach($purposes as $purpose): ?>
                                <option value="<?php echo htmlspecialchars($purpose); ?>">
                                    <?php echo htmlspecialchars($purpose); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Add this modal HTML at the bottom of your form but before the submit button -->
                    <div id="seatModal" class="modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Select a Seat</h2>
                                <span class="close">&times;</span>
                            </div>
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #dcfce7; border: 2px solid #22c55e;"></div>
                                    <span>Available</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #fee2e2; border: 2px solid #ef4444;"></div>
                                    <span>In Use</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #fef3c7; border: 2px solid #f59e0b;"></div>
                                    <span>Maintenance</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #e0e7ff; border: 2px solid #6366f1;"></div>
                                    <span>Reserved</span>
                                </div>
                            </div>
                            <div class="seat-grid">
                                <?php for($i = 1; $i <= 40; $i++): ?>
                                    <div class="seat available" data-seat="<?php echo $i; ?>" onclick="selectSeat(<?php echo $i; ?>)">
                                        PC<?php echo $i; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Reservation
                    </button>
                </form>
            </div>
            <div class="requests-section">
                <div class="reservation-history">
                    <div class="history-header">
                        <h2><i class="fas fa-clock"></i> Reservation Requests</h2>
                        <div class="status-filters">
                            <button class="filter-btn active" data-status="pending">Pending</button>
                            <button class="filter-btn" data-status="approved">Approved</button>
                            <button class="filter-btn" data-status="rejected">Rejected</button>
                        </div>
                    </div>
                    <div class="history-table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>Seat</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $history_query = "SELECT * FROM reservations 
                                                WHERE student_id = ? 
                                                ORDER BY date DESC, time DESC";
                                $stmt = $con->prepare($history_query);
                                $stmt->bind_param("s", $user_data['IDNO']);
                                $stmt->execute();
                                $history_result = $stmt->get_result();

                                while ($reservation = $history_result->fetch_assoc()) {
                                    $status_class = '';
                                    switch(strtolower($reservation['status'])) {
                                        case 'approved': $status_class = 'status-approved'; break;
                                        case 'pending': $status_class = 'status-pending'; break;
                                        case 'rejected': $status_class = 'status-rejected'; break;
                                    }
                                    ?>
                                    <tr class="reservation-row" data-status="<?php echo strtolower($reservation['status']); ?>" 
                                        style="display: <?php echo $reservation['status'] === 'pending' ? '' : 'none'; ?>">
                                        <td><?php echo date('M d, Y', strtotime($reservation['date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($reservation['time'])); ?></td>
                                        <td>Room <?php echo htmlspecialchars($reservation['room']); ?></td>
                                        <td>PC <?php echo htmlspecialchars($reservation['seat_number']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                        <td><span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst(htmlspecialchars($reservation['status'])); ?>
                                        </span></td>
                                        <td>
                                            <?php if($reservation['status'] === 'pending'): ?>
                                                <button class="cancel-btn" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                if ($history_result->num_rows === 0) {
                                    echo '<tr><td colspan="7" class="no-records">No reservations found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let selectedRoom = '';
let currentStatuses = {};

// Initialize room selection
document.getElementById('room').addEventListener('change', function() {
    selectedRoom = this.value;
    if (selectedRoom) {
        updateSeatAvailability();
    }
});

// Update when date or time changes
document.getElementById('date').addEventListener('change', function() {
    if (!selectedRoom) {
        alert('Please select a room first');
        this.value = '';
        return;
    }
    updateSeatAvailability();
});

document.getElementById('time').addEventListener('change', function() {
    if (!selectedRoom) {
        alert('Please select a room first');
        this.value = '';
        return;
    }
    updateSeatAvailability();
});

// Replace the existing updateSeatAvailability function

async function updateSeatAvailability() {
    const room = document.getElementById('room').value;
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;

    if (!room) {
        alert('Please select a room first');
        return;
    }

    try {
        // Get PC statuses AND reservations in one request
        const response = await fetch('get_seat_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                room: room,
                date: date,
                time: time 
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        // Update seats in the modal
        document.querySelectorAll('.seat').forEach(seat => {
            const seatNumber = parseInt(seat.getAttribute('data-seat'));
            
            // Reset seat to default state
            seat.className = 'seat available';
            seat.onclick = () => selectSeat(seatNumber);

            // Check if seat has an approved reservation
            const approvedReservation = data.reservations.find(r => 
                r.seat_number === seatNumber && r.status === 'approved'
            );

            // Check if seat has a pending reservation
            const pendingReservation = data.reservations.find(r => 
                r.seat_number === seatNumber && r.status === 'pending'
            );

            // Check PC status from admin settings
            const pcStatus = data.pcStatus.find(pc => 
                parseInt(pc.pc_number) === seatNumber
            );

            if (approvedReservation) {
                // Show as used (red) if reservation is approved
                seat.className = 'seat used';
                seat.onclick = null;
                statusText = 'In Use';
            } else if (pendingReservation) {
                // Show as reserved (blue) if reservation is pending
                seat.className = 'seat reserved';
                seat.onclick = null;
                statusText = 'Reserved';
            } else if (pcStatus) {
                // Use admin-set status if no reservation
                seat.className = `seat ${pcStatus.status}`;
                statusText = pcStatus.status.charAt(0).toUpperCase() + pcStatus.status.slice(1);
                if (pcStatus.status === 'used' || pcStatus.status === 'maintenance') {
                    seat.onclick = null;
                }
            } else {
                statusText = 'Available';
            }

            // Update seat display
            seat.innerHTML = `PC${seatNumber}<br><small>(${statusText})</small>`;
        });

    } catch (error) {
        console.error('Error:', error);
        alert('Error loading seat availability');
    }
}

function openSeatModal() {
    if (!selectedRoom) {
        alert('Please select a room first');
        return;
    }
    modal.style.display = "block";
    updateSeatAvailability();
}

function selectSeat(seatNumber) {
    const status = currentStatuses[seatNumber];
    if (status === 'used' || status === 'maintenance') {
        alert(`This PC is currently ${status}`);
        return;
    }
    document.getElementById('seat_number').value = seatNumber;
    modal.style.display = "none";
}

// Modal controls
const modal = document.getElementById("seatModal");
const closeBtn = document.getElementsByClassName("close")[0];

closeBtn.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Form validation
function validateForm() {
    const room = document.getElementById('room').value;
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const seat = document.getElementById('seat_number').value;
    const purpose = document.getElementById('purpose').value;

    if (!room || !date || !time || !seat || !purpose) {
        alert('Please fill in all required fields.');
        return false;
    }

    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        alert('Please select a future date.');
        return false;
    }

    if (seat < 1 || seat > 40) {
        alert('Please select a valid seat.');
        return false;
    }

    const status = currentStatuses[seat];
    if (status === 'used' || status === 'maintenance') {
        alert(`Cannot reserve this PC. Status: ${status}`);
        return false;
    }

    return true;
}

// Auto-update every 30 seconds if modal is open
setInterval(() => {
    if (modal.style.display === 'block' && selectedRoom) {
        updateSeatAvailability();
    }
}, 30000);

// Add to your existing JavaScript
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        const status = button.dataset.status;
        document.querySelectorAll('.reservation-row').forEach(row => {
            if (row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

function cancelReservation(reservationId) {
    if (confirm('Are you sure you want to cancel this reservation?')) {
        fetch('cancel_reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reservation_id: reservationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reservation cancelled successfully');
                location.reload();
            } else {
                alert('Error cancelling reservation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling reservation');
        });
    }
}
</script>

<?php
if(isset($_POST['submit'])) {
    // Verify user data exists
    if (!isset($user_data) || !isset($user_data['IDNO'])) {
        echo "<script>
            alert('Error: User data not found. Please login again.');
            window.location.href='login.php';
        </script>";
        exit;
    }

    $student_id = $user_data['IDNO'];
    $fullname = $user_data['FIRSTNAME'] . ' ' . $user_data['MIDNAME'] . ' ' . $user_data['LASTNAME'];
    $room = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $purpose = $_POST['purpose'];
    $seat_number = $_POST['seat_number'];
    $remaining_sessions = $user_data['REMAINING_SESSIONS'];
    
    // Check lab schedule availability
    $lab_availability = checkLabAvailability($con, $room, $date, $time);
    if (!$lab_availability['available']) {
        echo "<script>alert('" . $lab_availability['message'] . "');</script>";
        exit;
    }

    // Check seat availability
    if (!checkSeatAvailability($con, $room, $date, $time, $seat_number)) {
        echo "<script>alert('This seat is already reserved for the selected date and time.');</script>";
        exit;
    }

    // Check PC status
    $status_query = "SELECT status FROM pc_status WHERE room_number = ? AND pc_number = ?";
    $stmt = $con->prepare($status_query);
    $stmt->bind_param("si", $room, $seat_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $pc_status = $result->fetch_assoc()['status'];
        if($pc_status !== 'available') {
            echo "<script>alert('Selected PC is not available. Current status: " . 
                  ucfirst($pc_status) . "');</script>";
            exit;
        }
    }

    // Check if student has remaining sessions
    if($remaining_sessions <= 0) {
        echo "<script>alert('You have no remaining sessions.');</script>";
        exit;
    }

    $insert_query = "INSERT INTO reservations 
                    (student_id, fullname, room, date, time, purpose, 
                     seat_number, remaining_sessions, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $insert_stmt = $con->prepare($insert_query);
    $insert_stmt->bind_param("ssssssii", 
        $student_id, $fullname, $room, $date, $time, 
        $purpose, $seat_number, $remaining_sessions
    );
    
    if($insert_stmt->execute()) {
        echo "<script>
            alert('Reservation submitted successfully!'); 
            window.location.href='reservation.php';
        </script>";
    } else {
        echo "<script>
            alert('Error submitting reservation. Please try again.');
        </script>";
    }
}
?>

<?php
// Add this after your database connection
function checkLabAvailability($con, $room, $date, $time) {
    // Convert date to day of week
    $day_map = [
        '1' => 'MW', // Monday
        '2' => 'TTH', // Tuesday
        '3' => 'MW', // Wednesday
        '4' => 'TTH', // Thursday
        '5' => 'F', // Friday
        '6' => 'S', // Saturday
    ];
    
    $day_of_week = date('N', strtotime($date));
    $day_group = $day_map[$day_of_week] ?? null;
    
    if (!$day_group) {
        return ['available' => false, 'message' => 'Laboratory is closed on this day'];
    }

    // Convert time to time slot
    $time_slots = [
        '07:30' => '7:30AM-9:00AM',
        '09:00' => '9:00AM-10:30AM',
        '10:30' => '10:30AM-12:00PM',
        '13:00' => '1:00PM-3:00PM',
        '14:30' => '3:00PM-4:30PM',
        '16:00' => '4:30PM-6:00PM',
        '17:30' => '6:00PM-7:30PM'
    ];
    
    $time_slot = $time_slots[$time] ?? null;
    
    if (!$time_slot) {
        return ['available' => false, 'message' => 'Invalid time slot'];
    }

    // Check lab_schedules table
    $query = "SELECT status FROM lab_schedules 
              WHERE room_number = ? 
              AND day_group = ? 
              AND time_slot = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("sss", $room, $day_group, $time_slot);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();

    if ($schedule && $schedule['status'] === 'Occupied') {
        return ['available' => false, 'message' => 'This time slot is occupied according to lab schedule'];
    }

    return ['available' => true, 'message' => 'Available'];
}

// Function to check if seat is available
function checkSeatAvailability($con, $room, $date, $time, $seat) {
    $query = "SELECT COUNT(*) as count FROM reservations 
              WHERE room = ? AND date = ? AND time = ? AND seat_number = ? 
              AND status != 'rejected'";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssi", $room, $date, $time, $seat);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['count'] == 0;
}
?>
</body>
</html>