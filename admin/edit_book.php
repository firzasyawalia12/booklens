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

$has_isbn = column_exists($koneksi, 'books', 'isbn');
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

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

if ($id_buku <= 0) {
    header('Location: books.php');
    exit();
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM books WHERE id_buku = ? LIMIT 1");

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, 'i', $id_buku);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);

if (!$book) {
    header('Location: books.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $jumlah_halaman = trim($_POST['jumlah_halaman'] ?? '');
    $sinopsis = trim($_POST['sinopsis'] ?? '');
    $cover_name = $book['cover'] ?? '';

    if ($judul === '' || $penulis === '' || $genre === '') {
        $error = 'Book Title, Author Name, dan Genre wajib diisi.';
    } else {
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $file_tmp = $_FILES['cover']['tmp_name'];
            $file_name = $_FILES['cover']['name'];
            $file_size = $_FILES['cover']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = 'Cover hanya boleh JPG atau PNG.';
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error = 'Ukuran cover maksimal 5MB.';
            } else {
                $cover_name = 'book_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $upload_dir = __DIR__ . '/../assets/images/books/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (!move_uploaded_file($file_tmp, $upload_dir . $cover_name)) {
                    $error = 'Cover gagal disimpan.';
                }
            }
        }

        if ($error === '') {
            $tahun_val = $tahun_terbit !== '' ? intval($tahun_terbit) : null;
            $halaman_val = $jumlah_halaman !== '' ? intval($jumlah_halaman) : null;

            if ($has_isbn) {
                $sql = "UPDATE books 
                        SET judul=?, penulis=?, genre=?, penerbit=?, tahun_terbit=?, jumlah_halaman=?, sinopsis=?, cover=?, isbn=? 
                        WHERE id_buku=?";

                $stmt = mysqli_prepare($koneksi, $sql);

                if (!$stmt) {
                    die("Prepare failed: " . mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssssiisssi',
                    $judul,
                    $penulis,
                    $genre,
                    $penerbit,
                    $tahun_val,
                    $halaman_val,
                    $sinopsis,
                    $cover_name,
                    $isbn,
                    $id_buku
                );
            } else {
                $sql = "UPDATE books 
                        SET judul=?, penulis=?, genre=?, penerbit=?, tahun_terbit=?, jumlah_halaman=?, sinopsis=?, cover=? 
                        WHERE id_buku=?";

                $stmt = mysqli_prepare($koneksi, $sql);

                if (!$stmt) {
                    die("Prepare failed: " . mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssssiissi',
                    $judul,
                    $penulis,
                    $genre,
                    $penerbit,
                    $tahun_val,
                    $halaman_val,
                    $sinopsis,
                    $cover_name,
                    $id_buku
                );
            }

            if (mysqli_stmt_execute($stmt)) {
                header('Location: books.php?status=edited');
                exit();
            } else {
                $error = 'Gagal mengubah buku: ' . mysqli_error($koneksi);
            }
        }
    }
}

$current_cover = !empty($book['cover']) && file_exists(__DIR__ . '/../assets/images/books/' . $book['cover'])
    ? '../assets/images/books/' . safe_text($book['cover'])
    : '../assets/images/books/pukul.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1280">
    <title>Edit Book - BookLens</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS dan Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background: #eef7fa;
            color: #152638;
            min-height: 100vh;
            min-width: 1280px;
            display: flex;
            flex-direction: column;
        }

        .page-wrap {
            padding: 33px 47px 46px;
            flex: 1;
        }

        .breadcrumb-custom {
            font-size: 13px;
            font-weight: 600;
            color: #102235;
            margin-bottom: 29px;
        }

        .breadcrumb-custom a {
            color: #102235;
            text-decoration: none;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        .page-title {
            font-size: 31px;
            letter-spacing: 1px;
            color: #17293b;
            margin-bottom: 7px;
            font-weight: 700;
        }

        .page-desc {
            font-size: 16px;
            color: #434a52;
            line-height: 1.55;
            max-width: 760px;
            margin-bottom: 24px;
        }

        .book-form-layout {
            display: grid;
            grid-template-columns: 264px 890px;
            gap: 24px;
            align-items: start;
        }

        .cover-panel {
            width: 264px;
            min-height: 520px;
            background: #ffffff;
            border: 1px solid #bfc8d2;
            padding: 26px 24px;
        }

        .panel-label {
            font-size: 13px;
            letter-spacing: 1px;
            color: #33383e;
            margin-bottom: 45px;
        }

        .cover-preview-wrap {
            width: 212px;
            height: 315px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 43px;
            cursor: pointer;
            overflow: hidden;
            background: #f7f8fa;
        }

        .cover-preview-wrap img {
            width: 212px;
            height: 315px;
            object-fit: cover;
        }

        .cover-note {
            display: flex;
            gap: 8px;
            color: #41464d;
            font-size: 12px;
            font-style: italic;
            line-height: 1.3;
            margin-bottom: 0;
        }

        .cover-note i {
            margin-top: 2px;
        }

        .form-panel {
            width: 890px;
            min-height: 696px;
            background: #ffffff;
            border: 1px solid #bfc8d2;
            padding: 33px 32px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            color: #444b54;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            width: 100%;
            height: 51px;
            border: 1px solid #c3c9d1;
            background-color: #fafbfc;
            border-radius: 0;
            padding: 0 16px;
            font-size: 16px;
            color: #17293b;
            outline: none;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #17293b;
            box-shadow: none;
            background-color: #ffffff;
        }

        .form-control::placeholder,
        textarea.form-control::placeholder {
            color: #6f7885;
        }

        textarea.form-control {
            height: 201px;
            resize: none;
            padding: 13px 16px;
            line-height: 1.55;
            font-size: 18px;
            color: #4d535b;
        }

        .divider {
            margin: 32px 0;
            border: 0;
            border-top: 1px solid #c3c9d1;
            opacity: 1;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
        }

        .btn-cancel,
        .btn-save {
            height: 45px;
            padding: 0 32px;
            border-radius: 2px;
            font-size: 14px;
            letter-spacing: 1px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel {
            background: #ffffff;
            border: 1px solid #526b80;
            color: #526b80;
        }

        .btn-cancel:hover {
            background: #f8f9fb;
            color: #526b80;
            border-color: #526b80;
        }

        .btn-save {
            background: #17293b;
            border: 1px solid #17293b;
            color: #ffffff;
        }

        .btn-save:hover {
            background: #0f1f34;
            border-color: #0f1f34;
            color: #ffffff;
        }

        .custom-alert {
            width: 1180px;
            border-radius: 2px;
            margin-bottom: 18px;
            padding: 12px 16px;
            font-size: 14px;
        }

        footer {
            height: 102px;
            background: #b8d5ec;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 50px;
            margin-top: auto;
        }

        footer .left strong {
            font-size: 16px;
            font-weight: 500;
            color: #112437;
        }

        footer .left p {
            color: #6b7480;
            margin-top: 6px;
            margin-bottom: 0;
            letter-spacing: .6px;
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
    </style>
</head>
<body>
    <main class="page-wrap">
        <div class="breadcrumb-custom">
            <a href="dashboard.php">Dashboard</a> &gt;
            <a href="books.php">Manage Books</a> &gt;
            Edit Book
        </div>

        <h1 class="page-title">Edit Book</h1>

        <p class="page-desc">
            Expand the collective knowledge of the BookLens community. Please provide accurate<br>
            details for the new addition to the archive.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert">
                <?php echo safe_text($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="book-form-layout">
            <aside class="cover-panel">
                <p class="panel-label">Book Cover</p>

                <label class="cover-preview-wrap" for="coverInput">
                    <img id="coverPreview" src="<?php echo $current_cover; ?>" alt="Book Cover">
                </label>

                <input type="file" name="cover" id="coverInput" accept="image/png,image/jpeg" hidden>

                <p class="cover-note">
                    <i class="bi bi-info-circle"></i>
                    <span>Cover art should clearly show the title and author for archival quality.</span>
                </p>
            </aside>

            <section class="form-panel">
                <div class="row g-4">
                    <div class="col-6">
                        <label class="form-label">Book Title</label>
                        <input class="form-control" type="text" name="judul" placeholder="e.g. Pukul Setengah Lima" value="<?php echo safe_text($book['judul'] ?? ''); ?>" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Author Name</label>
                        <input class="form-control" type="text" name="penulis" placeholder="e.g. Rintik Sedu" value="<?php echo safe_text($book['penulis'] ?? ''); ?>" required>
                    </div>

                    <div class="col-4">
                        <label class="form-label">Publisher</label>
                        <input class="form-control" type="text" name="penerbit" placeholder="Gramedia Pusta..." value="<?php echo safe_text($book['penerbit'] ?? ''); ?>">
                    </div>

                    <div class="col-4">
                        <label class="form-label">Publication Year</label>
                        <input class="form-control" type="number" name="tahun_terbit" placeholder="2023" min="1000" max="9999" value="<?php echo safe_text($book['tahun_terbit'] ?? ''); ?>">
                    </div>

                    <div class="col-4">
                        <label class="form-label">Genre</label>
                        <select class="form-select" name="genre" required>
                            <option value="">Select Genre</option>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?php echo safe_text($genre); ?>" <?php echo (($book['genre'] ?? '') === $genre) ? 'selected' : ''; ?>>
                                    <?php echo safe_text($genre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label">ISBN-13</label>
                        <input 
                            class="form-control" 
                            type="text" 
                            name="isbn" 
                            placeholder="xx-xxx-xxxx-xx" 
                            value="<?php echo $has_isbn ? safe_text($book['isbn'] ?? '') : ''; ?>" 
                            <?php echo !$has_isbn ? 'disabled title="Tambahkan kolom isbn dulu di database"' : ''; ?>
                        >
                    </div>

                    <div class="col-6">
                        <label class="form-label">Total Pages</label>
                        <input class="form-control" type="number" name="jumlah_halaman" placeholder="204" min="1" value="<?php echo safe_text($book['jumlah_halaman'] ?? ''); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Synopsis</label>
                        <textarea class="form-control" name="sinopsis" placeholder="Write synopsis here..."><?php echo safe_text($book['sinopsis'] ?? ''); ?></textarea>
                    </div>
                </div>

                <hr class="divider">

                <div class="actions">
                    <a href="books.php" class="btn-cancel">Cancel</a>

                    <button type="submit" class="btn-save">
                        <i class="bi bi-floppy"></i>
                        Save Edit
                    </button>
                </div>
            </section>
        </form>
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

    <script>
        const coverInput = document.getElementById('coverInput');
        const coverPreview = document.getElementById('coverPreview');

        coverInput.addEventListener('change', function() {
            const file = this.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                coverPreview.src = e.target.result;
            };

            reader.readAsDataURL(file);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>