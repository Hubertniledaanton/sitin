<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>CCS Sitin Monitoring System</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class=""content>
        <div class="container">
            <div class="navbar">
                <img src="logo1.png" alt="Logo 1" class="logo">
            </div>
            <h2>CCS Sitin Monitoring System</h2>
            <form action="login.php" method="POST">
                <div class="form-control">
                    <input type="text" name="Username" required>
                    <label>Username</label>
                </div>
                <div class="form-control">
                    <input type="password" name="Password" required>
                    <label>Password</label>
                </div>
                <button type="submit">Login</button>
                <div style="display: flex;">
                    <div>Don't have an account? 
                        <a href="registered.php">Register here</a>
                    </div>
                </div>
            </form>
        </div>
        </div>
        <script src="" async defer></script>
    </body>
</html>

<?php
session_start();
include("connector.php"); // Ensure this connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["Username"];
    $password = $_POST["Password"];
    
    // Check if the entered credentials are for the default admin account
    if ($username === 'admin' && $password === 'user') {
        $_SESSION["Username"] = $username;
        echo "<script>alert('Admin Login Successful'); window.location.href = 'admindash.php';</script>";
    } else {
        // Check if user exists in the database
        $query = "SELECT * FROM user WHERE USERNAME='$username' AND PASSWORD='$password'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            // User found, start session
            $user = mysqli_fetch_assoc($result);
            $_SESSION["Username"] = $username;

            // Insert login recor
            echo "<script>alert('Login Successful'); window.location.href = 'dashboard.php';</script>";
        } else {
            // Invalid login
            echo "<script>alert('Invalid Username or Password'); window.location.href = 'login.php';</script>";
        }
    }
}
?>
