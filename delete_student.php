<?php
session_start();
include("connector.php");

// Check if admin is logged in
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<script>
            alert('No student ID provided!');
            window.location.href = 'liststudent.php';
          </script>";
    exit();
}

$student_id = mysqli_real_escape_string($con, $_GET['id']);

// Begin transaction
mysqli_begin_transaction($con);

try {
    // Delete related records first (foreign key constraints)
    // Delete from login_records
    $query = "DELETE FROM login_records WHERE IDNO = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();

    // Delete from reward_points
    $query = "DELETE FROM reward_points WHERE student_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();

    // Delete from reservations if exists
    $query = "DELETE FROM reservations WHERE student_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();

    // Finally, delete the student from user table
    $query = "DELETE FROM user WHERE IDNO = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();

    // If we get here, commit the transaction
    mysqli_commit($con);
    
    echo "<script>
            alert('Student successfully deleted!');
            window.location.href = 'liststudent.php';
          </script>";
} catch (Exception $e) {
    // If there's an error, rollback the transaction
    mysqli_rollback($con);
    
    echo "<script>
            alert('Error deleting student: " . mysqli_error($con) . "');
            window.location.href = 'liststudent.php';
          </script>";
}

// Close connection
mysqli_close($con);
?> 