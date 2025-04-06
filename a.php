<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจองคิวอาหารยำแหนม</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['user'])) {
        echo '<a href="line_login.php">ล็อกอินด้วย LINE</a>';
        exit();
    }
    $user = $_SESSION['user'];
    ?>
    
    <h1>ระบบจองคิวอาหารยำแหนม</h1>
    <p>สวัสดี, <?php echo htmlspecialchars($user['displayName']); ?>!</p>
    
    <form id="bookingForm">
        <label for="name">ชื่อ:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['displayName']); ?>" readonly>
        
        <label>เลือกเมนู:</label>
        <div>
            <input type="number" id="menu1" name="menu1" value="0" min="0"> ไส้กรอกเปรี้ยว (15 บาท)
        </div>
        <div>
            <input type="number" id="menu2" name="menu2" value="0" min="0"> ไส้กรอกไม่เปรี้ยว (15 บาท)
        </div>
        <div>
            <input type="number" id="menu3" name="menu3" value="0" min="0"> ยำแหนม (40 บาท)
        </div>
        
        <label>ขนาด:</label>
        <div>
            <input type="radio" id="normal" name="size" value="normal" checked> ธรรมดา (40 บาท)
            <input type="radio" id="special" name="size" value="special"> พิเศษ (50 บาท)
        </div>
        
        <label for="details">รายละเอียดเพิ่มเติม:</label>
        <textarea id="details" name="details"></textarea>
        
        <button type="submit">จองคิว</button>
    </form>
    
    <h2>จำนวนคิวที่เหลืออยู่</h2>
    <p id="queueStatus">กำลังโหลด...</p>
    
    <script src="script.js"></script>
</body>
</html>