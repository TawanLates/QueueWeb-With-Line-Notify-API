<?php
include 'database.php'; // รวมการเชื่อมต่อกับฐานข้อมูล
$conn->set_charset("utf8mb4");
session_start();
$user = $_SESSION['user'];

// ฟังก์ชันสำหรับส่งการแจ้งเตือนผ่าน LINE Notify
function sendLineNotify($message) {
    $line_token = 'jXuhuRlKbZgLtoLVjWrSGDWWcma25csM1WDas2TdpRa'; // นำโทเคนจาก LINE Notify มาใส่ตรงนี้
    $data = array('message' => $message);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $line_token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
    $name = $_POST['name'];
    $phoneNumber = $_POST['phone_number'];
    
    $sourCount = intval($_POST['sour_count']);
    $nonSourCount = intval($_POST['non_sour_count']);
    $namCount = intval($_POST['nam_count']);
    $size = isset($_POST['size']) ? $_POST['size'] : null; // เช็คว่าติ๊ก size หรือไม่
    $details = $_POST['details'];

    // คำนวณราคาของไส้กรอก
    $totalSausages = $sourCount + $nonSourCount;
    $sausagePrice = $totalSausages * 15; // ราคา 15 บาทต่อไม้

    // คำนวณราคายำแหนมตามขนาดที่เลือก
    $namPrice = 0;
    if ($size == "ธรรมดา") {
        $namPrice = $namCount * 40;
    } elseif ($size == "พิเศษ") {
        $namPrice = $namCount * 50;
    }

    // รวมราคาทั้งหมด
    $totalPrice = $sausagePrice + $namPrice;

    // ตรวจสอบว่ามีการจองคิวสำหรับหมายเลขนี้หรือไม่
    $existingQueue = $conn->query("SELECT * FROM queues WHERE phone_number = '$phoneNumber'")->fetch_assoc();

    if ($existingQueue) {
    echo "<script>alert('ผู้ใช้นี้ได้ทำการสั่งไว้แล้ว!');</script>";
} else {
    // จองคิวใหม่
    $lastQueue = $conn->query("SELECT MAX(queue_number) as lastQueue FROM queues")->fetch_assoc();
    $newQueueNumber = $lastQueue['lastQueue'] + 1;

    // คำนวณราคาเพิ่มเติมจาก foodmore
    $foodmoreTotal = 0;
    $foodmoreDetails = ""; // สำหรับเก็บรายละเอียดของสินค้าเพิ่มเติม
    $sql = "SELECT * FROM foodmore";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $quantity = (int)$_POST['quantity_' . $row['id']] ?: 0; // ใช้ค่า quantity จาก POST
        $foodmoreTotal += $quantity * $row['pricefood']; // คำนวณราคาสินค้าเพิ่มเติม
        if ($quantity > 0) {
            // เก็บชื่ออาหารและจำนวน พร้อมราคา
            $foodmoreDetails .= $row['morefood'] . ": " . $quantity . " ชิ้น " . $row['pricefood'] * $quantity . " บาท\n";
        }
    }

    // บันทึกการจองลงในฐานข้อมูล history
    $insertQueryHistory = "INSERT INTO history (name, phone_number, queue_number, status, sour_count, non_sour_count, nam_count, size, details, total_price, foodmoreTotal) 
            VALUES ('$name', '$phoneNumber', $newQueueNumber, 'waiting', $sourCount, $nonSourCount, $namCount, '$size', '$details', $totalPrice, $foodmoreTotal)";
    $conn->query($insertQueryHistory);

    // บันทึกการจองลงในฐานข้อมูล queues
    $insertQueryQueues = "INSERT INTO queues (name, phone_number, queue_number, status, sour_count, non_sour_count, nam_count, size, details, total_price, foodmoreTotal) 
             VALUES ('$name', '$phoneNumber', $newQueueNumber, 'waiting', $sourCount, $nonSourCount, $namCount, '$size', '$details', $totalPrice, $foodmoreTotal)";
    
    // ตรวจสอบการบันทึกข้อมูล
    if (!$conn->query($insertQueryQueues)) {
        echo "<script>alert('เกิดข้อผิดพลาดในการจองคิว: " . $conn->error . "');</script>";
    } else {
        // ส่งการแจ้งเตือนผ่าน LINE Notify
        $message = "คุณ " . $name . " ได้จองคิว \nคิวที่ " . $newQueueNumber .
                   "\nไส้กรอกเปรี้ยว: $sourCount ไม้" .
                   "\nไส้กรอกไม่เปรี้ยว: $nonSourCount ไม้" .
                   "\nยำแหนม: $namCount ชุด" . // ข้อมูลของยำแหนม
                   "\nขนาด: " . ($size ? $size : "ไม่ได้เลือก") . // ขนาด (หากไม่ได้เลือก)
                   "\n\nราคารวม: ".$totalPrice . 
                   "\n\nรายละเอียดเพิ่มเติม: ".$details .
                   "\n\nอาหารเพิ่มเติม:\n" . $foodmoreDetails . // รายละเอียดสินค้าเพิ่มเติม
                   "\nรวมราคาสุทธิ: ".($totalPrice + $foodmoreTotal)." บาท" . // รวมราคาทั้งหมด
                   "\nเบอร์โทร: " . $phoneNumber;

        sendLineNotify($message);
        echo "<script>alert('จองคิวสำเร็จ! คิวของคุณคือคิวที่ $newQueueNumber');</script>";
    }
}


}

