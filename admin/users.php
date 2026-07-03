<?php
include '../includes/auth.php';
include '../config/database.php';
include '../includes/functions.php';

$page_title = 'Users - BookLens';
$users = mysqli_query($conn, "SELECT id_user, name, email, role, created_at FROM users ORDER BY created_at DESC");

include '../includes/admin-header.php';
?>

<section class="page-heading">
    <h1>Users</h1>
    <p>Daftar pengguna yang terdaftar pada sistem BookLens.</p>
</section>

<section class="panel">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)) : ?>
                <tr>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="genre-badge"><?= e($user['role']) ?></span></td>
                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
