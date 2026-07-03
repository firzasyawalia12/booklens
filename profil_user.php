<?php
// 1. Memulai session untuk mengecek status login user (Sama seperti home.php)
session_start();
include 'koneksi.php';

// Proteksi halaman: Jika belum login, kembalikan ke login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Ambil data user yang sedang login secara dinamis berdasarkan session username
$username_session = $_SESSION['username'];
$query_user = "SELECT * FROM users WHERE username = '$username_session'";
$result_user = mysqli_query($koneksi, $query_user);

if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    // Jika kolom nama lengkap di database kosong, gunakan username sebagai backup
    $nama_user = !empty($user['nama']) ? $user['nama'] : $user['username'];
    $email_user = !empty($user['email']) ? $user['email'] : $user['username'] . "@example.com";
    $bio_user = !empty($user['bio']) ? $user['bio'] : "Seorang pencinta buku fiksi dan misteri yang senang membagikan pandangan serta ulasan jujur setelah membaca.";
    $join_user = !empty($user['join_date']) ? $user['join_date'] : "Januari 2026";
} else {
    // Data cadangan jika struktur tabel belum lengkap
    $nama_user = $_SESSION['username'];
    $email_user = $_SESSION['username'] . "@example.com";
    $bio_user = "Seorang pencinta buku fiksi dan misteri yang senang membagikan pandangan serta ulasan jujur setelah membaca.";
    $join_user = "Januari 2026";
}

