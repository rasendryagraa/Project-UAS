<?php
require_once 'koneksi.php';

class User {
    protected $id;
    protected $username;
    private $password; 
    protected $role;
    protected $db; 

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            $this->id = $result['id'];
            $this->username = $result['username'];
            $this->role = $result['role'];
            return $result;
        }
        return false;
    }
}

class AdminUser extends User {
    
    public function getStatistikUang() {
        $query = "SELECT SUM(p.harga) AS total FROM pesanan ps JOIN produk p ON ps.produk_id = p.id";
        $stmt = $this->db->query($query);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }

    public function getStatistikUkuran() {
        $query = "SELECT ukuran, COUNT(*) as jumlah FROM pesanan GROUP BY ukuran";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // CRUD Produk
    public function tambahProduk($nama, $harga, $foto) {
        $stmt = $this->db->prepare("INSERT INTO produk (nama_produk, harga, foto) VALUES (:nama, :harga, :foto)");
        return $stmt->execute([':nama' => $nama, ':harga' => $harga, ':foto' => $foto]);
    }

    public function tampilkanProduk() {
        return $this->db->query("SELECT * FROM produk")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hapusProduk($id) {
        $stmt = $this->db->prepare("DELETE FROM produk WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Kelola Pesanan Masuk
    public function tampilkanSemuaPesanan() {
        $query = "SELECT ps.id, u.username, p.nama_produk, ps.ukuran, p.harga, ps.status 
                  FROM pesanan ps 
                  JOIN users u ON ps.user_id = u.id 
                  JOIN produk p ON ps.produk_id = p.id";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function konfirmasiLunas($pesanan_id) {
        $stmt = $this->db->prepare("UPDATE pesanan SET status = 'Lunas' WHERE id = :id");
        return $stmt->execute([':id' => $pesanan_id]);
    }
}

class CustomerUser extends User {
    
    public function tampilkanKatalog() {
        return $this->db->query("SELECT * FROM produk")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buatPesanan($user_id, $produk_id, $ukuran) {
        $stmt = $this->db->prepare("INSERT INTO pesanan (user_id, produk_id, ukuran) VALUES (:user_id, :produk_id, :ukuran)");
        return $stmt->execute([':user_id' => $user_id, ':produk_id' => $produk_id, ':ukuran' => $ukuran]);
    }

    public function lihatRiwayatPesanan($user_id) {
        $query = "SELECT ps.id, p.nama_produk, ps.ukuran, ps.status, p.harga 
                  FROM pesanan ps 
                  JOIN produk p ON ps.produk_id = p.id 
                  WHERE ps.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>