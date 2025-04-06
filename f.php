<?php
session_start();

// ตั้งค่า LINE Login API
$client_id = '2006854599';
$client_secret = '14e2684966151fb09461f2f7e941781f';
$redirect_uri = 'http://queue-yamnhame.great-site.net/f.php';

// ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปที่ LINE Login
if (!isset($_GET['code'])) {
    $auth_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&scope=profile%20openid%20email&state=12345";
    header("Location: $auth_url");
    exit();
}

// รับค่าโค้ดจาก LINE
$code = $_GET['code'];

// ขอ access token
$token_url = "https://api.line.me/oauth2/v2.1/token";
$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'client_id' => $client_id,
    'client_secret' => $client_secret
];
$options = [
    'http' => [
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data),
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$result = json_decode($response, true);

if (!isset($result['access_token'])) {
    echo "Error: Cannot get access token";
    exit();
}

$access_token = $result['access_token'];

// รับข้อมูลโปรไฟล์ผู้ใช้
$user_info_url = "https://api.line.me/v2/profile";
$options = [
    'http' => [
        'header' => "Authorization: Bearer $access_token\r\n",
        'method' => 'GET',
    ]
];
$context = stream_context_create($options);
$user_info = file_get_contents($user_info_url, false, $context);
$user = json_decode($user_info, true);

// เก็บข้อมูลผู้ใช้ใน session
$_SESSION['user'] = $user;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยินดีต้อนรับเข้าสู่ระบบ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            padding: 50px;
        }

        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
        }

        img {
            border-radius: 50%;
            margin-top: 20px;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: inline-block;
            text-align: center;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 4px;
        }

        a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>ยินดีต้อนรับ, <?= htmlspecialchars($user['displayName']) ?></h1>
        <img src="<?= htmlspecialchars($user['pictureUrl']) ?>" alt="Profile Picture" width="150">
        <p>คุณได้เข้าสู่ระบบสำเร็จ</p>
        <a href="login.php">เริ่มสั่ง</a>
    </div>

</body>
</html>
