<?php
include("connector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST["idno"];
    
    // Prepare the SQL statement
    $stmt = $con->prepare("SELECT * FROM user WHERE IDNO = ?");
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "
            <div style='border: 1px solid #ccc; padding: 20px; border-radius: 8px; background-color: #f9f9f9;'>
                <h2>User Details</h2>
                <p><strong>IDNO:</strong> " . htmlspecialchars($row['IDNO']) . "</p>
                <p><strong>Last Name:</strong> " . htmlspecialchars($row['LASTNAME']) . "</p>
                <p><strong>First Name:</strong> " . htmlspecialchars($row['FIRSTNAME']) . "</p>
                <p><strong>Middle Name:</strong> " . htmlspecialchars($row['MIDNAME']) . "</p>
                <p><strong>Course:</strong> " . htmlspecialchars($row['COURSE']) . "</p>
                <p><strong>Year Level:</strong> " . htmlspecialchars($row['YEARLEVEL']) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($row['EMAIL']) . "</p>
                <p><strong>Remaining Sessions:</strong> " . htmlspecialchars($row['REMAINING_SESSIONS']) . "</p>
                <form action='adsitin.php' method='POST'>
                    <input type='hidden' name='idno' value='" . htmlspecialchars($row['IDNO']) . "'>
                    <input type='hidden' name='lastname' value='" . htmlspecialchars($row['LASTNAME']) . "'>
                    <input type='hidden' name='firstname' value='" . htmlspecialchars($row['FIRSTNAME']) . "'>
                    <input type='hidden' name='midname' value='" . htmlspecialchars($row['MIDNAME']) . "'>
                    <input type='hidden' name='course' value='" . htmlspecialchars($row['COURSE']) . "'>
                    <input type='hidden' name='yearlevel' value='" . htmlspecialchars($row['YEARLEVEL']) . "'>
                    <input type='hidden' name='email' value='" . htmlspecialchars($row['EMAIL']) . "'>
                    <input type='hidden' name='remaining_sessions' value='" . htmlspecialchars($row['REMAINING_SESSIONS']) . "'>
                    <button type='submit' style='margin-top: 10px; padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>Sit In</button>
                </form>
            </div>
        ";
    } else {
        echo "<p style='color: red;'>No records found for IDNO: " . htmlspecialchars($idno) . "</p>";
    }

    // Close the statement
    $stmt->close();
}
?>