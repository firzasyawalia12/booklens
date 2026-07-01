<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function column_exists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $check && mysqli_num_rows($check) > 0;
}

function safe_text($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function bind_params_dynamic($stmt, $types, $params) {
    if ($types === '') {
        return true;
    }

    $bind_params = [];
    $bind_params[] = $types;

    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $bind_params);
}

$has_isbn = column_exists($koneksi, 'books', 'isbn');
$has_dibuat_pada = column_exists($koneksi, 'books', 'dibuat_pada');
$has_rata_rating = column_exists($koneksi, 'books', 'rata_rating');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    if ($has_isbn) {
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

if ($genre_filter !== '') {
    $where[] = "genre = ?";
    $params[] = $genre_filter;
    $types .= 's';
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$q_total_books = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM books");
$total_books = mysqli_fetch_assoc($q_total_books)['total'] ?? 0;

if ($has_dibuat_pada) {
    $q_recent = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM books WHERE dibuat_pada >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_additions = mysqli_fetch_assoc($q_recent)['total'] ?? 0;
} else {
    $recent_additions = 0;
}

if ($has_rata_rating) {
    $q_top = mysqli_query($koneksi, "SELECT AVG(rata_rating) AS top_rated FROM books");
    $top_rated = mysqli_fetch_assoc($q_top)['top_rated'] ?? 0;
    $top_rated = $top_rated ? number_format($top_rated, 2) : '0.00';
} else {
    $top_rated = '0.00';
}

$count_sql = "SELECT COUNT(*) AS total FROM books $where_sql";
$count_stmt = mysqli_prepare($koneksi, $count_sql);

if (!$count_stmt) {
    die("Prepare count failed: " . mysqli_error($koneksi));
}

bind_params_dynamic($count_stmt, $types, $params);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_filtered = mysqli_fetch_assoc($count_result)['total'] ?? 0;

$total_pages = max(1, (int)ceil($total_filtered / $limit));

if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;

$select_columns = $has_isbn
    ? "id_buku, judul, penulis, genre, cover, isbn"
    : "id_buku, judul, penulis, genre, cover";

$list_sql = "SELECT $select_columns FROM books $where_sql ORDER BY id_buku DESC LIMIT ? OFFSET ?";
$list_stmt = mysqli_prepare($koneksi, $list_sql);

if (!$list_stmt) {
    die("Prepare list failed: " . mysqli_error($koneksi));
}

$list_params = $params;
$list_types = $types . 'ii';
$list_params[] = $limit;
$list_params[] = $offset;

bind_params_dynamic($list_stmt, $list_types, $list_params);
mysqli_stmt_execute($list_stmt);
$books_result = mysqli_stmt_get_result($list_stmt);

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

function cover_path($cover) {
    $cover = basename($cover ?? '');

    if (!empty($cover) && file_exists(__DIR__ . '/../assets/images/books/' . $cover)) {
        return '../assets/images/books/' . htmlspecialchars($cover, ENT_QUOTES, 'UTF-8');
    }

    return '../assets/images/books/hujan.png';
}

function page_url($page_num, $search, $genre_filter) {
    $query = http_build_query([
        'search' => $search,
        'genre' => $genre_filter,
        'page' => $page_num
    ]);

    return 'books.php?' . $query;
}

$current_rows = $books_result ? mysqli_num_rows($books_result) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - BookLens</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS dan Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: #eef7fa;
            color: #152638;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        .page-wrap {
            min-height: calc(100vh - 102px);
            padding: 34px 52px 60px;
        }

        .breadcrumb-custom {
            font-size: 13px;
            font-weight: 600;
            color: #102235;
            margin-bottom: 36px;
        }

        .breadcrumb-custom a {
            color: #102235;
            text-decoration: none;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        .stat-card {
            height: 98px;
            background: #ffffff;
            border: 1px solid #bfc8d2;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 26px;
        }

        .stat-card p {
            color: #3d4650;
            font-size: 15px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-card h3 {
            font-size: 17px;
            font-weight: 500;
            color: #152638;
            margin: 0;
        }

        .stat-card .icon-box {
            width: 46px;
            height: 42px;
            border-radius: 12px;
            background: #cde8f4;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card .icon-box img {
            max-width: 28px;
            max-height: 28px;
            object-fit: contain;
        }

        .filter-box {
            background: #ffffff;
            border: 1px solid #bfc8d2;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .search-control,
        .select-control {
            height: 41px;
            border: 1px solid #bfc8d2;
            background: #fafbfc;
            display: flex;
            align-items: center;
            padding: 0 14px;
        }

        .search-control i {
            color: #7b838d;
            font-size: 17px;
            margin-right: 12px;
        }

        .search-control input,
        .select-control select {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            color: #636d79;
            font-size: 16px;
            box-shadow: none;
        }

        .search-control input:focus,
        .select-control select:focus {
            outline: none;
            box-shadow: none;
        }

        .select-control select {
            cursor: pointer;
        }

        .top-action {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
        }

        .add-btn {
            background: #17293b;
            color: #ffffff;
            border: 1px solid #17293b;
            padding: 10px 18px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .5px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .add-btn:hover {
            background: #0f1f34;
            border-color: #0f1f34;
            color: #ffffff;
        }

        .table-card {
            background: #ffffff;
            border: 1px solid #bfc8d2;
            border-radius: 4px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            color: #152638;
        }

        .table thead tr {
            height: 51px;
            background: #fafbfc;
            border-bottom: 1px solid #bfc8d2;
        }

        .table th {
            text-align: left;
            font-size: 15px;
            font-weight: 700;
            color: #424950;
            vertical-align: middle;
            background: #fafbfc;
            border-bottom: none;
        }

        .table td {
            height: 88px;
            font-size: 15px;
            color: #152638;
            vertical-align: middle;
            border-bottom: none;
        }

        .table tbody tr {
            border-bottom: 1px solid #eef1f4;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table th:first-child,
        .table td:first-child {
            padding-left: 76px;
            width: 220px;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 220px;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 180px;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 135px;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 170px;
        }

        .table th:last-child,
        .table td:last-child {
            width: 120px;
            text-align: center;
            padding-right: 35px;
        }

        .cover-img {
            width: 40px;
            height: 56px;
            object-fit: cover;
            border-radius: 2px;
            border: 1px solid #d9dee5;
        }

        .genre-badge {
            display: inline-block;
            background: #eef1f2;
            color: #607483;
            font-size: 12px;
            padding: 3px 9px;
            border-radius: 3px;
        }

        .isbn-text {
            color: #737c88;
        }

        .action-btn {
            text-decoration: none;
            font-size: 17px;
            margin: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .edit-btn {
            color: #526b78;
        }

        .edit-btn:hover {
            color: #17293b;
        }

        .delete-btn {
            color: #f01818;
        }

        .delete-btn:hover {
            color: #b80000;
        }

        .table-footer {
            height: 62px;
            background: #fafbfc;
            border-top: 1px solid #bfc8d2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
        }

        .entries-text {
            font-size: 16px;
            color: #44484d;
            margin: 0;
        }

        .book-pagination {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .book-pagination a,
        .book-pagination span {
            min-width: 36px;
            height: 31px;
            border: 1px solid #203144;
            color: #203144;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            border-radius: 2px;
            background: #ffffff;
        }

        .book-pagination .active {
            background: #203144;
            color: #ffffff;
        }

        .book-pagination .arrow {
            border: none;
            background: transparent;
            font-size: 22px;
            min-width: 30px;
        }

        footer {
            height: 102px;
            background: #b8d5ec;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 50px;
        }

        footer .left strong {
            font-size: 16px;
            font-weight: 500;
            color: #112437;
        }

        footer .left p {
            color: #6b7480;
            margin-top: 6px;
            letter-spacing: .6px;
            margin-bottom: 0;
        }

        footer .right {
            display: flex;
            gap: 20px;
        }

        footer .right a {
            color: #5d6875;
            text-decoration: none;
            font-size: 16px;
        }

        @media (max-width: 900px) {
            .page-wrap {
                padding: 28px 20px 50px;
            }

            .table-card {
                overflow-x: auto;
            }

            .table {
                min-width: 1000px;
            }

            footer {
                padding: 20px;
                flex-direction: column;
                height: auto;
                gap: 14px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="page-wrap">
        <div class="breadcrumb-custom">
            <a href="dashboard.php">Dashboard</a> &gt; 
            <span>Manage Books</span>
        </div>

        <section class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p>Total Books</p>
                        <h3><?php echo number_format($total_books); ?></h3>
                    </div>
                    <div class="icon-box">
                        <img src="../assets/images/ui/icon-total books.png" alt="Total Books">
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p>Recent Additions</p>
                        <h3><?php echo number_format($recent_additions); ?></h3>
                    </div>
                    <div class="icon-box">
                        <img src="../assets/images/ui/icon-total reviews.png" alt="Recent Additions">
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p>Top Rated</p>
                        <h3><?php echo $top_rated; ?></h3>
                    </div>
                    <div class="icon-box">
                        <img src="../assets/images/ui/icon- avg rating.png" alt="Top Rated">
                    </div>
                </div>
            </div>
        </section>

        <form method="GET" class="filter-box">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="search-control">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" placeholder="Find books by title or ISBN..." value="<?php echo safe_text($search); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="select-control">
                        <select name="genre" class="form-select border-0 bg-transparent p-0" onchange="this.form.submit()">
                            <option value="">All Genres</option>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?php echo safe_text($genre); ?>" <?php echo $genre_filter === $genre ? 'selected' : ''; ?>>
                                    <?php echo safe_text($genre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <div class="top-action">
            <a href="add_book.php" class="add-btn">
                <i class="bi bi-plus-lg"></i> Add Book
            </a>
        </div>

        <section class="table-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>ISBN</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($books_result && mysqli_num_rows($books_result) > 0): ?>
                        <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo cover_path($book['cover'] ?? ''); ?>" class="cover-img" alt="Cover">
                                </td>

                                <td><?php echo safe_text($book['judul'] ?? '-'); ?></td>
                                <td><?php echo safe_text($book['penulis'] ?? '-'); ?></td>

                                <td>
                                    <span class="genre-badge">
                                        <?php echo safe_text($book['genre'] ?? '-'); ?>
                                    </span>
                                </td>

                                <td class="isbn-text">
                                    <?php echo $has_isbn && !empty($book['isbn']) ? safe_text($book['isbn']) : '-'; ?>
                                </td>

                                <td>
                                    <a href="edit_book.php?id=<?php echo $book['id_buku']; ?>" class="action-btn edit-btn" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <a href="delete_book.php?id=<?php echo $book['id_buku']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:#7b838d;">
                                Belum ada data buku.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <p class="entries-text">
                    Showing <?php echo $current_rows; ?> of <?php echo number_format($total_filtered); ?> entries
                </p>

                <div class="book-pagination">
                    <?php if ($page > 1): ?>
                        <a class="arrow" href="<?php echo page_url($page - 1, $search, $genre_filter); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= min($total_pages, 3); $i++): ?>
                        <a href="<?php echo page_url($i, $search, $genre_filter); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($total_pages > 4): ?>
                        <span>...</span>
                        <a href="<?php echo page_url($total_pages, $search, $genre_filter); ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a class="arrow" href="<?php echo page_url($page + 1, $search, $genre_filter); ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="left">
            <strong>BookLens</strong>
            <p>&copy; 2026 BookLens. All rights reserved.</p>
        </div>

        <div class="right">
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>