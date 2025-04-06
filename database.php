<?php
$servername = "sql12.freesqldatabase.com";
$username = "sql12763583";
$password = "uMHTVtH3Hd";
$dbname = "sql12763583";

// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>

<link rel="stylesheet" href="style.css">
<?php error_reporting(0);
ini_set('display_errors', 0); ?>