// 3. Hitung TOTAL REVIEWS secara dinamis dari tabel 'reviews'
$query_reviews_count = "SELECT COUNT(*) as total FROM reviews";
$result_count = mysqli_query($koneksi, $query_reviews_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_reviews = $row_count['total'];

// 4. Hitung TOTAL WISHLIST secara dinamis dari Session Wishlist
$total_wishlist = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <title>My Profile - BookLens</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        /* Mengambil base styling dari aplikasi Anda */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* NAVBAR STYLING (Sama Persis dengan home.php) */
        .nav-right-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-search-wrapper {
            position: relative;
            background-color: #f1f5f9;
            border-radius: 20px;
            padding: 6px 12px 6px 32px;
            display: flex;
            align-items: center;
        }
        .nav-search-wrapper .nav-search-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 13px;
        }
        .nav-search-wrapper input {
            border: none;
            background: none;
            outline: none;
            font-size: 12px;
            width: 160px;
            font-family: 'Poppins', sans-serif;
        }
        .logo-brand .brand-link { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            text-decoration: none; 
            color: #0f172a; 
            font-size: 1.4rem; 
            font-weight: 700; 
        }
        .logo-brand .brand-link img { width: 28px; height: 28px; object-fit: contain; }
        
        .nav-links { display: flex; gap: 24px; margin-left: 48px; margin-right: auto; }
        .nav-links a { text-decoration: none; color: #475569; font-weight: 500; font-size: 0.95rem; }
        .nav-links a:hover { color: #0f172a; }
        .nav-links a.active { color: #0f172a; font-weight: 700; }

        /* MAIN CONTAINER PROFILE */
        main.container {
            flex: 1 0 auto;
            margin-top: 40px;
            padding-bottom: 60px;
            max-width: 1140px;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 30px;
        }

        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        /* Avatar Lingkaran Besar UI */
        .profile-avatar-large {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            font-size: 2.5rem;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .profile-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .profile-email {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .profile-join {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 400;
        }

        .stat-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            margin: 0;
        }

        /* Tombol Hitam Elegan (Tema BookLens) */
        .btn-edit-profile {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit-profile:hover {
            background-color: #1e293b;
            color: #ffffff;
        }

        /* Tombol Logout Merah */
        .btn-logout-custom {
            background-color: #fee2e2;
            color: #ef4444;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-logout-custom:hover {
            background-color: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        /* FOOTER (Sama Persis dengan home.php) */
        .explore-footer {
            background-color: #cbd5e1;
            padding: 25px 0;
            font-size: 0.85rem;
            color: #475569;
            margin-top: auto;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .explore-footer a {
            color: #475569;
            text-decoration: none;
            margin-left: 20px;
        }
        .explore-footer a:hover {
            color: #0f172a;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light d-flex justify-content-between align-items-center">
        <div class="logo-brand ms-4">
            <a href="home.php" style="text-decoration: none; color: #000000; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo" style="width: 24px; height: 24px;" onerror="this.style.display='none'">
                <span>BookLens</span>
            </a>
        </div>
        <div class="nav-links" style="display: flex; gap: 28px;">
            <a href="home.php" style="text-decoration: none; font-weight: 600;">Home</a>
            <a href="books_user.php" class="nav-item-normal" style="text-decoration: none;">Books</a>
            <a href="wishlist.php" class="nav-item-normal" style="text-decoration: none;">My wishlist</a>
            <a href="reviews.php" class="nav-item-normal" style="text-decoration: none;">My Review</a>
        </div>
        <div class="nav-right-container me-4" style="display: flex; align-items: center; gap: 20px;">
            <div class="nav-search-wrapper" style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
                <input type="text" placeholder="Search for titles, author..." style="padding: 6px 12px 6px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 0.8rem; outline: none; width: 210px; background-color: #ffffff; font-family: 'Poppins', sans-serif;">
            </div>
            
            <<a href="profil_user.php" title="My Profile" style="text-decoration: none;">
    <div class="profile-avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #1e293b;">
        <i class="fa-regular fa-user"></i>
    </div>
</a>
        </div>
    </nav>


    <main class="container">
        <h1 class="page-title">My Profile</h1>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-custom">
                    <div class="d-flex align-items-center gap-4 flex-column flex-sm-row text-center text-sm-start">
                        <div class="profile-avatar-large">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <div>
                            <div class="profile-name"><?php echo htmlspecialchars($nama_user); ?></div>
                            <div class="profile-email"><?php echo htmlspecialchars($email_user); ?></div>
                            <div class="profile-join">
                                <i class="fa-regular fa-calendar-days me-1"></i> Anggota Sejak <?php echo htmlspecialchars($join_user); ?>
                            </div>
                        </div>
                        <div class="ms-sm-auto mt-3 mt-sm-0">
                            <a href="edit_profile.php" class="btn-edit-profile">
                                <i class="fa-regular fa-pen-to-square"></i> Edit Profile
                            </a>
                        </div>
                    </div>

                    <hr style="border-color: #e2e8f0; margin-top: 30px; margin-bottom: 20px;">

                    <h5 style="font-size: 1rem; font-weight: 600; color: #0f172a; margin-bottom: 8px;">Biography</h5>
                    <p style="font-size: 0.9rem; color: #475569; line-height: 1.6; text-align: justify; margin: 0;">
                        <?php echo nl2br(htmlspecialchars($bio_user)); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-custom d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 style="font-size: 1.1rem; font-weight: 600; color: #0f172a; margin-bottom: 20px;">Aktivitas Membaca</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                                    <p class="stat-label">Reviews</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_wishlist; ?></div>
                                    <p class="stat-label">Wishlist</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <a href="reviews.php" style="text-decoration: none; font-size: 0.85rem; color: #475569; display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                <span><i class="fa-regular fa-comment-dots me-2"></i> Lihat Semua Review Anda</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                            </a>
                            <a href="wishlist.php" style="text-decoration: none; font-size: 0.85rem; color: #475569; display: flex; align-items: center; justify-content: space-between; padding: 12px 0;">
                                <span><i class="fa-regular fa-heart me-2"></i> Buka Daftar Wishlist</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <a href="logout.php" class="btn-logout-custom" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?');">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar Akun (Logout)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="explore-footer">
        <div class="container footer-content">
            <div class="footer-left">
                <strong>BookLens</strong>
                <p class="m-0" style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">&copy; 2026 BookLens. All rights reserved.</p>
            </div>
            <div class="footer-right">
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>