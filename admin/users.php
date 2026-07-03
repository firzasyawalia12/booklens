<?php
require_once 'includes/dashboard.php';
include '../koneksi.php';

$current_page = 'users';
$flash_success = "";
$flash_error = "";

// =====================================================
// HANDLE AKSI: UPDATE ROLE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {

    $id_user_target = intval($_POST['id_user']);
    $new_role_name  = $_POST['role'] === 'admin' ? 'admin' : 'user';

    // Ambil id_role berdasarkan nama role
    $stmt_role = mysqli_prepare($koneksi, "SELECT id_role FROM mst_role WHERE nama_role = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_role, "s", $new_role_name);
    mysqli_stmt_execute($stmt_role);
    $result_role = mysqli_stmt_get_result($stmt_role);

    if ($result_role && $row_role = mysqli_fetch_assoc($result_role)) {
        $id_role_target = $row_role['id_role'];

        $stmt_update = mysqli_prepare($koneksi, "UPDATE users SET id_role = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_update, "ii", $id_role_target, $id_user_target);

        if (mysqli_stmt_execute($stmt_update)) {
            // Jika admin sedang mengubah role dirinya sendiri, sinkronkan session
            if ($id_user_target === (int) $_SESSION['id_user']) {
                $_SESSION['role'] = $new_role_name;
            }
            $flash_success = "Role user berhasil diperbarui.";
        } else {
            $flash_error = "Gagal memperbarui role user.";
        }
        mysqli_stmt_close($stmt_update);
    } else {
        $flash_error = "Role tidak valid.";
    }
    mysqli_stmt_close($stmt_role);
}

// =====================================================
// HANDLE AKSI: DELETE USER
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {

    $id_user_target = intval($_POST['id_user']);

    if ($id_user_target === (int) $_SESSION['id_user']) {
        $flash_error = "Kamu tidak bisa menghapus akunmu sendiri.";
    } else {
        $stmt_delete = mysqli_prepare($koneksi, "DELETE FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id_user_target);

        if (mysqli_stmt_execute($stmt_delete)) {
            $flash_success = "User berhasil dihapus.";
        } else {
            $flash_error = "Gagal menghapus user.";
        }
        mysqli_stmt_close($stmt_delete);
    }
}

// =====================================================
// AMBIL DAFTAR USER
// =====================================================
$users_list = [];
$query_users = "
    SELECT u.id_user, u.nama, u.username, u.email, r.nama_role AS role, u.dibuat_pada
    FROM users u
    JOIN mst_role r ON u.id_role = r.id_role
    ORDER BY u.dibuat_pada DESC
";
if ($res = mysqli_query($koneksi, $query_users)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $users_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BookLens Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <h1>Users</h1>
            <p>Kelola akun terdaftar, ubah role, atau hapus akun.</p>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
        <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($flash_error); ?></div>
    <?php endif; ?>

    <div class="admin-panel">
        <div class="admin-panel-title">Daftar User (<?php echo count($users_list); ?>)</div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_list)): ?>
                        <tr class="admin-empty-row"><td colspan="6">Belum ada user terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users_list as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nama']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $u['role'] === 'admin' ? 'role-badge-admin' : 'role-badge-user'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($u['role'])); ?>
                                    </span>
                                </td>
                                <td class="cell-muted"><?php echo date('d M Y', strtotime($u['dibuat_pada'])); ?></td>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <form method="POST" class="role-form">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                            <select name="role" class="role-select">
                                                <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="btn-admin-mini btn-save-role">Simpan</button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini? Semua review dan wishlist miliknya juga akan terhapus.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                            <button type="submit" class="btn-admin-mini btn-delete-user">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>