<?php  
include 'database.php';

// รับวันที่จากพารามิเตอร์ URL
$saleDate = isset($_GET['sale_date']) ? $_GET['sale_date'] : '';

// คำสั่ง SQL สำหรับดึงรายละเอียดออเดอร์ในวันที่กำหนด
$query = "SELECT * FROM history WHERE DATE(created_at) = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $saleDate);
$stmt->execute();
$orderDetails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดออเดอร์ - <?= $saleDate ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 10px;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 100%;
            width: 95%;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .table-wrapper {
            overflow-x: auto; /* ทำให้เลื่อนซ้ายขวาได้ */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* ป้องกันตารางบีบเกินไป */
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 14px; /* ลดขนาดให้พอดีมือถือ */
        }

        th {
            background-color: #f4f4f4;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            th, td {
                font-size: 13px; /* ปรับให้เล็กลงในจอเล็ก */
                padding: 8px;
            }

            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>รายละเอียดออเดอร์สำหรับวันที่ <?= date('d/m/Y', strtotime($saleDate)) ?></h1>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>คิวที่</th>
                        <th>ชื่อ</th>
                        <th>หมายเลขโทรศัพท์</th>
                
                        <th>ยอดรวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orderDetails) > 0): ?>
                        <?php foreach ($orderDetails as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['queue_number']) ?></td>
                                <td><?= htmlspecialchars($order['name']) ?></td>
                                <td><?= htmlspecialchars($order['phone_number']) ?></td>
                          
                                <td><?= number_format($order['total_price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">ไม่มีข้อมูลออเดอร์ในวันที่นี้</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="sales.php" class="back-link">กลับไปยังยอดขายรวมรายวัน</a>
    </div>
</body>
</html>
