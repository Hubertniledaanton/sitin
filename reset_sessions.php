<?php
include("connector.php");
session_start();

// Check if admin is logged in
if (!isset($_SESSION['Username'])) {
header("Location: login.php");
exit();
}

if (isset($_GET['id'])) {
$student_id = $_GET['id'];

// Prepare and execute the update query
$query = "UPDATE user SET REMAINING_SESSIONS = 30 WHERE IDNO = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
echo "<script>
alert('Student sessions have been reset successfully!');
window.location.href = 'liststudent.php';
</script>";
} else {
echo "<script>
alert('Error resetting student sessions!');
window.location.href = 'liststudent.php';
</script>";
}

$stmt->close();
} else {
header("Location: liststudent.php");
exit();
}
?>