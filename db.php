<?php
// db.php
$servername = "localhost"; // Assuming localhost, change if needed
$username = "uiumzmgo1eg2q";
$password = "kuqi5gwec3tv";
$dbname = "db0wnlxirxj75y";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
