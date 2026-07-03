<?php
session_start();
include '../koneksi.php';

// Proteksi halaman: hanya admin yang boleh membuka halaman kelola buku.
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Mengecek kolom opsional agar halaman tetap aman pada struktur database yang berbeda.
function columnExists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $check && mysqli_num_rows($check) > 0;
}

// Membersihkan teks sebelum ditampilkan ke halaman.
function safeText($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
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

// Mengambil path cover buku, atau cover default jika file tidak ada.
function coverPath($cover) {
    $cover = basename($cover ?? '');

    if ($cover !== '' && file_exists(__DIR__ . '/../assets/images/books/' . $cover)) {
        return '../assets/images/books/' . rawurlencode($cover);
    }

    return '../assets/images/ui/boxicons_book.png';
}

// Membuat URL pagination tanpa menghilangkan filter dan keyword pencarian.
function pageUrl($pageNum, $search, $genreFilter) {
    return 'books.php?' . http_build_query([
        'search' => $search,
        'genre' => $genreFilter,
        'page' => $pageNum
    ]);
}

$hasIsbn = columnExists($koneksi, 'books', 'isbn');
$hasCreatedAt = columnExists($koneksi, 'books', 'dibuat_pada');
$hasRataRating = columnExists($koneksi, 'books', 'rata_rating');

// Mengambil keyword, filter genre, dan halaman aktif dari URL.
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genreFilter = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;

$where = [];
$params = [];
$types = '';

// Filter pencarian buku berdasarkan judul, penulis, dan ISBN jika kolom tersedia.
if ($search !== '') {
    if ($hasIsbn) {
        $where[] = "(judul LIKE ? OR penulis LIKE ? OR isbn LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= 'sss';
    } else {
        $where[] = "(judul LIKE ? OR penulis LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }
}

// Filter genre dari dropdown.
if ($genreFilter !== '') {
    $where[] = "genre = ?";
    $params[] = $genreFilter;
    $types .= 's';
}

$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Data ringkasan di bagian atas.
$totalBooksQuery = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM books');
$totalBooks = (int)(mysqli_fetch_assoc($totalBooksQuery)['total'] ?? 0);

// Menghitung buku yang baru ditambahkan dalam 7 hari terakhir.
if ($hasCreatedAt) {
    $recentQuery = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM books WHERE dibuat_pada >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $recentAdditions = (int)(mysqli_fetch_assoc($recentQuery)['total'] ?? 0);
} else {
    $recentAdditions = 0;
}

// Mengambil rata-rata rating jika kolom rata_rating tersedia.
if ($hasRataRating) {
    $topQuery = mysqli_query($koneksi, 'SELECT AVG(rata_rating) AS top_rated FROM books');
    $topRated = mysqli_fetch_assoc($topQuery)['top_rated'] ?? 0;
    $topRated = $topRated ? number_format((float)$topRated, 2) : '0.00';
} else {
    $topRated = '0.00';
}

// Menghitung total buku sesuai filter untuk pagination.
$countSql = "SELECT COUNT(*) AS total FROM books $whereSql";
$countStmt = mysqli_prepare($koneksi, $countSql);

if (!$countStmt) {
    die('Prepare count failed: ' . mysqli_error($koneksi));
}

bindParams($countStmt, $types, $params);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalFiltered = (int)(mysqli_fetch_assoc($countResult)['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalFiltered / $limit));

// Jika halaman di URL melebihi jumlah halaman, kembalikan ke halaman terakhir.
if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;
$selectColumns = $hasIsbn
    ? 'id_buku, judul, penulis, genre, cover, isbn'
    : 'id_buku, judul, penulis, genre, cover';

// Mengambil daftar buku sesuai filter dan halaman aktif.
$listSql = "SELECT $selectColumns FROM books $whereSql ORDER BY id_buku DESC LIMIT ? OFFSET ?";
$listStmt = mysqli_prepare($koneksi, $listSql);

if (!$listStmt) {
    die('Prepare list failed: ' . mysqli_error($koneksi));
}

$listParams = $params;
$listTypes = $types . 'ii';
$listParams[] = $limit;
$listParams[] = $offset;

bindParams($listStmt, $listTypes, $listParams);
mysqli_stmt_execute($listStmt);
$booksResult = mysqli_stmt_get_result($listStmt);
$currentRows = $booksResult ? mysqli_num_rows($booksResult) : 0;
$showingStart = $totalFiltered > 0 ? $offset + 1 : 0;
$showingEnd = $offset + $currentRows;

$genres = [
    'Fantasy',
    'Mystery',
    'Romance',
    'Horor',
    'Thriller',
    'Sci-Fi',
    'Self Help',
    'Business',
    'Drama',
    'Family',
    'Poetry'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - BookLens Admin</title>

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
            --blue: #2563d9;
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

        .books-content {
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
            margin: 0 0 30px;
            color: #454b55;
            font-size: 15px;
            letter-spacing: .3px;
        }

        .stats-row {
            margin-bottom: 24px;
        }

        .stat-card {
            min-height: 104px;
            border: 1px solid #d8e0e8;
            border-radius: 7px;
            background: #ffffff;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card p {
            margin: 0 0 7px;
            color: #4b5565;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .stat-card h3 {
            margin: 0;
            color: var(--navy);
            font-size: 22px;
            font-weight: 700;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #e1f1fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon img {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        .books-card {
            overflow: hidden;
            border: 1px solid #d8e0e8;
            border-radius: 7px;
            background: #ffffff;
        }

        .books-toolbar {
            min-height: 71px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
            gap: 14px;
        }

        .add-book-btn {
            height: 38px;
            padding: 0 16px;
            border: 0;
            border-radius: 4px;
            background: var(--blue);
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .add-book-btn:hover {
            background: #1d4fbd;
            color: #ffffff;
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

        .genre-select {
            width: 170px;
            height: 38px;
            border: 1px solid #dce3eb;
            border-radius: 4px;
            background: #ffffff;
            color: #4b5565;
            font-size: 13px;
            padding: 0 10px;
            outline: none;
        }

        .books-table {
            margin: 0;
            min-width: 930px;
        }

        .books-table thead th {
            height: 49px;
            padding: 0 24px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
            color: #303946;
            font-size: 12px;
            font-weight: 700;
            vertical-align: middle;
        }

        .books-table tbody td {
            height: 82px;
            padding: 12px 24px;
            border-bottom: 1px solid var(--line);
            color: #465166;
            font-size: 13px;
            vertical-align: middle;
        }

        .books-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .book-cell {
            gap: 14px;
        }

        .cover-img {
            width: 38px;
            height: 56px;
            border: 1px solid #d8e1e8;
            border-radius: 2px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .book-title {
            margin: 0 0 3px;
            color: var(--navy);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.25;
        }

        .book-author {
            margin: 0;
            color: #5b6575;
            font-size: 12px;
            line-height: 1.3;
        }

        .genre-badge {
            display: inline-flex;
            align-items: center;
            min-height: 25px;
            padding: 4px 10px;
            border-radius: 4px;
            background: #edf2f4;
            color: #526a79;
            font-size: 12px;
            font-weight: 600;
        }

        .isbn-text {
            color: #5b6575;
            white-space: nowrap;
        }

        .action-group {
            gap: 8px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .edit-btn {
            border: 1px solid #d8e3ef;
            background: #f7fbff;
            color: #395a7d;
        }

        .edit-btn:hover {
            background: var(--navy);
            border-color: var(--navy);
            color: #ffffff;
        }

        .delete-btn {
            border: 1px solid #ffd5d5;
            background: #fff5f5;
            color: #d10000;
        }

        .delete-btn:hover {
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

        .pagination-box a,
        .pagination-box span {
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
            .books-toolbar,
            .table-footer,
            .main-footer {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .table-search,
            .genre-select {
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

                    <a href="books.php" class="active d-flex align-items-center">
                        <img src="../assets/images/ui/icon-books.png" alt="Books" class="menu-icon">
                        <span>Books</span>
                    </a>

                    <a href="reviews.php" class="d-flex align-items-center">
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
                <div class="books-content">
                    <!-- Search bar bagian atas halaman -->
                    <form method="GET" action="books.php" class="top-bar d-flex align-items-center">
                        <div class="input-group search-group flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search for books, authors, or users..." value="<?php echo safeText($search); ?>" aria-label="Search">
                            <input type="hidden" name="genre" value="<?php echo safeText($genreFilter); ?>">
                        </div>

                        <div class="notif-box">
                            <img src="../assets/images/ui/Button.png" alt="Notification">
                        </div>
                    </form>

                    <h1 class="page-title">Books</h1>
                    <p class="page-subtitle">Manage all books available in BookLens.</p>

                    <!-- Kartu ringkasan jumlah buku, buku terbaru, dan rating -->
                    <section class="row g-3 stats-row">
                        <div class="col-12 col-md-4">
                            <div class="stat-card">
                                <div>
                                    <p>Total Books</p>
                                    <h3><?php echo number_format($totalBooks); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <img src="../assets/images/ui/icon-total books.png" alt="Total Books">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="stat-card">
                                <div>
                                    <p>Recent Additions</p>
                                    <h3><?php echo number_format($recentAdditions); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <img src="../assets/images/ui/icon-total reviews.png" alt="Recent Additions">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="stat-card">
                                <div>
                                    <p>Top Rated</p>
                                    <h3><?php echo safeText($topRated); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <img src="../assets/images/ui/icon- avg rating.png" alt="Top Rated">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="books-card">
                        <!-- Toolbar tabel: tombol tambah buku, pencarian, dan filter genre -->
                        <form method="GET" action="books.php" class="books-toolbar d-flex align-items-center">
                            <a href="add_book.php" class="add-book-btn">
                                <i class="bi bi-plus-lg"></i>
                                <span>Add Book</span>
                            </a>

                            <div class="table-search">
                                <input type="text" name="search" placeholder="Search book..." value="<?php echo safeText($search); ?>" aria-label="Search book">
                                <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            </div>

                            <select name="genre" class="genre-select" onchange="this.form.submit()" aria-label="Filter genre">
                                <option value="">All Genres</option>
                                <?php foreach ($genres as $genre): ?>
                                    <option value="<?php echo safeText($genre); ?>" <?php echo $genreFilter === $genre ? 'selected' : ''; ?>>
                                        <?php echo safeText($genre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="table-responsive">
                            <!-- Tabel daftar buku dan aksi edit/hapus -->
                            <table class="table books-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 9%;">No</th>
                                        <th style="width: 34%;">Book</th>
                                        <th style="width: 17%;">Genre</th>
                                        <th style="width: 23%;">ISBN</th>
                                        <th style="width: 17%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($booksResult && $currentRows > 0): ?>
                                        <?php $rowNumber = $offset + 1; ?>
                                        <?php while ($book = mysqli_fetch_assoc($booksResult)): ?>
                                            <tr>
                                                <td><?php echo $rowNumber++; ?></td>
                                                <td>
                                                    <!-- Cover, judul, dan penulis buku -->
                                                    <div class="book-cell d-flex align-items-center">
                                                        <img src="<?php echo safeText(coverPath($book['cover'] ?? '')); ?>" class="cover-img" alt="Cover">
                                                        <div>
                                                            <h4 class="book-title"><?php echo safeText($book['judul'] ?? '-'); ?></h4>
                                                            <p class="book-author"><?php echo safeText($book['penulis'] ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!-- Genre buku ditampilkan sebagai badge kecil -->
                                                    <span class="genre-badge"><?php echo safeText($book['genre'] ?? '-'); ?></span>
                                                </td>
                                                <td>
                                                    <!-- ISBN bisa kosong jika database tidak punya kolom ISBN atau datanya belum diisi -->
                                                    <span class="isbn-text">
                                                        <?php echo $hasIsbn && !empty($book['isbn']) ? safeText($book['isbn']) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <!-- Aksi admin untuk mengedit atau menghapus buku -->
                                                    <div class="action-group d-inline-flex align-items-center">
                                                        <a href="edit_book.php?id=<?php echo (int)$book['id_buku']; ?>" class="action-btn edit-btn" title="Edit Book">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>

                                                        <a href="delete_book.php?id=<?php echo (int)$book['id_buku']; ?>" class="action-btn delete-btn" title="Delete Book" onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                                            <i class="bi bi-trash3"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="empty-data">Belum ada data buku.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer tabel: jumlah data tampil dan pagination -->
                        <div class="table-footer d-flex align-items-center justify-content-center">
                            <span>
                                Showing <?php echo $showingStart; ?> to <?php echo $showingEnd; ?> of <?php echo $totalFiltered; ?> entries
                            </span>

                            <?php if ($totalPages > 1): ?>
                                <div class="pagination-box d-flex align-items-center">
                                    <?php if ($page > 1): ?>
                                        <a href="<?php echo pageUrl($page - 1, $search, $genreFilter); ?>" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <a href="<?php echo pageUrl($i, $search, $genreFilter); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="<?php echo pageUrl($page + 1, $search, $genreFilter); ?>" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
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
