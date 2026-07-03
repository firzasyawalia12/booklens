<?php
session_start();
include '../koneksi.php';


if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$nama_admin = $_SESSION['nama'] ?? 'Admin';

function safeText($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function tableExists($koneksi, $table) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function columnExists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function getSingleValue($koneksi, $query, $key, $default = 0) {
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row[$key] ?? $default;
    }

    return $default;
}

function timeAgo($datetime) {
    if (empty($datetime)) {
        return 'Added recently';
    }

    $timestamp = strtotime($datetime);

    if (!$timestamp) {
        return 'Added recently';
    }

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Added just now';
    }

    if ($difference < 3600) {
        $minutes = floor($difference / 60);
        return 'Added ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    }

    if ($difference < 86400) {
        $hours = floor($difference / 3600);
        return 'Added ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }

    if ($difference < 172800) {
        return 'Added yesterday';
    }

    return 'Added ' . date('M d, Y', $timestamp);
}

function getCoverUrl($cover) {
    $cover = basename((string)$cover);
    $absolutePath = __DIR__ . '/../assets/images/books/' . $cover;

    if ($cover !== '' && is_file($absolutePath)) {
        return '../assets/images/books/' . rawurlencode($cover);
    }

    return '../assets/images/ui/boxicons_book.png';
}

$total_users_raw = getSingleValue(
    $koneksi,
    "
    SELECT COUNT(*) AS total
    FROM users u
    JOIN role r ON u.id_role = r.id_role
    WHERE r.nama_role = 'user'
    ",
    'total',
    0
);

$total_books_raw = getSingleValue(
    $koneksi,
    "SELECT COUNT(*) AS total FROM books",
    'total',
    0
);

$total_reviews_raw = 0;
if (tableExists($koneksi, 'reviews')) {
    $total_reviews_raw = getSingleValue(
        $koneksi,
        "SELECT COUNT(*) AS total FROM reviews",
        'total',
        0
    );
}

$avg_rating_raw = null;
if (tableExists($koneksi, 'ratings') && columnExists($koneksi, 'ratings', 'nilai_rating')) {
    $avg_rating_raw = getSingleValue(
        $koneksi,
        "SELECT AVG(nilai_rating) AS rata_rata FROM ratings",
        'rata_rata',
        null
    );
}

$total_users = number_format((int)$total_users_raw);
$total_books = number_format((int)$total_books_raw);
$total_reviews = number_format((int)$total_reviews_raw);
$avg_rating = $avg_rating_raw !== null ? number_format((float)$avg_rating_raw, 1) : '0.0';

$punya_kolom_isbn = columnExists($koneksi, 'books', 'isbn');
$punya_kolom_dibuat = columnExists($koneksi, 'books', 'dibuat_pada');

$select_isbn = $punya_kolom_isbn ? ', isbn' : '';
$select_dibuat = $punya_kolom_dibuat ? ', dibuat_pada' : '';

$query_buku_terbaru = "
    SELECT id_buku, judul, penulis, genre, cover $select_isbn $select_dibuat
    FROM books
    ORDER BY id_buku DESC
    LIMIT 5
";

$buku_terbaru_result = mysqli_query($koneksi, $query_buku_terbaru);
$jumlah_buku_terbaru = $buku_terbaru_result ? mysqli_num_rows($buku_terbaru_result) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLens - Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #0f1f34;
            --sidebar-text: #395a7d;
            --page-bg: #eef7fa;
            --footer-bg: #b6d2e9;
            --border: #e5e9ef;
        }

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page-bg);
            color: #0f172a;
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
            background: #fff;
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
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .sidebar-menu {
            gap: 5px;
        }

        .sidebar-menu a {
            position: relative;
            min-height: 46px;
            gap: 12px;
            padding: 0 16px;
            color: var(--sidebar-text);
            font-size: 14px;
            font-weight: 600;
        }

        .sidebar-menu a:hover {
            color: var(--navy);
            background: #f5f8fc;
        }

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
            background: #10243e;
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

        .dashboard-content {
            width: 100%;
            max-width: 943px;
        }

        .top-bar {
            gap: 16px;
            margin-bottom: 42px;
        }

        .search-group .input-group-text {
            width: 40px;
            height: 36px;
            justify-content: center;
            border: 1px solid #364357;
            border-right: 0;
            border-radius: 4px 0 0 4px;
            background: #fff;
            color: #3d4653;
        }

        .search-group .form-control {
            height: 36px;
            border: 1px solid #364357;
            border-left: 0;
            border-radius: 0 4px 4px 0;
            background: #fff;
            color: #334155;
            font-size: 14px;
            box-shadow: none;
        }

        .search-group .form-control:focus {
            border-color: #364357;
            box-shadow: none;
        }

        .search-group .form-control::placeholder {
            color: #7d8796;
        }

        .notif-box,
        .notif-box img {
            width: 36px;
            height: 36px;
        }

        .notif-box {
            flex-shrink: 0;
        }

        .notif-box img {
            display: block;
            object-fit: contain;
        }

        .welcome-title {
            margin-bottom: 10px;
            color: var(--navy);
            font-size: 33px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 1px;
        }

        .welcome-sub-title {
            margin-bottom: 35px;
            color: #454b55;
            font-size: 15px;
            letter-spacing: .4px;
        }

        .stats-row {
            margin-bottom: 32px;
        }

        .card-stat {
            min-height: 128px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
        }

        .stat-icon-img {
            width: 32px;
            height: 32px;
            margin-bottom: 19px;
            object-fit: contain;
        }

        .stat-label {
            margin-bottom: 7px;
            color: #333943;
            font-size: 12px;
            line-height: 1;
        }

        .card-stat h3 {
            margin: 0;
            color: var(--navy);
            font-size: 22px;
            font-weight: 500;
            line-height: 1;
        }

        .rating-suffix {
            color: var(--navy);
            font-size: 22px;
            font-weight: 500;
        }

        .table-section {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
        }

        .table-header-custom {
            min-height: 62px;
            padding: 0 16px;
            border-bottom: 1px solid #edf0f3;
        }

        .table-header-custom h3 {
            margin: 0;
            gap: 10px;
            color: var(--navy);
            font-size: 21px;
            font-weight: 700;
        }

        .view-books-link {
            color: var(--navy);
            font-size: 12px;
            font-weight: 700;
        }

        .view-books-link:hover {
            color: #395a7d;
        }

        .dashboard-table {
            margin-bottom: 0;
        }

        .dashboard-table th {
            height: 48px;
            padding: 0 24px;
            border-bottom: 1px solid #edf0f3;
            background: #fff;
            color: #3f444c;
            font-size: 11.5px;
            font-weight: 700;
            vertical-align: middle;
        }

        .dashboard-table td {
            min-height: 100px;
            padding: 12px 24px;
            border-bottom: 1px solid #edf0f3;
            color: #555b65;
            font-size: 16px;
            vertical-align: middle;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .book-title-cell {
            min-width: 260px;
            gap: 16px;
        }

        .cover-img {
            width: 48px;
            height: 64px;
            border: 1px solid #d8e1e8;
            border-radius: 2px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .book-info h4 {
            margin-bottom: 4px;
            color: var(--navy);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .book-info p {
            margin: 0;
            color: #4b5058;
            font-size: 12px;
        }

        .badge-genre {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 2px;
            background: #eef0f1;
            color: #536977;
            font-size: 11px;
        }

        .isbn-text {
            color: #6d737d;
            font-size: 16px;
        }

        .empty-data {
            height: 100px;
            color: #94a3b8 !important;
            font-size: 14px !important;
            text-align: center;
        }

        .showing-entries-text {
            min-height: 47px;
            border-top: 1px solid #edf0f3;
            color: #2f343b;
            font-size: 13px;
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

            .sidebar-menu a {
                width: auto;
            }

            .main-content {
                padding: 30px 20px 50px;
            }
        }

        @media (max-width: 767.98px) {
            .main-footer {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 18px;
                padding: 24px 20px;
            }

            .footer-right {
                flex-wrap: wrap;
            }
        }

        .dashboard-table th:nth-child(2),
        .dashboard-table td:nth-child(2),
        .dashboard-table th:nth-child(3),
        .dashboard-table td:nth-child(3),
        .dashboard-table th:nth-child(4),
        .dashboard-table td:nth-child(4) {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper d-flex flex-column">
        <div class="content-wrapper d-flex flex-grow-1">
            <aside class="sidebar">
                <div class="brand-section d-flex align-items-center">
                    <img src="../assets/images/ui/boxicons_book.png" alt="BookLens Logo" class="brand-logo-img">
                    <div class="brand-text-wrapper">
                        <h2>BookLens</h2>
                        <p>Admin Management</p>
                    </div>
                </div>

                <nav class="sidebar-menu d-flex flex-column">
                    <a href="dashboard.php" class="active d-flex align-items-center">
                        <img src="../assets/images/ui/icon-dahboard.png" alt="Dashboard" class="menu-icon">
                        <span>Dashboard</span>
                    </a>

                    <a href="books.php" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-books.png" alt="Books" class="menu-icon">
                        <span>Books</span>
                    </a>

                    <a href="#" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-review.png" alt="Reviews" class="menu-icon">
                        <span>Reviews</span>
                    </a>

                    <a href="#" class="d-flex align-items-center">
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
                <div class="dashboard-content">
                    <div class="top-bar d-flex align-items-center">
                        <div class="input-group search-group flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search for books, authors, or users..." aria-label="Search">
                        </div>

                        <div class="notif-box">
                            <img src="../assets/images/ui/Button.png" alt="Notification">
                        </div>
                    </div>

                    <h1 class="welcome-title">Dashboard</h1>
                    <p class="welcome-sub-title">Welcome back, <?php echo safeText($nama_admin); ?>. Here is what's happening today at BookLens.</p>

                    <section class="row g-4 stats-row">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <img src="../assets/images/ui/icon- total user.png" alt="Total Users" class="stat-icon-img">
                                <p class="stat-label">Total Users</p>
                                <h3><?php echo $total_users; ?></h3>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <img src="../assets/images/ui/icon-total books.png" alt="Total Books" class="stat-icon-img">
                                <p class="stat-label">Total Books</p>
                                <h3><?php echo $total_books; ?></h3>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <img src="../assets/images/ui/icon-total reviews.png" alt="Total Reviews" class="stat-icon-img">
                                <p class="stat-label">Total Reviews</p>
                                <h3><?php echo $total_reviews; ?></h3>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <img src="../assets/images/ui/icon- avg rating.png" alt="Average Rating" class="stat-icon-img">
                                <p class="stat-label">Avg Rating</p>
                                <h3><?php echo $avg_rating; ?><span class="rating-suffix">/5.0</span></h3>
                            </div>
                        </div>
                    </section>

                    <section class="table-section">
                        <div class="table-header-custom d-flex align-items-center justify-content-between">
                            <h3 class="d-flex align-items-center">
                                <i class="bi bi-clock-history"></i>
                                <span>Newest Additions</span>
                            </h3>
                            <a href="books.php" class="view-books-link">View Books</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 33%;">Book Title</th>
                                        <th style="width: 22%;">Author</th>
                                        <th style="width: 16%;">Genre</th>
                                        <th style="width: 20%;">ISBN-13</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($buku_terbaru_result && $jumlah_buku_terbaru > 0): ?>
                                        <?php while ($book = mysqli_fetch_assoc($buku_terbaru_result)): ?>
                                            <?php
                                            $title = !empty($book['judul']) ? $book['judul'] : '-';
                                            $author = !empty($book['penulis']) ? $book['penulis'] : '-';
                                            $genre = !empty($book['genre']) ? $book['genre'] : '-';
                                            $isbn = ($punya_kolom_isbn && !empty($book['isbn'])) ? $book['isbn'] : '-';
                                            $dibuat = ($punya_kolom_dibuat && !empty($book['dibuat_pada'])) ? $book['dibuat_pada'] : null;
                                            $coverUrl = getCoverUrl($book['cover'] ?? '');
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="book-title-cell d-flex align-items-center">
                                                        <img src="<?php echo safeText($coverUrl); ?>" alt="Book Cover" class="cover-img">
                                                        <div class="book-info">
                                                            <h4><?php echo safeText($title); ?></h4>
                                                            <p><?php echo safeText(timeAgo($dibuat)); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo safeText($author); ?></td>
                                                <td><span class="badge-genre"><?php echo safeText($genre); ?></span></td>
                                                <td><span class="isbn-text"><?php echo safeText($isbn); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="empty-data">Belum ada data buku terbaru.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="showing-entries-text d-flex align-items-center justify-content-center">
                            Showing <?php echo (int)$jumlah_buku_terbaru; ?> of <?php echo (int)$total_books_raw; ?> entries
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
