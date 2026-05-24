<?php
require_once 'Class.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db_conn = $database->getConnection();
$customer = new CustomerUser($db_conn);

if (isset($_POST['pesan'])) {
    // Perbaikan: menambahkan tanda '$' pada variabel customer
    $customer->buatPesanan($_SESSION['user_id'], $_POST['produk_id'], $_POST['ukuran']);
    echo "<script>alert('Pesanan berhasil dibuat!'); window.location='user.php';</script>";
}

$katalog = $customer->tampilkanKatalog();
$riwayat = $customer->lihatRiwayatPesanan($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>User | Showcase Ekokraf HMIT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .catalog-grid { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .product-card { background: white; border: 1px solid #eee; padding: 15px; width: 200px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .product-card img { max-width: 100%; height: 130px; object-fit: cover; border-radius: 4px; background: #eee; }
        .form-section, .history-section { background: white; padding: 20px; border-radius: 8px; margin-top: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-belum { background: #ffeaa7; color: #d63031; }
        .badge-lunas { background: #55efc4; color: #00b894; }
        select, button { padding: 8px 12px; margin-right: 10px; }
        .btn-order { background: #2ecc71; border: none; color: white; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="LOGO_EKOKRAF.jpg" alt="Logo Ekokraf" style="height: 50px; width: auto;">
            <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
        </div>
        <a href="logout.php" style="color: red; text-decoration: none; font-weight: bold;">Logout</a>
    </div>

    <h3>Katalog Merchandise</h3>
    <div class="catalog-grid">
        <?php foreach($katalog as $row): ?>
            <div class="product-card">
                <img src="images/<?= htmlspecialchars($row['foto']); ?>" alt="<?= htmlspecialchars($row['nama_produk']); ?>" style="max-width: 100%; height: 130px; object-fit: cover;">
                <h4><?= htmlspecialchars($row['nama_produk']); ?></h4>
                <p style="color: #e74c3c; font-weight: bold;">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-section">
        <h3>Form Pre-Order</h3>
        <form action="" method="POST">
            <label>Pilih Merchandise:</label>
            <select name="produk_id" required>
                <?php foreach($katalog as $row): ?>
                    <option value="<?= $row['id']; ?>"><?= $row['nama_produk']; ?> - Rp <?= number_format($row['harga'], 0, ',', '.'); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Ukuran:</label>
            <select name="ukuran" required>
                <option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option>
            </select>

            <button type="submit" name="pesan" class="btn-order">Pesan Sekarang</button>
        </form>
    </div>

    <div class="history-section">
        <h3>Riwayat Pre-Order Anda</h3>
        <blockquote>
            <strong>Instruksi Pembayaran:</strong> Silakan transfer total biaya sesuai pesanan Anda ke <strong>Bank Mandiri: 140-00-1234567-8 a.n Ekokraf HMIT</strong>.
        </blockquote>
        <table>
            <thead>
                <tr><th>ID Pesanan</th><th>Nama Barang</th><th>Ukuran</th><th>Total Biaya</th><th>Status Kelunasan</th></tr>
            </thead>
            <tbody>
                <?php if(count($riwayat) == 0): ?>
                    <tr><td colspan="5" style="text-align:center;">Anda belum memiliki riwayat pemesanan.</td></tr>
                <?php else: ?>
                    <?php foreach($riwayat as $row): ?>
                        <tr>
                            <td>#<?= $row['id']; ?></td>
                            <td><?= $row['nama_produk']; ?></td>
                            <td><?= $row['ukuran']; ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?= $row['status'] == 'Lunas' ? 'badge-lunas' : 'badge-belum'; ?>">
                                    <?= $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>