<?php
include("connector.php");

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'error' => true,
        'message' => 'Student ID is required'
    ]);
    exit;
}

$student_id = $_GET['id'];

// Check for active sessions
$query = "SELECT COUNT(*) as active_count 
          FROM login_records 
          WHERE IDNO = ? AND TIME_OUT IS NULL";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    'error' => false,
    'hasActiveSession' => $data['active_count'] > 0,
    'message' => $data['active_count'] > 0 ? 'Student has an active session' : 'No active session'
]);