<?php
session_start();
include '../koneksi.php';

// Proteksi halaman: hanya admin yang boleh membuka halaman kelola user.
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Membersihkan teks sebelum ditampilkan agar aman dari karakter HTML berbahaya.
function safeText($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Mengecek nama tabel role karena beberapa versi database memakai "role" atau "mst_role".
function tableExists($koneksi, $table) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

// Mengecek kolom opsional agar halaman tetap aman jika struktur database sedikit berbeda.
function columnExists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
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

// Menentukan warna badge sesuai role user.
function roleBadgeClass($role) {
    return strtolower($role) === 'admin' ? 'role-admin' : 'role-user';
}

// Memberi variasi warna avatar pada tiap baris user.
function avatarClass($index) {
    $classes = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-yellow'];
    return $classes[$index % count($classes)];
}

$roleTable = tableExists($koneksi, 'role') ? 'role' : 'mst_role';
$hasCreatedAt = columnExists($koneksi, 'users', 'dibuat_pada');
$createdSelect = $hasCreatedAt ? ', u.dibuat_pada' : '';

// Proses hapus user saat admin menekan tombol delete.
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];

    // Admin tidak boleh menghapus akun yang sedang dipakai untuk login.
    if ($deleteId === (int)$_SESSION['id_user']) {
        header('Location: users.php?error=self_delete');
        exit();
    }

    // Menghapus user berdasarkan id_user dengan prepared statement.
    $deleteStmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id_user = ?');
    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 'i', $deleteId);
        mysqli_stmt_execute($deleteStmt);
    }

    header('Location: users.php?status=deleted');
    exit();
}

// Mengambil keyword pencarian dan halaman aktif dari URL.
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$where = '';
$types = '';
$params = [];

