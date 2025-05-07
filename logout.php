<?php
session_start();
include("connector.php");

if (isset($_GET['IDNO'])) {
    $idno = $_GET['IDNO'];
    $time_out = date('Y-m-d H:i:s');

    // Update the TIME_OUT for the specific record
    $update_query = "UPDATE login_records SET TIME_OUT = '$time_out' WHERE IDNO = '$idno'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('User logged out successfully.'); window.location.href = 'adsitin.php';</script>";
    } else {
        echo "<script>alert('Error logging out user.'); window.location.href = 'adsitin.php';</script>";
    }
} else {
    header("Location: adsitin.php");
    exit();
}
?>