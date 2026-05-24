<?php
require_once 'Class.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db_conn = $database->getConnection();
$admin = new AdminUser($db_conn);

if (isset($_POST['tambah_produk'])) {
    $admin->tambahProduk($_POST['nama_produk'], $_POST['harga'], $_POST['foto']);
    header('Location: admin.php');
}

if (isset($_GET['hapus_produk'])) {
    $admin->hapusProduk($_GET['hapus_produk']);
    header('Location: admin.php');
}

if (isset($_GET['konfirmasi_id'])) {
    $admin->konfirmasiLunas($_GET['konfirmasi_id']);
    header('Location: admin.php');
}

$total_uang = $admin->getStatistikUang();
$stat_ukuran = $admin->getStatistikUkuran();
$daftar_produk = $admin->tampilkanProduk();
$pesanan_masuk = $admin->tampilkanSemuaPesanan();

$count_ukuran = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0];
foreach($stat_ukuran as $s) {
    $count_ukuran[$s['ukuran']] = $s['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard | Showcase Ekokraf HMIT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #34495e; padding-bottom: 10px; }
        .stats-container { display: flex; gap: 20px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; }
        .panel { background: white; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #34495e; color: white; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 13px; color: white; display: inline-block;}
        .btn-green { background: #2ecc71; }
        .btn-red { background: #e74c3c; }
        .btn-blue { background: #3498db; border: none; padding: 8px 12px; cursor: pointer;}
        input[type="text"], input[type="number"] { padding: 6px; width: 200px; margin-right: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="LOGO_EKOKRAF.jpg" alt="Logo Ekokraf" style="height: 50px; width: auto;">
            <h2>Admin Dashboard Backend</h2>
        </div>
        <a href="logout.php" style="color: red; text-decoration: none; font-weight: bold;">Logout</a>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <h4>Total Uang Masuk (Omset PO)</h4>
            <h2 style="color: #2ecc71;">Rp <?= number_format($total_uang, 0, ',', '.'); ?></h2>
        </div>
        <div class="stat-card">
            <h4>Pesanan Per Ukuran</h4>
            <p>
                <strong>S:</strong> <?= $count_ukuran['S']; ?> | 
                <strong>M:</strong> <?= $count_ukuran['M']; ?> | 
                <strong>L:</strong> <?= $count_ukuran['L']; ?> | 
                <strong>XL:</strong> <?= $count_ukuran['XL']; ?>
            </p>
        </div>
    </div>

    <div class="panel">
        <h3>Kelola Produk Merchandise</h3>
        <form action="" method="POST" style="margin-bottom: 20px;">
            <input type="text" name="nama_produk" placeholder="Nama Merchandise" required>
            <input type="number" name="harga" placeholder="Harga (Rp)" required>
            <input type="text" name="foto" placeholder="Nama File Foto (cth: kaos.jpg)">
            <button type="submit" name="tambah_produk" class="btn btn-blue">Tambah Barang</button>
        </form>

        <table>
            <thead>
                <tr><th>ID</th><th>Nama Produk</th><th>Harga</th><th>File Foto</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach($daftar_produk as $prod): ?>
                <tr>
                    <td><?= $prod['id']; ?></td>
                    <td><?= $prod['nama_produk']; ?></td>
                    <td>Rp <?= number_format($prod['harga'], 0, ',', '.'); ?></td>
                    <td><?= $prod['foto']; ?></td>
                    <td>
                        <a href="admin.php?hapus_produk=<?= $prod['id']; ?>" class="btn btn-red" onclick="return confirm('Hapus produk ini?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h3>Daftar Pesanan Masuk (Validasi Pembayaran)</h3>
        <table>
            <thead>
                <tr><th>ID PO</th><th>Nama Pemesan</th><th>Produk</th><th>Ukuran</th><th>Nominal</th><th>Status</th><th>Aksi Konfirmasi</th></tr>
            </thead>
            <tbody>
                <?php if(count($pesanan_masuk) == 0): ?>
                    <tr><td colspan="7" style="text-align:center;">Belum ada pesanan masuk.</td></tr>
                <?php else: ?>
                    <?php foreach($pesanan_masuk as $pesan): ?>
                    <tr>
                        <td>#<?= $pesan['id']; ?></td>
                        <td><strong><?= htmlspecialchars($pesan['username']); ?></strong></td>
                        <td><?= $pesan['nama_produk']; ?></td>
                        <td><span style="background: #eee; padding:2px 6px; border-radius:3px;"><?= $pesan['ukuran']; ?></span></td>
                        <td>Rp <?= number_format($pesan['harga'], 0, ',', '.'); ?></td>
                        <td><?= $pesan['status']; ?></td>
                        <td>
                            <?php if($pesan['status'] == 'Belum Lunas'): ?>
                                <a href="admin.php?konfirmasi_id=<?= $pesan['id']; ?>" class="btn btn-green" onclick="return confirm('Konfirmasi pelunasan pemesan ini?')">Konfirmasi Lunas</a>
                            <?php else: ?>
                                <span style="color: #2ecc71; font-weight:bold;">✓ Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>