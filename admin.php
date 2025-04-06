<?php
// ตัวอย่างการเชื่อมต่อฐานข้อมูล
include 'database.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
    padding: 10px; /* เพิ่ม padding เพื่อไม่ให้เนื้อหาติดขอบเกินไป */
}

.container {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 30px;
    width: 100%;
    max-width: 800px;
    box-sizing: border-box;
}

h1, h2 {
    color: #333;
}

h2 {
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
    word-wrap: break-word; /* ให้ข้อความยาวๆ สามารถตัดบรรทัด */
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

.button-container {
    text-align: center;
    margin-top: 20px;
}

button, a button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    margin: 5px;
    text-decoration: none;
}

button:hover, a button:hover {
    background-color: #45a049;
}

a {
    text-decoration: none;
}

/* Media Queries */
@media (max-width: 768px) {
    .container {
        padding: 20px;
        width: 100%;
    }

    h1 {
        font-size: 20px;
    }

    h2 {
        font-size: 18px;
    }

    li {
        font-size: 14px;
    }

    button {
        padding: 12px 15px;
        font-size: 14px;
    }
}

@media (min-width: 769px) {
    .container {
        width: 80%;
    }

    h1 {
        font-size: 24px;
    }

    h2 {
        font-size: 20px;
    }

    button {
        padding: 10px 20px;
        font-size: 16px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>ระบบจัดการหลังบ้านร้านยำแหนมดาวคะนอง</h1>
        
        <h2>ระบบจัดการ</h2>
        
        <div class="button-container">
            <!-- ปุ่มไปที่หน้าแจ้งเตือน -->
            <a href="notify.php">
                <button>ระบบจัดการคิว</button>
            </a>

            <!-- ปุ่มไปที่หน้ายอดขาย -->
            <a href="sales.php">
                <button>ยอดขาย</button>
            </a>

            <!-- ปุ่มไปที่หน้าเพิ่มสินค้า -->
            <a href="add_product.php">
                <button>เพิ่มสินค้า</button>
            </a>
        </div>
    </div>
</body>
</html>
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
                            <br> เบอร์โทร: <?= htmlspecialchars($queue['phone_number']) ?>
                            <br> ไส้กรอกเปรี้ยว: <?= $queue['sour_count'] ?>
                            <br> ไส้กรอกไม่เปรี้ยว: <?= $queue['non_sour_count'] ?>
                            <br> ยำแหนม: <?= $queue['nam_count'] ?>
                            <br> ขนาด: <?= $queue['size'] ?>
                            <br> ราคาทั้งหมด: <?= $queue['total_price'] ?>
                        
                            
                            
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ไม่มีคิวที่เสร็จสิ้นแล้ว</p>
            <?php endif; ?>
        </ul>

       
    </div>
</body>
</html>
