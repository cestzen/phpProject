 <?php
function writeMsg() {
    $desiredUser = $_POST['name'];
    $desiredPassword = $_POST['password'];
    $servername = "localhost";
    $username = "myUser";
    $password = "myUser";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully";
    $sql = "INSERT INTO `users` (`id`, `username`, `password`) VALUES (NULL, '".$desiredUser."', '".$desiredPassword."')";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully";
    } else {
        echo "Error creating database: " . $conn->error;
    }
    $conn->close();
}
?> 

