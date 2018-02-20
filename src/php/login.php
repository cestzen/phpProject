<?php
session_start();

$desiredUser = $_POST['name'];
$desiredPassword = $_POST['password'];
$servername = "localhost";
$username = "myUser";
$password = "myUser";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
$sql = "SELECT id FROM users WHERE username = '" . $desiredUser . "' AND password='" . $desiredPassword . "'";
$result = $conn->query($sql);
// Check if this user exists:
if ($result->num_rows == 1) {
    // Store the login in the session:
    session_start();
    $_SESSION['username'] = $result->fetch_assoc()['id'];
    header("Location: http://localhost/phpProject/src/php/showBusses.php");
} else {
    header("Location: http://localhost/phpProject/src/php/login.html");
}
$conn->close();

?>