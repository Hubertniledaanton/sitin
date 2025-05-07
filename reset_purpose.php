<?php
include("connector.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idno = $_POST['idno'];
    $time_in = $_POST['time_in'];
    
    // Update the purpose to NULL for the specific record
    $query = "UPDATE login_records SET PURPOSE = NULL WHERE IDNO = ? AND TIME_IN = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $idno, $time_in);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 