// นับจำนวนคิวที่ยังรออยู่ในฐานข้อมูล
$totalWaiting = $conn->query("SELECT COUNT(*) as total FROM queues WHERE status = 'waiting'")->fetch_assoc()['total'];
$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจองคิว</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            padding-top: 60px;
        }

        form {
            max-width: 450px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid black;
            border-radius: 10px;
            background-color: #fff;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        img {
            width: 100%;
            max-width: 350px;
            height: auto;
            margin-bottom: 10px;
            border-radius: 10px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
            text-align: left;
        }

        input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        .size-options {
            display: flex;
            justify-content: space-between;
            padding: 1px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }

        button:hover {
            background-color: #333;
        }

        @media screen and (max-width: 480px) {
            form {
                max-width: 90%;
                padding: 15px;
            }
            
            img {
                max-width: 100%;
            }
            
            button {
                font-size: 16px;
            }
        }
        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px; 
        }
         .image-container {
            display: flex;
            justify-content: center; /* จัดรูปภาพให้อยู่กลางแนวนอน */
            align-items: center;     /* จัดรูปภาพให้อยู่กลางแนวตั้ง */
 
        }

        img {
            max-width: 180px;  /* ขยายรูปภาพให้เต็มความกว้างของ container */
            max-height: 180px; /* ขยายรูปภาพให้เต็มความสูงของ container */
        }
    </style>
</head>
<body>
<br>
<br>
<br>
    <div class="image-container">
        <img src="https://i.ibb.co/Ngx1Rb5j/Lilac-Cat-Pet-Shops-Logo.png" alt="">
    </div>
<form method="POST" onsubmit="return validateInput()">
    <h3> ผู้ใช้ : <span style="color: green;"> <?php echo htmlspecialchars($user['displayName']); ?></span></h3>
    
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['displayName']); ?>" hidden>

    <a style='color:red;text-decoration: underline overline;' href="login.php">กดที่นี่เปลี่ยนเบอร์โทรศัพท์</a>

    <input type="text" name="phone_number" id="phone_number" required 
           pattern="\d{10}" maxlength="10" inputmode="numeric" 
           oninput="this.value = this.value.replace(/\D/, '')" value="<?php echo htmlspecialchars($phone_number); ?>" hidden>
           
    <label for="phone_number">
    เบอร์โทรศัพท์ของคุณ : <span style="color: green;"> <?php echo htmlspecialchars($phone_number); ?> 
    </span>
        
    </label>
    <br>
    
    <img src="https://cheewajit.com/app/uploads/2018/12/web-2.jpg" alt="ไส้กรอกเปรี้ยว">
    <label for="sour_count">จำนวนไส้กรอก เปรี้ยว (15 บ./ไม้):</label>
    <input type="number" id="sour_count" name="sour_count" min="0" max="99" step="1" inputmode="numeric" oninput="calculateTotal()">
    
    <label for="non_sour_count">จำนวนไส้กรอก ไม่เปรี้ยว (15 บ./ไม้):</label>
    <input type="number" id="non_sour_count" name="non_sour_count" min="0" max="99" step="1" inputmode="numeric" oninput="calculateTotal()">
    <br><br>
    
    <img src="https://s359.kapook.com/pagebuilder/2db443b8-5b2a-472b-96d9-291b4c19c095.jpg" alt="ยำแหนม">
    <label for="nam_count">จำนวนยำแหนม:</label>
    <input type="number" id="nam_count" name="nam_count" min="0" max="99" step="1" inputmode="numeric" oninput="calculateTotal()">
    
    <label for="size">ขนาดยำแหนม:</label>
    <div class="size-options">
        <label><input type="radio" name="size" value="ธรรมดา" onclick="calculateTotal()"> ธรรมดา (40 บ.)</label>
        <label><input type="radio" name="size" value="พิเศษ" onclick="calculateTotal()"> พิเศษ (50 บ.)</label>
    </div>
    
    <label for="details">รายละเอียดเพิ่มเติม:</label>
    <textarea name="details"></textarea>

    <h3>สินค้าเพิ่มเติม</h3>
    <?php 
