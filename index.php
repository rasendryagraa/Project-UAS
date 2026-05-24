<?php
require_once 'Class.php';
session_start();

$database = new Database();
$db_conn = $database->getConnection(); 

if ($db_conn === "DB_NOT_FOUND") {
    die("Aplikasi belum siap. Silakan jalankan file <a href='database.php'>database.php</a> terlebih dahulu untuk memasang database.");
}

$user_system = new User($db_conn); 
$error = '';

if (isset($_POST['login'])) {
    $login_success = $user_system->login($_POST['username'], $_POST['password']);
    if ($login_success) {
        $_SESSION['user_id'] = $login_success['id'];
        $_SESSION['username'] = $login_success['username'];
        $_SESSION['role'] = $login_success['role'];

        if ($_SESSION['role'] == 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: user.php');
        }
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login | Showcase Ekokraf HMIT</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #f7f7f7; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        .logo-placeholder { font-weight: bold; color: #2c3e50; font-size: 24px; margin-bottom: 5px; }
        .sub-logo { font-size: 12px; color: #7f8c8d; margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 92%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #3498db; border: none; color: white; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #2980b9; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-placeholder">
            <img src="LOGO_EKOKRAF.jpg" alt="Logo Ekokraf" style="max-width: 120px; height: auto; margin-bottom: 10px;">
        </div>
        <div class="sub-logo">Showcase Ekokraf HMIT</div>
        
        <?php if($error): ?> <p class="error"><?= $error; ?></p> <?php endif; ?>
        
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Masuk Aplikasi</button>
        </form>
    </div>
</body>
</html>
