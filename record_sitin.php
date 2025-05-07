<?php
include("connector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchId = $_POST['searchId'];
    $activity = $_POST['activity'];
    $labRoom = $_POST['labRoom'];

    $stmt = $con->prepare("INSERT INTO login_records (IDNO, ACTIVITY, LAB_ROOM, TIMESTAMP) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $searchId, $activity, $labRoom);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
}
?>
