
<?php
session_start();

$servername = "localhost";
$username = "myUser";
$password = "myUser";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header("Location: http://localhost/phpProject/src/php/showBusses.php");
}
$sql = "INSERT INTO `useraction` (`id`, `userId`, `action`, `time`) VALUES (NULL, '" . $_SESSION['username'] . "', '" . $_POST['string'] . "', '" . date('Y-m-d H:i:s') . "')";
$conn->query($sql);
$conn->close();
?>