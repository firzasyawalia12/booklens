<?php
session_start();
include '../koneksi.php';

// Proteksi halaman: hanya admin yang boleh mengakses halaman kelola review.
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Membersihkan teks sebelum ditampilkan agar aman dari karakter HTML berbahaya.
function safeText($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Mengecek tabel opsional, dipakai agar halaman tetap jalan jika tabel ratings belum ada.
function tableExists($koneksi, $table) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

// Helper untuk bind parameter prepared statement secara dinamis.
function bindParams($stmt, $types, $params) {
    if ($types === '') {
        return true;
    }

    $bind = [$types];
    foreach ($params as $key => $value) {
        $bind[] = &$params[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $bind);
}

// Memberi variasi warna avatar pada tiap baris review.
function avatarClass($index) {
    $classes = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-yellow'];
    return $classes[$index % count($classes)];
}

// Mengambil path cover buku, dan memakai ikon default jika cover tidak ditemukan.
function coverPath($cover) {
    $cover = basename((string)$cover);
    $path = __DIR__ . '/../assets/images/books/' . $cover;

    if ($cover !== '' && is_file($path)) {
        return '../assets/images/books/' . rawurlencode($cover);
    }

    return '../assets/images/ui/boxicons_book.png';
}

// Membuat tampilan bintang rating dari angka 1 sampai 5.
function renderStars($rating) {
    $rating = max(0, min(5, (int)$rating));
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="bi bi-star-fill"></i>'
            : '<i class="bi bi-star-fill empty-star"></i>';
    }

    return $html;
}

// Proses hapus review saat admin menekan tombol delete.
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    $deleteStmt = mysqli_prepare($koneksi, 'DELETE FROM reviews WHERE id_review = ?');

    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 'i', $deleteId);
        mysqli_stmt_execute($deleteStmt);
    }

    header('Location: reviews.php?status=deleted');
    exit();
}

// Jika tabel ratings ada, rating diambil dari sana. Jika tidak ada, rating ditampilkan 0.
$hasRatingsTable = tableExists($koneksi, 'ratings');
$ratingSelect = $hasRatingsTable ? ', rt.nilai_rating' : ', 0 AS nilai_rating';
$ratingJoin = $hasRatingsTable ? 'LEFT JOIN ratings rt ON rv.id_user = rt.id_user AND rv.id_buku = rt.id_buku' : '';

// Mengambil keyword pencarian dan halaman aktif dari URL.
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$where = '';
$types = '';
$params = [];

// Filter pencarian berdasarkan buku, penulis, user, email, atau isi review.
if ($search !== '') {
    $where = "WHERE b.judul LIKE ? OR b.penulis LIKE ? OR u.nama LIKE ? OR u.email LIKE ? OR rv.isi_review LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like];
    $types = 'sssss';
}

// Menghitung total review untuk kebutuhan teks "Showing..." dan pagination.
$countSql = "SELECT COUNT(*) AS total
             FROM reviews rv
             JOIN books b ON rv.id_buku = b.id_buku
             JOIN users u ON rv.id_user = u.id_user
             $where";
$countStmt = mysqli_prepare($koneksi, $countSql);

if (!$countStmt) {
    die('Prepare count failed: ' . mysqli_error($koneksi));
}

bindParams($countStmt, $types, $params);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalData = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalData / $limit));

// Jika halaman di URL melebihi jumlah halaman, kembalikan ke halaman terakhir.
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Mengambil data review lengkap: buku, user, rating, isi review, dan tanggal.
$listSql = "SELECT rv.id_review, rv.isi_review, rv.tanggal_review,
                   b.judul, b.penulis, b.cover,
                   u.nama, u.email
                   $ratingSelect
            FROM reviews rv
            JOIN books b ON rv.id_buku = b.id_buku
            JOIN users u ON rv.id_user = u.id_user
            $ratingJoin
            $where
            ORDER BY rv.tanggal_review DESC, rv.id_review DESC
            LIMIT ? OFFSET ?";
$listStmt = mysqli_prepare($koneksi, $listSql);

if (!$listStmt) {
    die('Prepare list failed: ' . mysqli_error($koneksi));
}

$listTypes = $types . 'ii';
$listParams = $params;
$listParams[] = $limit;
$listParams[] = $offset;

bindParams($listStmt, $listTypes, $listParams);
mysqli_stmt_execute($listStmt);
$reviewsResult = mysqli_stmt_get_result($listStmt);
$currentRows = $reviewsResult ? mysqli_num_rows($reviewsResult) : 0;

