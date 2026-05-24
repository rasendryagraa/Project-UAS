<?php

class Database {
    private $host = "localhost";
    private $db_name = "showcase_ekokraf";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            if ($exception->getCode() == 1049) {
                return "DB_NOT_FOUND";
            }
            echo "Koneksi database error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function getServerConnection() {
        try {
            $server_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $server_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $server_conn;
        } catch(PDOException $e) {
            die("Koneksi ke server MySQL gagal: " . $e->getMessage());
        }
    }
}
