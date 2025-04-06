<?php
session_start();
$user = $_SESSION['user'];
include 'database.php';

$waitingQueues = [];
$totalWaiting = 0;
$queueNumber = 0;
$hasCheckedQueue = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'cancelQueue') {
        $queueId = $_POST['queue_id'];

        $queueData = $conn->query("SELECT * FROM queues WHERE id = $queueId")->fetch_assoc();

        if ($queueData) {
            $conn->query("DELETE FROM queues WHERE id = $queueId");
            header("Location: check_queue.php");
            exit;
        } else {
            echo "<script>alert('ไม่พบคิวที่ต้องการยกเลิก');</script>";
        }
    }

    $phoneNumber = $_POST['phone_number'];
    $waitingQueues = $conn->query("SELECT * FROM queues WHERE phone_number = '$phoneNumber' ORDER BY queue_number ASC")->fetch_all(MYSQLI_ASSOC);

    if (!empty($waitingQueues)) {
        $queueNumber = $waitingQueues[0]['queue_number'];
    }

    $hasCheckedQueue = true;
}

$totalWaiting = $conn->query("SELECT COUNT(*) as total FROM queues WHERE status = 'waiting'")->fetch_assoc()['total'];
$remainingQueues = $queueNumber > 0 ? $conn->query("SELECT COUNT(*) as remaining FROM queues WHERE queue_number < $queueNumber")->fetch_assoc()['remaining'] : 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบคิว</title>
    <link rel="stylesheet" href="checkq.css">
</head>
<body>

<div class="container">
    <h1>ตรวจสอบคิว</h1>
    <p>ยินดีต้อนรับ <strong><?= htmlspecialchars($user['displayName']); ?></strong></p>

    <form method="POST">
        <label for="phone_number">กรอกเบอร์โทรศัพท์ของคุณ:</label><br>
        <input type="text" name="phone_number" id="phone_number" required 
        pattern="\d{10}" maxlength="10" inputmode="numeric" 
        oninput="this.value = this.value.replace(/\D/, '')">
        <br>
        <button type="submit">ตรวจสอบคิว</button>
    </form>

    <?php if ($hasCheckedQueue): ?>
        <?php if (count($waitingQueues) > 0): ?>
            <h2>คิวของคุณ</h2>
            <ul>
                <?php foreach ($waitingQueues as $queue): ?>
                    <li>
                        คิวที่ <span class="queue-status"><?= $queue['queue_number'] ?></span>: 
                        <?= htmlspecialchars($queue['name']) ?> 
                        (สถานะ: <strong><?= htmlspecialchars($queue['status']) ?></strong>)
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="cancelQueue">
                            <input type="hidden" name="queue_id" value="<?= $queue['id'] ?>">
                            <button type="submit" class="cancel">ยกเลิกคิว</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3 class="waiting-count">คุณต้องรออีก <?= $remainingQueues ?> คิว</h3>
        <?php else: ?>
            <p class="queue-status">ไม่พบคิวสำหรับเบอร์นี้</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="login.php">กลับไปที่หน้าเข้าสู่ระบบ</a>
</div>

</body>
</html>