// Filter pencarian berdasarkan nama, username, email, atau role.
if ($search !== '') {
    $where = "WHERE u.nama LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR r.nama_role LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

// Menghitung total data untuk kebutuhan teks "Showing..." dan pagination.
$countSql = "SELECT COUNT(*) AS total
             FROM users u
             JOIN `$roleTable` r ON u.id_role = r.id_role
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

// Mengambil data user sesuai halaman dan keyword pencarian.
$listSql = "SELECT u.id_user, u.nama, u.username, u.email, r.nama_role $createdSelect
            FROM users u
            JOIN `$roleTable` r ON u.id_role = r.id_role
            $where
            ORDER BY u.id_user ASC
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
$usersResult = mysqli_stmt_get_result($listStmt);
$currentRows = $usersResult ? mysqli_num_rows($usersResult) : 0;

$showingStart = $totalData > 0 ? $offset + 1 : 0;
$showingEnd = $offset + $currentRows;

// Membuat URL pagination tanpa menghilangkan keyword pencarian.
function pageUrl($page, $search) {
    return 'users.php?' . http_build_query([
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
    <title>Users - BookLens Admin</title>

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

        .users-content {
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

        .users-card {
            overflow: hidden;
            border: 1px solid #d8e0e8;
            border-radius: 7px;
            background: #ffffff;
        }

        .users-toolbar {
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

        .users-table {
            margin: 0;
            min-width: 960px;
        }

        .users-table thead th {
            height: 49px;
            padding: 0 24px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
            color: #303946;
            font-size: 12px;
            font-weight: 700;
            vertical-align: middle;
        }

        .users-table tbody td {
            height: 77px;
            padding: 12px 24px;
            border-bottom: 1px solid var(--line);
            color: #465166;
            font-size: 14px;
            vertical-align: middle;
        }

        .users-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .name-cell {
            gap: 14px;
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

        .user-name {
            color: var(--navy);
            font-size: 13px;
            font-weight: 700;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            height: 25px;
            padding: 0 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-admin {
            background: #dbeafe;
            color: #2563d9;
        }

        .role-user {
            background: #dcefe7;
            color: #16744d;
        }

        .delete-user-btn {
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

        .delete-user-btn:hover {
            background: #d10000;
            border-color: #d10000;
            color: #ffffff;
        }

        .delete-user-btn.disabled {
            pointer-events: none;
            opacity: .45;
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
            .users-toolbar,
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

                    <a href="#" class="d-flex align-items-center">
                        <img src="../assets/images/ui/icon-review.png" alt="Reviews" class="menu-icon">
                        <span>Reviews</span>
                    </a>

                    <a href="users.php" class="active d-flex align-items-center">
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
                <div class="users-content">
                    <!-- Search bar bagian atas halaman -->
                    <form method="GET" action="users.php" class="top-bar d-flex align-items-center">
                        <div class="input-group search-group flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search for books, authors, or users..." value="<?php echo safeText($search); ?>" aria-label="Search">
                        </div>

                        <div class="notif-box">
                            <img src="../assets/images/ui/Button.png" alt="Notification">
                        </div>
                    </form>

                    <h1 class="page-title">Users</h1>
                    <p class="page-subtitle">Manage all registered users in BookLens.</p>

                    <!-- Pesan setelah user berhasil dihapus -->
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                        <div class="alert alert-custom alert-dismissible fade show" role="alert">
                            User berhasil dihapus.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Pesan jika admin mencoba menghapus akunnya sendiri -->
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
                        <div class="alert alert-custom alert-dismissible fade show" role="alert">
                            Akun admin yang sedang digunakan tidak bisa dihapus.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <section class="users-card">
                        <!-- Search khusus untuk tabel user -->
                        <div class="users-toolbar d-flex align-items-center">
                            <form method="GET" action="users.php" class="table-search">
                                <input type="text" name="search" placeholder="Search user..." value="<?php echo safeText($search); ?>" aria-label="Search user">
                                <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <!-- Tabel daftar user dan aksi hapus -->
                            <table class="table users-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 8%;">No</th>
                                        <th style="width: 27%;">Name</th>
                                        <th style="width: 29%;">Email</th>
                                        <th style="width: 14%;">Role</th>
                                        <th style="width: 14%;">Joined Date</th>
                                        <th style="width: 8%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($usersResult && $currentRows > 0): ?>
                                        <?php $rowNumber = $offset + 1; ?>
                                        <?php $avatarIndex = 0; ?>
                                        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                                            <?php
                                            $role = $user['nama_role'] ?? 'user';
                                            $joinedDate = '-';

                                            // Format tanggal daftar user jika kolom dibuat_pada tersedia.
                                            if ($hasCreatedAt && !empty($user['dibuat_pada'])) {
                                                $timestamp = strtotime($user['dibuat_pada']);
                                                $joinedDate = $timestamp ? date('d M Y', $timestamp) : '-';
                                            }

                                            // Tombol hapus dinonaktifkan untuk akun admin yang sedang login.
                                            $isCurrentUser = (int)$user['id_user'] === (int)$_SESSION['id_user'];
                                            ?>
                                            <tr>
                                                <td><?php echo $rowNumber++; ?></td>
                                                <td>
                                                    <div class="name-cell d-flex align-items-center">
                                                        <span class="avatar-circle <?php echo avatarClass($avatarIndex++); ?>">
                                                            <i class="bi bi-person"></i>
                                                        </span>
                                                        <span class="user-name"><?php echo safeText($user['nama']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo safeText($user['email']); ?></td>
                                                <td>
                                                    <span class="role-badge <?php echo roleBadgeClass($role); ?>">
                                                        <?php echo safeText($role); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo safeText($joinedDate); ?></td>
                                                <td class="text-center">
                                                    <a
                                                        href="users.php?action=delete&id=<?php echo (int)$user['id_user']; ?>"
                                                        class="delete-user-btn <?php echo $isCurrentUser ? 'disabled' : ''; ?>"
                                                        title="Delete User"
                                                        onclick="return confirm('Yakin ingin menghapus user <?php echo safeText($user['nama']); ?>?')"
                                                    >
                                                        <i class="bi bi-trash3"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="empty-data">No registered user entries found.</td>
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
