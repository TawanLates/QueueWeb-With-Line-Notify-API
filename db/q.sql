CREATE TABLE queues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    queue_number INT NOT NULL,
    status ENUM('waiting', 'served') DEFAULT 'waiting',
    sour_count INT DEFAULT 0,
    non_sour_count INT DEFAULT 0,
    nam_count INT DEFAULT 0,
    size ENUM('ธรรมดา', 'พิเศษ'), -- ไม่ตั้งค่า DEFAULT
    details TEXT,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    queue_number INT NOT NULL,
    status ENUM('waiting', 'served') DEFAULT 'waiting',
    sour_count INT DEFAULT 0,
    non_sour_count INT DEFAULT 0,
    nam_count INT DEFAULT 0,
    size ENUM('ธรรมดา', 'พิเศษ'), -- ไม่ตั้งค่า DEFAULT
    details TEXT,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
