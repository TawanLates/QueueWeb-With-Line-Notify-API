<?php
include 'database.php';
$conn->set_charset("utf8mb4");
// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT morefood, pricefood, imgfood FROM foodmore";
$result = $conn->query($sql);

// แสดงผลข้อมูล
if ($result->num_rows > 0) {
    // เริ่มการแสดงผลข้อมูลในฟอร์ม
    while($row = $result->fetch_assoc()) {
        echo "<div class='food-item'>";
        echo "<h3>" . $row["morefood"] . "</h3>";
        echo "<p>ราคา: " . $row["pricefood"] . " บาท</p>";
        echo "<img src='pic/" . $row["imgfood"] . "' alt='" . $row["morefood"] . "' />";
        echo "</div>";
    }
} else {
    echo "ไม่พบข้อมูล";
}


?>
