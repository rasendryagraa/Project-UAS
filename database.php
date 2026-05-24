<?php
require_once 'koneksi.php';

class DatabaseSetup {
    private $dbGateway;
    private $pdo;

    public function __construct(Database $databaseInstance) {
        $this->dbGateway = $databaseInstance;
    }

    public function build() {
        echo "<div style='font-family: monospace; background: #222; color: #fff; padding: 20px; border-radius: 5px;'>";
        echo "=== INITIALIZING OOP DATABASE MIGRATION ===<br><br>";
        
        try {
            $this->initDatabase();
            $this->initTables();
            $this->initSeeds();
            
            echo "<br><span style='color: #2ecc71; font-weight: bold;'>[SUCCESS] Database dan data awal objek berhasil dibangun!</span>";
        } catch (PDOException $e) {
            echo "<br><span style='color: #e74c3c; font-weight: bold;'>[ERROR] Proses migrasi gagal: " . $e->getMessage() . "</span>";
        }
        
        echo "</div>";
    }

    private function initDatabase() {
        $serverConn = $this->dbGateway->getServerConnection();
        $serverConn->exec("CREATE DATABASE IF NOT EXISTS showcase_ekokraf");
        echo "[+] Object Database 'showcase_ekokraf' terwujud.<br>";
        
        // Jembatani properti internal dengan koneksi DB utama yang baru saja dibuat
        $this->pdo = $this->dbGateway->getConnection();
    }

    private function initTables() {
        $tables = [
            "users" => "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') NOT NULL
            )",
            "produk" => "CREATE TABLE IF NOT EXISTS produk (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama_produk VARCHAR(100) NOT NULL,
                harga INT NOT NULL,
                foto VARCHAR(255) NOT NULL
            )",
            "pesanan" => "CREATE TABLE IF NOT EXISTS pesanan (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                produk_id INT,
                ukuran ENUM('S', 'M', 'L', 'XL') NOT NULL,
                status ENUM('Belum Lunas', 'Lunas') DEFAULT 'Belum Lunas',
                tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
            )"
        ];

        foreach ($tables as $name => $sql) {
            $this->pdo->exec($sql);
            echo "[+] Struktur tabel '$name' siap digunakan.<br>";
        }
    }

    private function initSeeds() {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users;");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $passwordTeksAdmin = "admin123"; 
        $passwordTeksUser  = "dudungganteng"; 

        $hashAdmin = password_hash($passwordTeksAdmin, PASSWORD_DEFAULT);
        $hashUser  = password_hash($passwordTeksUser, PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role) VALUES (:user, :pass, :role)");
        
        $stmt->execute([':user' => 'admin123', ':pass' => $hashAdmin, ':role' => 'admin']);

        $stmt->execute([':user' => 'dudung', ':pass' => $hashUser, ':role' => 'user']);
        
        echo "[->] Seed data akun pengguna dengan password berbeda berhasil disuntikkan.<br>";

        $productCount = $this->pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
        if ($productCount == 0) {
            $stmtProd = $this->pdo->prepare("INSERT INTO produk (nama_produk, harga, foto) VALUES (:nama, :harga, :foto)");
            
            $stmtProd->execute([':nama' => 'PDH HMIT 2026', ':harga' => 150000, ':foto' => 'pdh.jpg']);
            $stmtProd->execute([':nama' => 'Gantungan Kunci Ekokraf', ':harga' => 15000, ':foto' => 'ganci.jpg']);
            $stmtProd->execute([':nama' => 'Sticker Pack HMIT', ':harga' => 10000, ':foto' => 'sticker.jpg']);
            echo "[->] Seed data katalog produk berhasil disuntikkan.<br>";
        }
    }
}

$dbCore = new Database();
$migration = new DatabaseSetup($dbCore);

$migration->build();
?>