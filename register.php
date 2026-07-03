<?php
include 'koneksi.php';

$pesan_sukses = "";
$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama             = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username         = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email            = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $pesan_error = "Konfirmasi password tidak cocok!";
    } else {
        $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' OR username='$username'");
        
        if (mysqli_num_rows($cek_user) > 0) {
            $pesan_error = "Username atau Email sudah terdaftar!";
        } else {
            $password_aman = password_hash($password, PASSWORD_BCRYPT);
            $query_simpan = "INSERT INTO users (id_role, nama, username, email, password, foto, dibuat_pada) VALUES
(2, '$nama', '$username', '$email', '$password_aman', NULL, NOW())";
          
            if (mysqli_query($koneksi, $query_simpan)) {
                $pesan_sukses = "Akun berhasil dibuat! Mengalihkan ke halaman login...";
                // Pengalihan lewat server-side PHP (2 detik)
                header("refresh:2;url=login.php");
            } else {
                $pesan_error = "Gagal mendaftar: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLens - Register</title>
    
    <?php if ($pesan_sukses): ?>
        <meta http-equiv="refresh" content="2;url=login.php">
    <?php endif; ?>

    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="split-container">
        <div class="left-side">
            <div class="quote-box">
                <img src="assets/images/ui/Vector.png" alt="Book Icon" class="quote-icon">
                <p class="quote-text" style="color: #ffffff !important;">“Books may be finished, but the stories they leave behind stay with us forever.”</p>
            </div>
        </div>

        <div class="right-side">
            <div class="form-wrapper">
                <div class="brand-title">
                    <h1>BookLens</h1>
                    <p>Review, Rate, and Share Your Favorite Books!</p>
                </div>

                <?php if ($pesan_error): ?>
                    <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
                <?php endif; ?>
                
                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="nama">Full Name</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="nama" name="nama" placeholder="Nama Lengkap" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-circle-user"></i>
                            <input type="text" id="username" name="username" placeholder="Username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="terms-checkbox">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>

                    <button type="submit" class="btn-primary">Register</button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>