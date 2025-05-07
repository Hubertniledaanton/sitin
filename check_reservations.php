<?php
include("connector.php");

// Check if table exists
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'reservations'");
if (mysqli_num_rows($table_check) == 0) {
    echo "Reservations table does not exist!";
    exit();
}

// Check table structure
$structure = mysqli_query($con, "DESCRIBE reservations");
echo "<h3>Table Structure:</h3>";
while ($row = mysqli_fetch_assoc($structure)) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

// Check if there are any reservations
$reservations = mysqli_query($con, "SELECT * FROM reservations");
echo "<h3>Number of Reservations:</h3>";
echo mysqli_num_rows($reservations);

echo "<h3>Reservations Data:</h3>";
while ($row = mysqli_fetch_assoc($reservations)) {
    echo "ID: " . $row['id'] . "<br>";
    echo "Student ID: " . $row['student_id'] . "<br>";
    echo "Room: " . $row['room'] . "<br>";
    echo "Date: " . $row['date'] . "<br>";
    echo "Time: " . $row['time'] . "<br>";
    echo "Status: " . $row['status'] . "<br>";
    echo "-------------------<br>";
}
?> 