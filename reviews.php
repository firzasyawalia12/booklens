<?php
// 1. Memulai session untuk mempertahankan status data review
session_start();

// 2. Jika session review belum ada, isi dengan data simulasi awal yang persis seperti di gambar UI (Buku: Hujan dan Pukul Setengah Lima)
if (!isset($_SESSION['my_reviews'])) {
    $_SESSION['my_reviews'] = [
        [
            "id" => 1,
            "book_id" => 1,
            "title" => "Hujan",
            "author" => "Tere Liye",
            "cover" => "hujan.png",
            "rating" => 5,
            "date" => "24/05/2026",
            "content" => "Novel ini benar-benar menyentuh hati. Kisah tentang Lail dan Esok di tengah dunia yang berubah setelah bencana besar sangat mengharukan. Alur ceritanya mengalir dengan indah, mengajarkan tentang ketabahan, penerimaan, dan arti sejati dari melepaskan. Gaya penulisan Tere Liye selalu sukses membuat pembaca larut dalam emosi."
        ],
        [
            "id" => 2,
            "book_id" => 4,
            "title" => "Pukul Setengah Lima",
            "author" => "Rintik Sedu",
            "cover" => "pukul.png",
            "rating" => 4,
            "date" => "20/05/2026",
            "content" => "Ceritanya sangat unik dan penuh misteri tentang identitas. Konflik emosional yang dialami Alina/Marni terasa sangat rileks dengan pencarian jati diri remaja masa kini. Meskipun ada beberapa bagian yang alurnya terasa agak lambat, buku ini tetap memberikan sudut pandang yang mendalam tentang penerimaan diri."
        ]
    ];
}

// 3. Logika Aksi Delete Review jika user mengklik ikon sampah
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $delete_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    foreach ($_SESSION['my_reviews'] as $key => $review) {
        if ($review['id'] == $delete_id) {
            unset($_SESSION['my_reviews'][$key]);
            // Reset susunan indeks array agar rapi kembali
            $_SESSION['my_reviews'] = array_values($_SESSION['my_reviews']);
            break;
        }
    }
    // Refresh halaman agar bersih dari query string action
    header("Location: reviews.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - BookLens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    
    <style>
        /* Pengaturan Layout Dasar & Font Global */
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
        }

        /* Struktur Styling Navigasi */
        .navbar {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6;
            padding: 0.8rem 2rem;
        }

        .logo-brand a {
            font-family: 'Poppins', sans-serif;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .nav-links a {
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .nav-links a.nav-item-active {
            color: #000000 !important;
            font-weight: 600 !important;
        }

        .nav-links a.nav-item-normal {
            color: rgba(0, 0, 0, 0.55) !important;
            font-weight: 400 !important;
        }

        .nav-links a.nav-item-normal:hover {
            color: #000000 !important;
        }

        /* Kontainer Utama Konten */
        main.container {
            flex: 1 0 auto;
            margin-top: 40px;
            padding-bottom: 60px;
            max-width: 1140px;
        }

        /* Judul Halaman */
        .page-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 30px;
        }

        /* Review Container Card Component */
        .review-card-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 20px;
            display: flex;
            gap: 25px;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        /* Sisi Kiri Card: Sampul Buku */
        .review-book-cover {
            width: 105px;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            flex-shrink: 0;
        }

        /* Detail Informasi Buku */
        .review-book-info {
            display: flex;
            flex-direction: column;
            width: 190px;
            flex-shrink: 0;
        }

        .review-book-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .review-book-author {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 12px;
            font-weight: 400;
        }

        /* Bintang Rating */
        .stars-display {
            display: flex;
            gap: 4px;
            color: #f59e0b;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        
        .stars-display .star-empty {
            color: #e2e8f0;
        }

        .review-date-text {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 400;
        }

        /* Teks Deskripsi Review */
        .review-content-body {
            flex-grow: 1;
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.6;
            text-align: justify;
            padding-right: 45px;
            font-weight: 400;
        }

        /* Tombol Aksi Hapus Sampah */
        .btn-delete-review {
            position: absolute;
            top: 24px;
            right: 24px;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.15s ease;
            padding: 0;
        }

        .btn-delete-review:hover {
            color: #ef4444;
        }

        /* Struktur Footer */
        .explore-footer {
            background-color: #cbd5e1;
            padding: 25px 0;
            font-size: 0.85rem;
            color: #475569;
            flex-shrink: 0;
            width: 100%;
        }

        .explore-footer a {
            color: #475569;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.2s ease;
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


 <main class="container mt-5 pt-4">
    
    <h1 class="page-title">
    My Reviews 
    <span style="color: #64748b; font-weight: 500;">
        (<?php echo isset($_SESSION['my_reviews']) ? count($_SESSION['my_reviews']) : 0; ?>)
    </span>
    </h1>

    <div class="reviews-list-wrapper">
            <?php if (!empty($_SESSION['my_reviews'])): ?>
                <?php foreach ($_SESSION['my_reviews'] as $review): ?>
                    
                    <div class="review-card-item">
                        <img src="assets/images/books/<?php echo $review['cover']; ?>" alt="<?php echo $review['title']; ?>" class="review-book-cover" onerror="this.src='https://via.placeholder.com/105x150?text=Book'">
                        
                        <div class="review-book-info">
                            <span class="review-book-title"><?php echo $review['title']; ?></span>
                            <span class="review-book-author">by <?php echo $review['author']; ?></span>
                            
                            <div class="stars-display">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fa-solid fa-star"></i>';
                                    } else {
                                        echo '<i class="fa-solid fa-star star-empty"></i>';
                                    }
                                }
                                ?>
                            </div>
                            
                            <span class="review-date-text"><?php echo $review['date']; ?></span>
                        </div>
                        
                        <div class="review-content-body">
                            <?php echo $review['content']; ?>
                        </div>

                        <button class="btn-delete-review" title="Delete Review" onclick="konfirmasiHapusReview(<?php echo $review['id']; ?>)">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #64748b; background: white; border-radius: 10px; border: 1px dashed #cbd5e1;">
                    <i class="fa-regular fa-comment-dots" style="font-size: 3rem; margin-bottom: 15px; color: #94a3b8; display: block;"></i>
                    <p style="margin:0; font-size: 1.05rem; font-weight: 500; color: #0f172a;">Anda belum menulis review apa pun.</p>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #64748b;">Buka halaman buku dan bagikan ulasan pertama Anda sekarang!</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="explore-footer">
        <div class="container d-flex justify-content-between align-items-center">
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

    <script>
        function konfirmasiHapusReview(idReview) {
            if (confirm("Apakah Anda yakin ingin menghapus review untuk buku ini?")) {
                window.location.href = "reviews.php?action=delete&id=" + idReview;
            }
        }
    </script>
</body>
</html>