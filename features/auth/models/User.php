<?php
require_once __DIR__ . '/../../../shared/helpers/db.php';

class User {
    protected $db;
    public function __construct() {
        $this->db = get_db();
    }

    public function create($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (username,email,password_hash) VALUES (:u,:e,:p)');
        $stmt->execute(['u'=>$username,'e'=>$email,'p'=>$hash]);
        return $this->db->lastInsertId();
    }

    public function findByUsernameOrEmail($username, $email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :x OR email = :y LIMIT 1');
        $stmt->execute(['x'=>$username,'y'=>$email]);
        return $stmt->fetch();
    }
}
