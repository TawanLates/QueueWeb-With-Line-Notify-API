<?php  
include 'database.php';

// คำสั่ง SQL สำหรับดึงยอดขายรวมรายวันจากฐานข้อมูล
$query = "SELECT DATE(created_at) AS sale_date, SUM(total_price) AS daily_total
          FROM history
          GROUP BY DATE(created_at)
          ORDER BY sale_date DESC";
$salesData = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยอดขายรวมรายวัน</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        .total-sales {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
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
        <h1>ยอดขายรวมรายวัน</h1>

        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>ยอดขายรวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($salesData) > 0): ?>
                    <?php foreach ($salesData as $sale): ?>
                        <tr>
                            <!-- เพิ่มลิงก์ไปยังหน้า order_details.php โดยส่งพารามิเตอร์ sale_date -->
                            <td><a href="order_details.php?sale_date=<?= $sale['sale_date'] ?>"><?= date('d/m/Y', strtotime($sale['sale_date'])) ?></a></td>
                            <td><?= number_format($sale['daily_total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">ไม่มีข้อมูลยอดขาย</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- คำนวณยอดขายรวมทั้งหมด -->
        <?php
        $totalSales = array_sum(array_column($salesData, 'daily_total'));
        ?>
        <div class="total-sales">
            ยอดขายรวมทั้งหมด: <?= number_format($totalSales, 2) ?> บาท
        </div>

        <a href="admin.php" class="back-link">กลับไปหน้า Dashboard</a>
    </div>
</body>
</html>