// ดึงข้อมูลสินค้าเพิ่มเติมจากฐานข้อมูล
$sql = "SELECT * FROM foodmore";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div>";
        echo "<img src='pic/".$row['imgfood']."' alt='".$row['morefood']."' width='100'><br>";
        echo "ชื่ออาหาร: " . mb_convert_encoding($row['morefood'], 'UTF-8', 'auto') . "<br><br>";
        echo "ราคาต่อชิ้น: " . $row['pricefood'] . " บาท<br><br>";
        echo "<label for='quantity_".$row['id']."'>จำนวน:</label>";
        echo "<input type='number' id='quantity_".$row['id']."' name='quantity_".$row['id']."' min='0' value='0' oninput='calculateTotal()' /> <br><br>";
        echo "</div>";
    }
} else {
    echo "";
}
?>




    <h3>ราคารวมทั้งหมด: <span id="total_price">0</span> บาท</h3>
    
    <button type="button" onclick="validateInput()">สั่งอาหาร</button>
</form>

<script>
    function validateInput() {
        var sourCount = document.getElementById('sour_count').value || 0;
        var nonSourCount = document.getElementById('non_sour_count').value || 0;
        var namCount = document.getElementById('nam_count').value || 0;
        var size = document.querySelector('input[name="size"]:checked'); // ตรวจสอบว่ามีการเลือกขนาดยำแหนมหรือไม่
        
        // ตรวจสอบจำนวนที่ไม่เกิน 99
        if (sourCount > 99 || nonSourCount > 99 || namCount > 99) {
            alert('ไม่สามารถสั่งเกิน 99');
            return false;
        }

        // ตรวจสอบว่ามีการกรอกจำนวนอาหารหรือไม่
        if (sourCount == 0 && nonSourCount == 0 && namCount == 0) {
            alert('กรุณาสั่งอาหารอย่างน้อย 1 รายการ');
            return false;
        }

        // ตรวจสอบการเลือกขนาดยำแหนม
        if (namCount > 0 && !size) {
            alert('กรุณาเลือกขนาดของยำแหนม');
            return false;
        }

        else{
            var totalPrice = document.getElementById('total_price').textContent;
            var confirmMessage = 'คุณจะได้รับคิวที่ <?= $totalWaiting + 1 ?> \nราคารวมทั้งหมด: ' + totalPrice + ' บาท\nกดตกลงเพื่อสั่งอาหาร';
        
        if (confirm(confirmMessage)) {
            document.querySelector('form').submit();
        } else {
            alert('การสั่งอาหารถูกยกเลิก');
        }
    
    }
        return true;

    }

    function calculateTotal() {
    var sourCount = parseInt(document.getElementById('sour_count').value) || 0;
    var nonSourCount = parseInt(document.getElementById('non_sour_count').value) || 0;
    var namCount = parseInt(document.getElementById('nam_count').value) || 0;
    
    var sizeRadios = document.getElementsByName('size');
    var sizePrice = 0;
    for (var i = 0; i < sizeRadios.length; i++) {
        if (sizeRadios[i].checked) {
            sizePrice = (sizeRadios[i].value === "ธรรมดา") ? 40 : 50;
        }
    }

    var total = (sourCount + nonSourCount) * 15 + namCount * sizePrice;

    // เพิ่มการคำนวณราคาเพิ่มเติม
    <?php
        // ดึงข้อมูลสินค้าจากฐานข้อมูล
        $sql = "SELECT * FROM foodmore";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "
            var quantity_".$row['id']." = parseInt(document.getElementById('quantity_".$row['id']."').value) || 0;
            total += quantity_".$row['id']." * ".$row['pricefood']."; ";
        }
    ?>

    document.getElementById('total_price').textContent = total;
}


    
</script>
<style>
    .queue-info {
        text-align: center;
        margin-top: 20px;
    }

    .queue-info h2 {
        font-size: 22px;
        color: #333;
    }

    .queue-info p {
        font-size: 18px;
        color: #555;
    }

    .queue-links {
        display: flex;
        justify-content: center; 
        margin-top: 15px;
    }

    .queue-button {
        display: inline-block;
        padding: 12px 20px;
        background-color:rgb(38, 180, 19);
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-size: 16px;
        transition: 0.3s;
        font-weight: bold;
    }

    .queue-button:hover {
        background-color: #e04e2b;
    }

</style>
<div class="queue-info">

    <h1>จำนวนคิวทั้งหมดที่รอ: <?= $totalWaiting ?> คิว</h1>
    <div class="queue-links">
        <a href="check_queue.php" class="queue-button">ตรวจสอบคิว</a>
    </div>
</div>
</body>
</html>