$showingStart = $totalData > 0 ? $offset + 1 : 0;
$showingEnd = $offset + $currentRows;

// Membuat URL pagination tanpa menghilangkan keyword pencarian.
function pageUrl($page, $search) {
    return 'reviews.php?' . http_build_query([
        'page' => $page,
        'search' => $search
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - BookLens Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #10243e;
            --text: #17263d;
            --page-bg: #eef7fa;
            --footer-bg: #b8d5ec;
            --line: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page-bg);
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        .page-wrapper {
            min-height: 100vh;
        }

        .content-wrapper {
            min-height: calc(100vh - 100px);
        }

        .sidebar {
            width: 256px;
            min-width: 256px;
            padding: 32px 16px 24px;
            background: #ffffff;
        }

        .brand-section {
            gap: 14px;
            padding-left: 12px;
            margin-bottom: 47px;
        }

        .brand-logo-img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .brand-text-wrapper h2 {
            margin: 0;
            color: #13233a;
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .brand-text-wrapper p {
            margin: 6px 0 0;
            color: #68728a;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .sidebar-menu {
            gap: 5px;
        }

        .sidebar-menu a {
            position: relative;
            min-height: 46px;
            gap: 12px;
            padding: 0 16px;
            color: #395a7d;
            font-size: 14px;
            font-weight: 600;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: var(--navy);
            background: #edf3ff;
        }

        .sidebar-menu a.active::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: var(--navy);
        }

        .sidebar-menu a.logout-link {
            margin-top: 5px;
            color: #d10000;
        }

        .sidebar-menu a.logout-link:hover {
            color: #b00000;
            background: #fff4f4;
        }

        .menu-icon {
            width: 18px;
            height: 18px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .main-content {
            min-width: 0;
            padding: 35px 32px 58px;
            overflow: hidden;
        }

        .reviews-content {
            width: 100%;
            max-width: 1046px;
        }

        .top-bar {
            gap: 16px;
            margin-bottom: 42px;
        }

        .search-group .input-group-text {
            width: 40px;
            height: 42px;
            justify-content: center;
            border: 1px solid #364357;
            border-right: 0;
            border-radius: 4px 0 0 4px;
            background: #ffffff;
            color: #3d4653;
        }

        .search-group .form-control {
            height: 42px;
            border: 1px solid #364357;
            border-left: 0;
            border-radius: 0 4px 4px 0;
            background: #ffffff;
            color: #334155;
            font-size: 14px;
            box-shadow: none;
        }

        .search-group .form-control:focus {
            border-color: #364357;
            box-shadow: none;
        }

        .search-group .form-control::placeholder,
        .table-search input::placeholder {
            color: #7d8796;
        }

        .notif-box,
        .notif-box img {
            width: 36px;
            height: 36px;
        }

        .notif-box img {
            display: block;
            object-fit: contain;
        }

        .page-title {
            margin: 0 0 10px;
            color: var(--navy);
            font-size: 33px;
            font-weight: 700;
            line-height: 1;
        }

        .page-subtitle {
            margin: 0 0 37px;
            color: #454b55;
            font-size: 15px;
            letter-spacing: .3px;
        }

        .alert-custom {
            border: 1px solid #d8e0e8;
            border-radius: 6px;
            background: #ffffff;
            color: #243447;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .reviews-card {
            overflow: hidden;
            border: 1px solid #d8e0e8;
            border-radius: 7px;
            background: #ffffff;
        }

        .reviews-toolbar {
            min-height: 71px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
        }

        .table-search {
            width: 285px;
            height: 38px;
            border: 1px solid #dce3eb;
            border-radius: 4px;
            background: #ffffff;
            display: flex;
            align-items: center;
            padding: 0 12px;
            margin-left: auto;
        }

        .table-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #334155;
            font-size: 13px;
        }

        .table-search button {
            border: 0;
            background: transparent;
            color: #395a7d;
            line-height: 1;
            padding: 0;
        }

        .reviews-table {
            margin: 0;
            min-width: 1060px;
        }

        .reviews-table thead th {
            height: 49px;
            padding: 0 22px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
            color: #303946;
            font-size: 12px;
            font-weight: 700;
            vertical-align: middle;
        }

        .reviews-table tbody td {
            height: 86px;
            padding: 12px 22px;
            border-bottom: 1px solid var(--line);
            color: #465166;
            font-size: 13px;
            vertical-align: middle;
        }

        .reviews-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .book-cell,
        .user-cell {
            gap: 14px;
        }

        .cover-img {
            width: 30px;
            height: 44px;
            border: 1px solid #d8e1e8;
            border-radius: 2px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .book-title,
        .user-name {
            margin-bottom: 3px;
            color: var(--navy);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }

        .book-author,
        .user-email {
            margin: 0;
            color: #5b6575;
            font-size: 12px;
            line-height: 1.3;
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .avatar-blue {
            background: #dff0ff;
            color: #1683df;
        }

        .avatar-green {
            background: #daf4e4;
            color: #17a45b;
        }

        .avatar-purple {
            background: #eadcff;
            color: #7c3fec;
        }

        .avatar-yellow {
            background: #fff1d1;
            color: #f4a408;
        }

        .rating-stars {
            color: #f5b301;
            font-size: 15px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .empty-star {
            color: #cbd5e1;
        }

        .review-text {
            max-width: 230px;
            margin: 0;
            color: #313b4d;
            line-height: 1.55;
        }

        .delete-review-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #ffd5d5;
            border-radius: 6px;
            background: #fff5f5;
            color: #d10000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .delete-review-btn:hover {
            background: #d10000;
            border-color: #d10000;
            color: #ffffff;
        }

        .empty-data {
            height: 150px !important;
            color: #8a95a5 !important;
            text-align: center;
        }

        .table-footer {
            min-height: 48px;
            border-top: 1px solid var(--line);
            background: #ffffff;
            color: #424b5a;
            font-size: 13px;
            gap: 14px;
            padding: 10px 20px;
        }

        .pagination-box {
            gap: 6px;
        }

        .pagination-box a {
            min-width: 30px;
            height: 28px;
            border: 1px solid #d5dde7;
            border-radius: 4px;
            background: #ffffff;
            color: var(--navy);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .pagination-box a.active {
            background: var(--navy);
            border-color: var(--navy);
            color: #ffffff;
        }

        .main-footer {
            min-height: 100px;
            padding: 22px 50px 26px;
            background: var(--footer-bg);
        }

        .footer-left strong {
            display: block;
            margin-bottom: 5px;
            color: var(--navy);
            font-size: 15px;
            font-weight: 500;
        }

        .footer-left p {
            margin: 0;
            color: #606b78;
            font-size: 15px;
        }

        .footer-right {
            gap: 18px;
        }

        .footer-right a {
            color: #606b78;
            font-size: 15px;
        }

        .footer-right a:hover {
            color: var(--navy);
        }

        @media (max-width: 991.98px) {
            .content-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-width: 100%;
            }

            .sidebar-menu {
                flex-direction: row !important;
                flex-wrap: wrap;
            }

            .main-content {
                padding: 30px 20px 50px;
            }
        }

        @media (max-width: 767.98px) {
            .reviews-toolbar,
            .table-footer,
            .main-footer {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .table-search {
                width: 100%;
                margin-left: 0;
            }

            .main-footer {
                gap: 18px;
                padding: 24px 20px;
            }

            .footer-right {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper d-flex flex-column">
        <div class="content-wrapper d-flex flex-grow-1">
            <!-- Sidebar menu admin -->
            <aside class="sidebar">
                <div class="brand-section d-flex align-items-center">
                    <img src="../assets/images/ui/boxicons_book.png" alt="BookLens Logo" class="brand-logo-img">
                    <div class="brand-text-wrapper">
                        <h2>BookLens</h2>
                        <p>Admin Management</p>
                    </div>
                </div>

                <nav class="sidebar-menu d-flex flex-column">
                    <a href="dashboard.php" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-dahboard.png" alt="Dashboard" class="menu-icon">
                        <span>Dashboard</span>
                    </a>

                    <a href="books.php" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-books.png" alt="Books" class="menu-icon">
                        <span>Books</span>
                    </a>

                    <a href="reviews.php" class="active d-flex align-items-center">
                        <img src="../assets/images/ui/icon-review.png" alt="Reviews" class="menu-icon">
                        <span>Reviews</span>
                    </a>

                    <a href="users.php" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-user.png" alt="Users" class="menu-icon">
                        <span>Users</span>
                    </a>

                    <a href="../logout.php" class="logout-link d-flex align-items-center">
                        <img src="../assets/images/ui/icon-logout.png" alt="Logout" class="menu-icon">
                        <span>Logout</span>
                    </a>
                </nav>
            </aside>

            <main class="main-content flex-grow-1">
                <div class="reviews-content">
                    <!-- Search bar bagian atas halaman -->
                    <form method="GET" action="reviews.php" class="top-bar d-flex align-items-center">
                        <div class="input-group search-group flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search for books, authors, or users..." value="<?php echo safeText($search); ?>" aria-label="Search">
                        </div>

                        <div class="notif-box">
                            <img src="../assets/images/ui/Button.png" alt="Notification">
                        </div>
                    </form>

                    <h1 class="page-title">Reviews</h1>
                    <p class="page-subtitle">Manage all book reviews in BookLens.</p>

                    <!-- Pesan setelah review berhasil dihapus -->
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                        <div class="alert alert-custom alert-dismissible fade show" role="alert">
                            Review berhasil dihapus.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <section class="reviews-card">
                        <!-- Search khusus untuk tabel review -->
                        <div class="reviews-toolbar d-flex align-items-center">
                            <form method="GET" action="reviews.php" class="table-search">
                                <input type="text" name="search" placeholder="Search review..." value="<?php echo safeText($search); ?>" aria-label="Search review">
                                <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <!-- Tabel daftar review dan aksi hapus -->
                            <table class="table reviews-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 7%;">No</th>
                                        <th style="width: 21%;">Book</th>
                                        <th style="width: 23%;">User</th>
                                        <th style="width: 15%;">Rating</th>
                                        <th style="width: 22%;">Review</th>
                                        <th style="width: 12%;">Date</th>
                                        <th style="width: 7%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($reviewsResult && $currentRows > 0): ?>
                                        <?php $rowNumber = $offset + 1; ?>
                                        <?php $avatarIndex = 0; ?>
                                        <?php while ($review = mysqli_fetch_assoc($reviewsResult)): ?>
                                            <?php
                                            // Format tanggal review agar tampil seperti "30 Nov 2024".
                                            $timestamp = !empty($review['tanggal_review']) ? strtotime($review['tanggal_review']) : false;
                                            $reviewDate = $timestamp ? date('d M Y', $timestamp) : '-';
                                            ?>
                                            <tr>
                                                <td><?php echo $rowNumber++; ?></td>
                                                <td>
                                                    <!-- Informasi buku yang direview -->
                                                    <div class="book-cell d-flex align-items-center">
                                                        <img src="<?php echo safeText(coverPath($review['cover'] ?? '')); ?>" alt="Book Cover" class="cover-img">
                                                        <div>
                                                            <h4 class="book-title"><?php echo safeText($review['judul']); ?></h4>
                                                            <p class="book-author"><?php echo safeText($review['penulis']); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- Informasi user yang menulis review -->
                                                    <div class="user-cell d-flex align-items-center">
                                                        <span class="avatar-circle <?php echo avatarClass($avatarIndex++); ?>">
                                                            <i class="bi bi-person"></i>
                                                        </span>
                                                        <div>
                                                            <h4 class="user-name"><?php echo safeText($review['nama']); ?></h4>
                                                            <p class="user-email"><?php echo safeText($review['email']); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- Rating ditampilkan sebagai lima bintang -->
                                                    <span class="rating-stars">
                                                        <?php echo renderStars($review['nilai_rating'] ?? 0); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="review-text"><?php echo safeText($review['isi_review']); ?></p>
                                                </td>
                                                <td><?php echo safeText($reviewDate); ?></td>
                                                <td class="text-center">
                                                    <!-- Tombol hapus review -->
                                                    <a
                                                        href="reviews.php?action=delete&id=<?php echo (int)$review['id_review']; ?>"
                                                        class="delete-review-btn"
                                                        title="Delete Review"
                                                        onclick="return confirm('Yakin ingin menghapus review ini?')"
                                                    >
                                                        <i class="bi bi-trash3"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="empty-data">No review entries found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer tabel: jumlah data tampil dan pagination -->
                        <div class="table-footer d-flex align-items-center justify-content-center">
                            <span>
                                Showing <?php echo $showingStart; ?> to <?php echo $showingEnd; ?> of <?php echo $totalData; ?> entries
                            </span>

                            <?php if ($totalPages > 1): ?>
                                <div class="pagination-box d-flex align-items-center">
                                    <?php if ($page > 1): ?>
                                        <a href="<?php echo pageUrl($page - 1, $search); ?>" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <a href="<?php echo pageUrl($i, $search); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="<?php echo pageUrl($page + 1, $search); ?>" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <footer class="main-footer d-flex align-items-center justify-content-between">
            <div class="footer-left">
                <strong>BookLens</strong>
                <p>&copy; 2026 BookLens. All rights reserved.</p>
            </div>

            <div class="footer-right d-flex align-items-center">
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms</a>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
