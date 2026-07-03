<?php
session_start();
include 'koneksi.php'; // Menghubungkan ke db_booklens

// Proteksi login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
// Mengambil ID buku dari URL parameter ?id=, default ke 1 jika kosong
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 1;

// --- PROSES ACTION: ADD TO WISHLIST ---
if (isset($_POST['action']) && $_POST['action'] == 'add_wishlist') {
    $tanggal = date('Y-m-d');
    $stmt_wish = mysqli_prepare($koneksi, "INSERT IGNORE INTO wishlist (id_user, id_buku, tanggal_ditambahkan) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_wish, "iis", $id_user, $id_buku, $tanggal);
    mysqli_stmt_execute($stmt_wish);
    header("Location: detail_books.php?id=" . $id_buku . "&wishlist=success");
    exit();
}

// --- PROSES ACTION: SUBMIT REVIEW ---
if (isset($_POST['action']) && $_POST['action'] == 'submit_review') {
    $isi_review = trim($_POST['isi_review']);
    if (!empty($isi_review)) {
        $stmt_rev = mysqli_prepare($koneksi, "INSERT INTO reviews (id_user, id_buku, isi_review) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_rev, "iis", $id_user, $id_buku, $isi_review);
        mysqli_stmt_execute($stmt_rev);
        header("Location: detail_books.php?id=" . $id_buku . "&review=success");
        exit();
    }
}

// --- AMBIL DATA BUKU DARI DATABASE ---
$query_buku = "SELECT * FROM books WHERE id_buku = ?";
$stmt = mysqli_prepare($koneksi, $query_buku);
mysqli_stmt_bind_param($stmt, "i", $id_buku);
mysqli_stmt_execute($stmt);
$result_buku = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result_buku);

if (!$buku) {
    die("Buku tidak ditemukan di database.");
}

// --- AMBIL COUNT REVIEWS ---
$query_count = "SELECT COUNT(*) as total FROM reviews WHERE id_buku = ?";
$stmt_c = mysqli_prepare($koneksi, $query_count);
mysqli_stmt_bind_param($stmt_c, "i", $id_buku);
mysqli_stmt_execute($stmt_c);
$res_c = mysqli_stmt_get_result($stmt_c);
$count_data = mysqli_fetch_assoc($res_c);
$total_reviews = $count_data['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($buku['judul']) ?> - BookLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="main.css">
</head>
<body>
<nav class="navbar">
    <div class="logo-brand">
        <a href="home.php" class="brand-link">
            <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo">
            <span class="brand-text">BookLens</span>
        </a>
    </div>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="books_user.php" class="active">Books</a>
        <a href="wishlist.php">My Wishlist</a>
    </div>
</nav>

<main class="container detail-container">
    <div class="back-nav">
        <a href="books_user.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Catalog</a>
    </div>

    <div class="book-detail-main">
        <div class="detail-left-cover">
            <img src="assets/images/books/<?= htmlspecialchars($buku['cover_image']) ?>" alt="Cover" class="main-book-cover">
        </div>
        
        <div class="detail-right-info">
            <h1 class="book-title"><?= htmlspecialchars($buku['judul']) ?></h1>
            <p class="book-author">By <?= htmlspecialchars($buku['penulis']) ?></p>
            
            <div class="rating-badge-container">
                <span class="stars"><i class="fa-solid fa-star"></i> <?= htmlspecialchars($buku['rating']) ?></span>
                <span class="reviews-count">(<?= $total_reviews ?> Reviews)</span>
            </div>

            <div class="action-buttons-group" style="margin: 20px 0; display:flex; gap:10px;">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_wishlist">
                    <button type="submit" class="btn btn-wishlist"><i class="fa-solid fa-bookmark"></i> Add to Wishlist</button>
                </form>
            </div>

            <div class="book-meta-spec">
                <div class="spec-item"><strong>Publisher:</strong> <?= htmlspecialchars($buku['penerbit']) ?></div>
                <div class="spec-item"><strong>Year:</strong> <?= htmlspecialchars($buku['tahun_terbit']) ?></div>
                <div class="spec-item"><strong>ISBN:</strong> <?= htmlspecialchars($buku['isbn']) ?></div>
                <div class="spec-item"><strong>Pages:</strong> <?= htmlspecialchars($buku['jumlah_halaman']) ?></div>
            </div>

            <div class="book-synopsis">
                <h3>Synopsis</h3>
                <p><?= nl2br(htmlspecialchars($buku['sinopsis'])) ?></p>
            </div>
        </div>
    </div>

    <div class="reviews-section" style="margin-top: 40px;">
        <h2>User Reviews</h2>
        
        <form method="POST" action="" style="margin-bottom: 30px;">
            <input type="hidden" name="action" value="submit_review">
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <textarea name="isi_review" placeholder="Write your thoughts about this book..." rows="4" required style="padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit;"></textarea>
                <button type="submit" class="btn-submit" style="width: fit-content; padding: 8px 20px; background-color: #1a253c; color: white; border: none; border-radius: 6px; cursor: pointer;">Post Review</button>
            </div>
        </form>

        <div class="reviews-list">
            <?php
            $query_rev_list = "SELECT r.*, u.nama FROM reviews r JOIN users u ON r.id_user = u.id_user WHERE r.id_buku = ? ORDER BY r.tanggal_review DESC";
            $stmt_rl = mysqli_prepare($koneksi, $query_rev_list);
            mysqli_stmt_bind_param($stmt_rl, "i", $id_buku);
            mysqli_stmt_execute($stmt_rl);
            $res_rl = mysqli_stmt_get_result($stmt_rl);

            if (mysqli_num_rows($res_rl) == 0): ?>
                <p style="color: #64748b; font-style: italic;">No reviews yet. Be the first to review!</p>
            <?php else: 
                while ($rev = mysqli_fetch_assoc($res_rl)): ?>
                <div class="review-card" style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <div class="review-header" style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                        <strong><?= htmlspecialchars($rev['nama']) ?></strong>
                        <span style="font-size: 0.85rem; color: #94a3b8;"><?= $rev['tanggal_review'] ?></span>
                    </div>
                    <p style="margin: 0; color: #334155;"><?= nl2br(htmlspecialchars($rev['isi_review'])) ?></p>
                </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</main>
</body>
</html>