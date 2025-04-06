<?php
// รวมไฟล์เชื่อมต่อฐานข้อมูล (ไฟล์ database.php ต้องมีการเชื่อมต่อกับฐานข้อมูล)
include 'database.php';

// ตั้งค่าการเชื่อมต่อให้ใช้ UTF-8
$conn->set_charset("utf8");

// ตรวจสอบการเพิ่มสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['foodmore']) && isset($_POST['pricefood'])) {
    $foodmore = $_POST['foodmore'];  // ชื่อสินค้า
    $pricefood = $_POST['pricefood'];  // ราคาสินค้า
    $imgfood = $_FILES['imgfood']['name'];  // ชื่อไฟล์รูปภาพ
    $imgfood_tmp = $_FILES['imgfood']['tmp_name'];  // ไฟล์ชั่วคราว

    // ตรวจสอบว่ามีการอัพโหลดไฟล์หรือไม่
    if ($imgfood && $imgfood_tmp) {
        // กำหนดที่อยู่ที่จะเก็บไฟล์
        $imgfood_path = "pic/" . basename($imgfood);

        // ย้ายไฟล์จากโฟลเดอร์ชั่วคราวไปยังโฟลเดอร์ที่ต้องการเก็บ
        if (move_uploaded_file($imgfood_tmp, $imgfood_path)) {
            // คำสั่ง SQL สำหรับการเพิ่มสินค้า
            $sql = "INSERT INTO foodmore (morefood, pricefood, imgfood) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // ตรวจสอบว่ามีการเตรียมคำสั่ง SQL สำเร็จหรือไม่
            if ($stmt === false) {
                die("ไม่สามารถเตรียมคำสั่ง SQL ได้: " . $conn->error);
            }

            // ผูกตัวแปรและรันคำสั่ง
            $stmt->bind_param("sss", $foodmore, $pricefood, $imgfood);

            if ($stmt->execute()) {
                echo "<script>alert('สินค้าถูกเพิ่มเรียบร้อยแล้ว'); window.location.href='add_product.php';</script>";
            } else {
                echo "<script>alert('ไม่สามารถเพิ่มสินค้าได้: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('ไม่สามารถอัพโหลดรูปภาพได้');</script>";
        }
    } else {
        echo "<script>alert('กรุณาเลือกรูปภาพสินค้า');</script>";
    }
}

// ตรวจสอบการลบสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete']) && isset($_POST['ID'])) {
    $productID = $_POST['ID'];  // รับ ID ของสินค้าที่จะลบ

    // คำสั่ง SQL สำหรับการลบสินค้า
    $sql = "DELETE FROM foodmore WHERE ID = ?";
    $stmt = $conn->prepare($sql);

    // ตรวจสอบว่ามีการเตรียมคำสั่ง SQL สำเร็จหรือไม่
    if ($stmt === false) {
        die("ไม่สามารถเตรียมคำสั่ง SQL ได้: " . $conn->error);
    }

    // ผูกตัวแปรและรันคำสั่ง
    $stmt->bind_param("i", $productID);

    if ($stmt->execute()) {
        echo "<script>alert('สินค้าถูกลบเรียบร้อยแล้ว'); window.location.href='add_product.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบสินค้าได้: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>จัดการสินค้า</title>
  <link rel="stylesheet" href="add_product.css">
</head>
<body>
  <h2>เพิ่มสินค้าใหม่</h2>
  <!-- ฟอร์มสำหรับเพิ่มสินค้าใหม่ -->
  <form action="add_product.php" method="post" enctype="multipart/form-data">
   
    <label for="foodmore">ชื่อสินค้า:</label>
    <input type="text" id="foodmore" name="foodmore" required><br><br>
    
    <label for="pricefood">ราคา:</label>
    <input type="number" id="pricefood" name="pricefood" step="0.01" required><br><br>

    <label for="imgfood">เลือกรูปภาพ:</label>
    <input type="file" id="imgfood" name="imgfood" accept="image/*" required><br><br>
    
    <input type="submit" value="เพิ่มสินค้า"><br><br><br>
    <a href="admin.php" class="back-link">กลับไปหน้า Dashboard</a>
  </form>

  <h3>รายละเอียดสินค้า</h3>
  <?php
  // ดึงข้อมูลสินค้าจากฐานข้อมูล
  $sql = "SELECT * FROM foodmore";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          // แสดงรายละเอียดสินค้า พร้อมปุ่ม Edit และ Delete
          echo "<div class='photomid'>";
          echo "<img src='pic/" . htmlspecialchars($row['imgfood']) . "' alt='" . htmlspecialchars($row['morefood']) . "' width='100'><br><br>";
          echo "ชื่อสินค้า: " . htmlspecialchars($row['morefood']) . "<br><br>";
          echo "ราคา: " . $row['pricefood'] . " บาท<br><br>";

          // ฟอร์มลบสินค้า
          echo "<form action='add_product.php' method='post' style='display:inline;'>";
          echo "<input type='hidden' name='ID' value='" . $row['ID'] . "'>";  // ส่ง ID ของสินค้าผ่านฟอร์ม
          echo "<input type='submit' name='delete' value='ลบ' style='background-color: red;' onclick=\"return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้า?');\">";
          echo "</form>";

          echo "<br>_____________________________________________________________<br>";
          echo "</div><br>";
      }
  } else {
      echo "ไม่พบสินค้า!";
  }

  // ปิดการเชื่อมต่อฐานข้อมูล
  $conn->close();
  ?>
</body>
</html>
