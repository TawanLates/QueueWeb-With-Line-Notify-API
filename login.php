<link rel="stylesheet" href="style.css">
<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phoneNumber = $_POST['phone_number'];

    // เก็บเบอร์โทรใน Session
    $_SESSION['phone_number'] = $phoneNumber;

    // ย้ายไปหน้าจองคิว
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบจองคิว</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="image-container">
        <img src="https://i.ibb.co/Ngx1Rb5j/Lilac-Cat-Pet-Shops-Logo.png" alt="">
    </div>
    
    <h1>สั่งอาหารร้านยำแหนมดาวคะนอง</h1><br>

    <form method="POST" action="index.php">
    
    <div class="queue-links">
        <a style='color:blue;text-decoration: underline overline;' href="check_queue.php">กดที่นี่ตรวจสอบคิวของคุณ</a>
        <p></p>
        <p></p>
    </div>
        <label for="phone_number">กรอกเบอร์โทรศัพท์ของคุณ</label>
        <input type="text" name="phone_number" id="phone_number" required 
        pattern="\d{10}" maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/\D/, '')">
        <br>
        <button type="submit">สั่งอาหาร</button>
    </form>

    <br>
<style>
    .queue-links {
        display: flex;
        justify-content: center; 

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
</body>
</html>
