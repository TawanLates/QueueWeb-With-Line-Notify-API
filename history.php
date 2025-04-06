<?php  
include 'database.php';

// ดึงข้อมูลประวัติการจัดการคิวจากฐานข้อมูล
$query = "SELECT * FROM history WHERE status = 'waiting' ORDER BY queue_number DESC";
$completedQueues = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการจัดการคิว</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 80%;
            max-width: 800px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        h2 {
            color: #555;
            margin-bottom: 15px;
        }

        ul {
            list-style-type: none;
            padding: 0;
            max-height: 400px; /* กำหนดความสูงสูงสุด */
            overflow-y: auto; /* เพิ่มการเลื่อนในแนวตั้ง */
        }


        li {
            background-color: #fff;
            padding: 15px;
            margin: 8px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        li:hover {
            background-color: #f1f1f1;
        }

        .queue-details {
            color: #555;
            margin-bottom: 10px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ประวัติการจัดการคิว</h1>

        <h2>คิวทั้งหมด</h2>
        <ul>
            <?php if (count($completedQueues) > 0): ?>
                <?php foreach ($completedQueues as $queue): ?>
                    <li>
                        <div class="queue-details">
                            คิวที่ <?= htmlspecialchars($queue['queue_number']) ?>: <?= htmlspecialchars($queue['name']) ?>
                            <br> เวลา: <?= $queue['created_at'] ?>
                            <br> เบอร์โทร: <?= htmlspecialchars($queue['phone_number']) ?>
                            <br> ไส้กรอกเปรี้ยว: <?= $queue['sour_count'] ?>
                            <br> ไส้กรอกไม่เปรี้ยว: <?= $queue['non_sour_count'] ?>
                            <br> ยำแหนม: <?= $queue['nam_count'] ?>
                            <br> ขนาด: <?= $queue['size'] ?>
                            <br> รายละเอียด: <?= $queue['details'] ?>
                            <br> ราคาทั้งหมด: <?= $queue['total_price'] ?>
                            <br> สถานะ: <?= $queue['status'] ?>
                            
                            
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ไม่มีคิวที่เสร็จสิ้นแล้ว</p>
            <?php endif; ?>
        </ul>

        <a href="admin.php" class="back-link">กลับไปหน้า Dashboard</a>
    </div>
</body>
</html>
