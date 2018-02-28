
<?php
session_start();
session_destroy();

header("Location: http://localhost/phpProject/src/html/login.html");
?>