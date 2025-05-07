<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
            overflow: hidden; /* Prevents scrolling */
        }
        .container1 {
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            border-radius: 8px;
        }
        .form-control {
            margin-bottom: 10px; /* Reduced margin to save space */
        }
        .form-control input {
            width: 100%;
            padding: 8px; /* Reduced padding to save space */
            box-sizing: border-box;
        }
        .form-control label {
            margin-bottom: 3px; /* Reduced margin to save space */
            display: block;
        }
        button, .lgn {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container1">
        <div class="navbar">
            <img src="logo1.png" alt="Logo 1" class="logo">
        </div>
        <h3>Register</h3>
        <form action="registered.php" method="post">
            <div class="form-control">
                <input type="text" name="IdNo" required>
                <label>IdNo</label>
            </div>
            <div class="form-control">
                <input type="text" name="Lastname" required>
                <label>Lastname</label>
            </div>
            <div class="form-control">
                <input type="text" name="Firstname" required>
                <label>Firstname</label>
            </div>
            <div class="form-control">
                <input type="text" name="Midname" required>
                <label>Midname</label>
            </div>
            <div class="form-control">
<select name="Course" required>
<option value="" disabled selected></option>
<option value="BSIT">BSIT</option>
<option value="BSCS">BSCS</option>
<option value="BSHM">BSHM</option>
<option value="BEED">BEED</option>
<option value="BSCRIM">BSCRIM</option>
<option value="BSED">BSED</option>
<option value="BSBA">BSBA</option>
<option value="BSCA">BSCA</option>
<!-- Add more options as needed -->
</select>
<label>Course</label>
</div>
<div class="form-control">
<select name="YearLevel" required>
<option value="" disabled selected></option>
<option value="1">1st Year</option>
<option value="2">2nd Year</option>
<option value="3">3rd Year</option>
<option value="4">4th Year</option>
<!-- Add more options as needed -->
</select>
<label>Year Level</label>
</div>
            <div class="form-control">
                <input type="text" name="Email" required>
                <label>Email</label>
            </div>
            <div class="form-control">
                <input type="text" name="Username" required>
                <label>Username</label>
            </div>
            <div class="form-control">
                <input type="password" name="Password" required>
                <label>Password</label>
            </div>
            <button type="submit">Sign In</button>
            <a class="lgn" href="login.php" style="margin-left:41% ">Login</a>
        </form>
    </div>
</body>
</html>

<?php
session_start();
include("connector.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
$idno = $_POST["IdNo"];
$lastname = $_POST["Lastname"];
$firstname = $_POST["Firstname"];
$midname = $_POST["Midname"];
$course = $_POST["Course"];
$yearLevel = $_POST["YearLevel"];
$username = $_POST["Username"];
$password = $_POST["Password"];
$email = $_POST["Email"];

// Debugging output
//var_dump($username, $password, !is_numeric($username));

if (!empty($username) && !empty($password) && !is_numeric($username)) {
$query = "INSERT INTO user (IDNO, LASTNAME, FIRSTNAME, MIDNAME, COURSE, YEARLEVEL, USERNAME, PASSWORD, EMAIL) VALUES
('$idno', '$lastname', '$firstname', '$midname', '$course', '$yearLevel', '$username', '$password', '$email')";

if (mysqli_query($con, $query)) {
echo "<script type='text/javascript'> alert('Successfully Registered'); window.location.href = 'login.php';
</script>";
} else {
echo "<script type='text/javascript'> alert('Registration Failed: " . mysqli_error($con) . "');</script>";
}
}
}
?>


