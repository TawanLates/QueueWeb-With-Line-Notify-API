<?php  
include 'database.php';

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

// แจ้งว่ากำลังทำอาหาร
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cooking_notify'])) {
    $queueId = intval($_POST['queue_id']);
    $queue = $conn->query("SELECT * FROM queues WHERE id = $queueId")->fetch_assoc();
    
    if ($queue) {
        $message = "คุณ ".$queue['name'] ." คิวที่ " . $queue['queue_number'] . " อยู่ในขั้นตอนทำอาหาร โปรดรอสักครู่";
        sendLineNotify($message);
        $alertMessage = 'แจ้งว่ากำลังทำอาหารส่งเรียบร้อย';
    } else {
        $alertMessage = 'ไม่พบคิวนี้';
    }
}

// แจ้งเตือนอาหารเสร็จแล้ว
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notify'])) {
    $queueId = intval($_POST['queue_id']);
    $queue = $conn->query("SELECT * FROM queues WHERE id = $queueId")->fetch_assoc();
    
    if ($queue) {
        $message = "คุณ ".$queue['name'] ." คิวที่ " . $queue['queue_number'] . " อาหารที่คุณสั่งเสร็จสิ้นแล้ว เชิญรับอาหารที่หน้าร้าน";
        sendLineNotify($message);
        $alertMessage = 'แจ้งเตือนส่งเรียบร้อย';
    } else {
        $alertMessage = 'ไม่พบคิวนี้';
    }
}

// ลบคิวที่เสร็จสิ้น
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_queue_id'])) {
    $queueId = intval($_POST['complete_queue_id']);
    
    // ลบคิวจากฐานข้อมูล
    $stmt = $conn->prepare("DELETE FROM queues WHERE id = ?");
    $stmt->bind_param("i", $queueId);
    if ($stmt->execute()) {
        $alertMessage = 'คิวถูกลบออกสำเร็จ';
    } else {
        $alertMessage = 'เกิดข้อผิดพลาดในการลบคิว';
    }
    $stmt->close();
}

// ดึงคิวทั้งหมด
$allQueues = $conn->query("SELECT * FROM queues ORDER BY queue_number ASC")->fetch_all(MYSQLI_ASSOC);
$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการคิว (Admin)</title>
    <script>
        function showAlert(message) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.style.display = 'block';
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 2000);
        }
    </script>
    <style>
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

        #alertBox {
            display: none;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        ul {
            list-style-type: none;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        li {
            background-color: #fff;
            padding: 15px;
            margin: 8px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .form-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
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

        /* Flexbox สำหรับการจัดตำแหน่งปุ่ม */
        .button-group {
            display: flex;
            justify-content: space-between;
        }

        .button-group form {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ระบบจัดการคิว (Admin)</h1>

        <div id="alertBox"></div>

        <h2>รายการคิวทั้งหมด</h2>
        <ul>
            <?php foreach ($allQueues as $queue): ?>
                <li>
                    <div class="queue-details">
                        คิวที่ <?= htmlspecialchars($queue['queue_number']) ?>: <?= htmlspecialchars($queue['name']) ?>
                        <br> ไส้กรอกเปรี้ยว: <?= $queue['sour_count'] ?>
                        <br> ไส้กรอกไม่เปรี้ยว: <?= $queue['non_sour_count'] ?>
                        <br> ยำแหนม: <?= $queue['nam_count'] ?>
                        <br> ขนาด: <?= $queue['size'] ?>
                        <br> สินค้าเพิ่มเติม: <?= ($queue['foodmoreTotal']/ 30) ?> ชิ้น
                        <br> รายละเอียด: <?= $queue['details'] ?>
                        <br> ราคาทั้งหมด: <?= ($queue['total_price'] + $queue['foodmoreTotal'])?> บาท
                    </div>
                    <br>
                    <div class="button-group">
                        <!-- ถ้าคิวมีสถานะ 'complete' จะไม่ให้กดปุ่ม -->
                        <?php if ($queue['status'] != 'complete'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="queue_id" value="<?= $queue['id'] ?>">
                                <button type="submit" name="cooking_notify">แจ้งว่ากำลังทำอาหาร</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="queue_id" value="<?= $queue['id'] ?>">
                                <button type="submit" name="notify">แจ้งเตือน (อาหารเสร็จแล้ว)</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="complete_queue_id" value="<?= htmlspecialchars($queue['id']) ?>">
                                <button type="submit">เสร็จสิ้นและลบคิว</button>
                            </form>
                        <?php else: ?>
                            <button disabled>คิวนี้เสร็จสิ้นแล้ว</button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="admin.php" class="back-link">กลับไปหน้า Dashboard</a>

        <?php if (isset($alertMessage)): ?>
            <script>
                showAlert(<?= json_encode($alertMessage) ?>);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>