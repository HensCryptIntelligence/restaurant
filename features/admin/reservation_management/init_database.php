<?php
require_once 'config.php';

// ==========================================
// AUTO CREATE DATABASE & TABLES
// ==========================================
try {
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS restaurant");
    $pdo->exec("USE restaurant");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id_user INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create reservation_rooms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservation_rooms (
        id_reservation_room VARCHAR(10) PRIMARY KEY,
        seats INT NOT NULL,
        price_place DECIMAL(10,2) DEFAULT 50000,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create reservation table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservation (
        id_reservation INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        id_reservation_room VARCHAR(10) NOT NULL,
        seats INT NOT NULL,
        reservation_start DATETIME NOT NULL,
        reservation_time INT DEFAULT 120,
        reservation_date DATE NOT NULL,
        phone_number VARCHAR(20),
        email_address VARCHAR(255),
        status ENUM('pending', 'confirmed', 'seated', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
        FOREIGN KEY (id_reservation_room) REFERENCES reservation_rooms(id_reservation_room) ON DELETE CASCADE
    )");
    
    // Check if tables are empty, then insert dummy data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    if ($userCount == 0) {
        // Insert dummy users
        $pdo->exec("INSERT INTO users (fullname, password_hash, role) VALUES 
            ('John Doe', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('Jane Smith', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('Robert Johnson', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('Maria Garcia', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('David Lee', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('Sarah Wilson', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'customer'),
            ('Admin User', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 'admin')
        ");
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservation_rooms");
    $roomCount = $stmt->fetch()['count'];
    
    if ($roomCount == 0) {
        // Insert all tables from 3 floors
        $floor_data = [
            '1' => ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'],
            '2' => ['D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2'],
            '3' => ['G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2']
        ];
        
        foreach ($floor_data as $floor => $tables) {
            foreach ($tables as $table) {
                $seats = ($table === 'Bar') ? 1 : rand(2, 6);
                $pdo->exec("INSERT INTO reservation_rooms (id_reservation_room, seats, price_place) 
                           VALUES ('$table', $seats, 50000)");
            }
        }
    }
    
    // Verify rooms yang dibutuhkan
    $requiredRooms = ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2', 
                      'D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2',
                      'G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2'];
    
    foreach ($requiredRooms as $room) {
        $checkRoom = $pdo->prepare("SELECT COUNT(*) FROM reservation_rooms WHERE id_reservation_room = ?");
        $checkRoom->execute([$room]);
        if ($checkRoom->fetchColumn() == 0) {
            $seats = ($room === 'Bar') ? 1 : rand(2, 6);
            $pdo->exec("INSERT INTO reservation_rooms (id_reservation_room, seats, price_place) 
                       VALUES ('$room', $seats, 50000)");
        }
    }
    
    // Insert dummy reservations for TODAY
    $pdo->exec("DELETE FROM reservation WHERE email_address LIKE '%dummy%'");
    
    $today = date('Y-m-d');
    
    $dummyReservations = [
        // Floor 1
        ['user_id' => 1, 'table' => 'A1', 'hour' => 10, 'guests' => 2, 'status' => 'confirmed', 'name' => 'John Doe'],
        ['user_id' => 2, 'table' => 'A1', 'hour' => 14, 'guests' => 4, 'status' => 'pending', 'name' => 'Jane Smith'],
        ['user_id' => 3, 'table' => 'A2', 'hour' => 11, 'guests' => 3, 'status' => 'seated', 'name' => 'Mike Johnson'],
        ['user_id' => 4, 'table' => 'B1', 'hour' => 12, 'guests' => 3, 'status' => 'seated', 'name' => 'Tom Brown'],
        ['user_id' => 5, 'table' => 'B2', 'hour' => 18, 'guests' => 2, 'status' => 'confirmed', 'name' => 'Sarah Lee'],
        ['user_id' => 6, 'table' => 'B3', 'hour' => 19, 'guests' => 4, 'status' => 'pending', 'name' => 'David Kim'],
        ['user_id' => 1, 'table' => 'C1', 'hour' => 19, 'guests' => 4, 'status' => 'pending', 'name' => 'Lisa Wang'],
        ['user_id' => 2, 'table' => 'C2', 'hour' => 13, 'guests' => 2, 'status' => 'confirmed', 'name' => 'Chris Martin'],
        ['user_id' => 3, 'table' => 'Bar', 'hour' => 20, 'guests' => 1, 'status' => 'seated', 'name' => 'Alex Turner'],
        
        // Floor 2
        ['user_id' => 4, 'table' => 'D1', 'hour' => 11, 'guests' => 2, 'status' => 'confirmed', 'name' => 'Emma Davis'],
        ['user_id' => 5, 'table' => 'D2', 'hour' => 15, 'guests' => 3, 'status' => 'pending', 'name' => 'James Wilson'],
        ['user_id' => 6, 'table' => 'D3', 'hour' => 18, 'guests' => 4, 'status' => 'confirmed', 'name' => 'Olivia Moore'],
        ['user_id' => 1, 'table' => 'E1', 'hour' => 17, 'guests' => 3, 'status' => 'pending', 'name' => 'William Taylor'],
        ['user_id' => 2, 'table' => 'E2', 'hour' => 12, 'guests' => 2, 'status' => 'seated', 'name' => 'Sophia Anderson'],
        ['user_id' => 3, 'table' => 'E3', 'hour' => 20, 'guests' => 5, 'status' => 'confirmed', 'name' => 'Michael Thomas'],
        ['user_id' => 4, 'table' => 'F1', 'hour' => 14, 'guests' => 2, 'status' => 'pending', 'name' => 'Emily Jackson'],
        ['user_id' => 5, 'table' => 'F2', 'hour' => 21, 'guests' => 3, 'status' => 'confirmed', 'name' => 'Daniel White'],
        
        // Floor 3
        ['user_id' => 6, 'table' => 'G1', 'hour' => 13, 'guests' => 4, 'status' => 'confirmed', 'name' => 'Isabella Harris'],
        ['user_id' => 1, 'table' => 'G2', 'hour' => 16, 'guests' => 2, 'status' => 'seated', 'name' => 'Matthew Clark'],
        ['user_id' => 2, 'table' => 'G3', 'hour' => 19, 'guests' => 3, 'status' => 'pending', 'name' => 'Ava Lewis'],
        ['user_id' => 3, 'table' => 'H1', 'hour' => 15, 'guests' => 2, 'status' => 'seated', 'name' => 'Joshua Robinson'],
        ['user_id' => 4, 'table' => 'H2', 'hour' => 17, 'guests' => 4, 'status' => 'confirmed', 'name' => 'Mia Walker'],
        ['user_id' => 5, 'table' => 'H3', 'hour' => 20, 'guests' => 3, 'status' => 'pending', 'name' => 'Andrew Hall'],
        ['user_id' => 6, 'table' => 'I1', 'hour' => 12, 'guests' => 2, 'status' => 'confirmed', 'name' => 'Charlotte Allen'],
        ['user_id' => 1, 'table' => 'I2', 'hour' => 18, 'guests' => 4, 'status' => 'seated', 'name' => 'Ryan Young'],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO reservation (
            id_user, id_reservation_room, seats, 
            reservation_start, reservation_time, reservation_date,
            phone_number, email_address, status
        ) VALUES (
            :id_user, :table, :guests,
            :res_start, 120, :res_date,
            :phone, :email, :status
        )
    ");
    
    foreach ($dummyReservations as $res) {
        $reservationStart = $today . ' ' . str_pad($res['hour'], 2, '0', STR_PAD_LEFT) . ':00:00';
        
        // Update user name
        $updateUser = $pdo->prepare("UPDATE users SET fullname = :name WHERE id_user = :id");
        $updateUser->execute(['name' => $res['name'], 'id' => $res['user_id']]);
        
        // Insert reservation
        $stmt->execute([
            'id_user' => $res['user_id'],
            'table' => $res['table'],
            'guests' => $res['guests'],
            'res_start' => $reservationStart,
            'res_date' => $today,
            'phone' => '0812345678' . $res['user_id'],
            'email' => strtolower(str_replace(' ', '.', $res['name'])) . '@dummy.com',
            'status' => $res['status']
        ]);
    }
    
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>