<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = "";

function safe_text($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function upload_cover_buku($file, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return "";
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Cover gagal diupload.";
        return "";
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $error = "Ukuran cover maksimal 5MB.";
        return "";
    }

    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        $error = "Format cover harus JPG atau PNG.";
        return "";
    }

    $upload_dir = "../assets/images/books/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_name = "book_" . time() . "_" . rand(1000, 9999) . "." . $ext;
    $target = $upload_dir . $new_name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        $error = "Cover gagal disimpan ke folder assets/images/books.";
        return "";
    }

    return $new_name;
}

$cek_isbn = mysqli_query($koneksi, "SHOW COLUMNS FROM books LIKE 'isbn'");
$punya_kolom_isbn = $cek_isbn && mysqli_num_rows($cek_isbn) > 0;

if (isset($_POST['save'])) {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $jumlah_halaman = trim($_POST['jumlah_halaman'] ?? '');
    $sinopsis = trim($_POST['sinopsis'] ?? '');

    $tahun_terbit = $tahun_terbit !== "" ? (int)$tahun_terbit : 0;
    $jumlah_halaman = $jumlah_halaman !== "" ? (int)$jumlah_halaman : 0;

    $cover = upload_cover_buku($_FILES['cover'], $error);

    if ($judul === "" || $penulis === "" || $genre === "") {
        $error = "Book title, author name, dan genre wajib diisi.";
    }

    if ($error === "") {
        if ($punya_kolom_isbn) {
            $query = "INSERT INTO books 
                      (judul, penulis, genre, isbn, penerbit, tahun_terbit, jumlah_halaman, sinopsis, cover)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($koneksi, $query);

            if (!$stmt) {
                $error = "Prepare gagal: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssiiss",
                    $judul,
                    $penulis,
                    $genre,
                    $isbn,
                    $penerbit,
                    $tahun_terbit,
                    $jumlah_halaman,
                    $sinopsis,
                    $cover
                );
            }
        } else {
            $query = "INSERT INTO books 
                      (judul, penulis, genre, penerbit, tahun_terbit, jumlah_halaman, sinopsis, cover)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($koneksi, $query);

            if (!$stmt) {
                $error = "Prepare gagal: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssiiss",
                    $judul,
                    $penulis,
                    $genre,
                    $penerbit,
                    $tahun_terbit,
                    $jumlah_halaman,
                    $sinopsis,
                    $cover
                );
            }
        }

        if ($error === "") {
            if ($stmt && mysqli_stmt_execute($stmt)) {
                header("Location: books.php");
                exit();
            } else {
                $error = "Data buku gagal disimpan: " . mysqli_error($koneksi);
            }
        }
    }
}

$genre_list = [
    "Fantasy",
    "Mystery",
    "Romance",
    "Horor",
    "Thriller",
    "Sci-Fi",
    "Self Help",
    "Business",
    "Drama",
    "Family"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1280">
    <title>Add New Book - BookLens</title>

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
            background: #eef9fd;
            color: #142536;
            min-height: 100vh;
            min-width: 1280px;
            display: flex;
            flex-direction: column;
        }

        .add-book-page {
            padding: 32px 47px 46px;
            flex: 1;
        }

        .breadcrumb-text {
            font-size: 13px;
            font-weight: 600;
            color: #142536;
            margin-bottom: 29px;
        }

        .breadcrumb-text a {
            color: #142536;
            text-decoration: none;
        }

        .breadcrumb-text a:hover {
            text-decoration: underline;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h1 {
            font-size: 31px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #142536;
            margin-bottom: 10px;
        }

        .page-heading p {
            font-size: 16px;
            line-height: 1.55;
            color: #444b54;
            letter-spacing: 0.3px;
            max-width: 760px;
            margin-bottom: 0;
        }

        .custom-alert {
            width: 1180px;
            border-radius: 2px;
            padding: 12px 15px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .book-form-layout {
            display: grid;
            grid-template-columns: 264px 890px;
            gap: 24px;
            align-items: start;
        }

        .cover-box {
            width: 264px;
            min-height: 520px;
            background: #ffffff;
            border: 1px solid #c5ccd4;
            padding: 26px 24px 28px;
        }

        .cover-box > label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #3f4650;
            letter-spacing: 0.8px;
            margin-bottom: 17px;
        }

        .upload-area {
            width: 100%;
            height: 371px;
            border: 2px dashed #c8cdd5;
            background: #f7f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 3;
        }

        #preview-cover {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 2;
        }

        #upload-placeholder {
            text-align: center;
            color: #142536;
            padding: 18px;
        }

        #upload-placeholder i {
            display: block;
            font-size: 32px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        #upload-placeholder strong {
            display: block;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.7px;
            margin-bottom: 6px;
        }

        #upload-placeholder span {
            display: block;
            font-size: 12px;
            color: #4b5563;
            line-height: 1.4;
        }

        .cover-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 15px;
            font-size: 12px;
            color: #3f4650;
            font-style: italic;
            line-height: 1.35;
        }

        .cover-note i {
            margin-top: 2px;
            font-size: 13px;
        }

        .form-card {
            width: 890px;
            min-height: 640px;
            background: #ffffff;
            border: 1px solid #c5ccd4;
            padding: 32px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #454c54;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            width: 100%;
            height: 51px;
            border: 1px solid #c7ccd4;
            background-color: #f8f9fb;
            color: #142536;
            border-radius: 0;
            font-size: 15px;
            letter-spacing: 0.5px;
            box-shadow: none;
            padding: 0 16px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #142536;
            background-color: #ffffff;
            box-shadow: none;
        }

        textarea.form-control {
            height: 146px;
            resize: none;
            padding: 14px 16px;
            line-height: 1.5;
        }

        .form-control::placeholder,
        textarea.form-control::placeholder {
            color: #747d8c;
        }

        .form-action {
            border-top: 1px solid #c7ccd4;
            margin-top: 32px;
            padding-top: 31px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
        }

        .btn-cancel,
        .btn-save {
            height: 46px;
            min-width: 118px;
            padding: 0 28px;
            border-radius: 2px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1.2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-cancel {
            background: #ffffff;
            border: 1px solid #41586a;
            color: #41586a;
        }

        .btn-cancel:hover {
            background: #f8f9fb;
            border-color: #41586a;
            color: #41586a;
        }

        .btn-save {
            background: #142536;
            border: 1px solid #142536;
            color: #ffffff;
            gap: 8px;
        }

        .btn-save:hover {
            background: #0d1b29;
            border-color: #0d1b29;
            color: #ffffff;
        }

        .main-footer {
            background-color: #b8d3ea;
            padding: 25px 50px;
            margin-top: auto;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left h4 {
            font-size: 16px;
            font-weight: 500;
            color: #142536;
            margin-bottom: 5px;
        }

        .footer-left p {
            font-size: 15px;
            color: #687582;
            letter-spacing: 0.5px;
            margin-bottom: 0;
        }

        .footer-right {
            display: flex;
            gap: 19px;
        }

        .footer-right a {
            text-decoration: none;
            color: #5b6773;
            font-size: 15px;
            font-weight: 400;
        }
    </style>
</head>
<body>

<main class="add-book-page">
    <section class="breadcrumb-text">
        <a href="books.php">Manage Books</a> &gt; Add Book
    </section>

    <section class="page-heading">
        <h1>Add New Book</h1>
        <p>
            Expand the collective knowledge of the BookLens community. Please provide accurate<br>
            details for the new addition to the archive.
        </p>
    </section>

    <?php if ($error) : ?>
        <div class="alert alert-danger custom-alert">
            <?php echo safe_text($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="book-form-layout">
        <aside class="cover-box">
            <label>Book Cover</label>

            <div class="upload-area">
                <img id="preview-cover" src="" alt="Preview Cover">

                <div id="upload-placeholder">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    <strong>Upload High-Res Cover</strong>
                    <span>JPG or PNG, max 5MB<br>Recommended ratio 2:3</span>
                </div>

                <input type="file" name="cover" id="cover" accept="image/png,image/jpeg">
            </div>

            <p class="cover-note">
                <i class="bi bi-info-circle"></i>
                <span>Cover art should clearly show the title and author for archival quality.</span>
            </p>
        </aside>

        <section class="form-card">
            <div class="row g-4">
                <div class="col-6">
                    <label class="form-label">Book Title</label>
                    <input class="form-control" type="text" name="judul" placeholder="e.g. Pukul Setengah Lima" required>
                </div>

                <div class="col-6">
                    <label class="form-label">Author Name</label>
                    <input class="form-control" type="text" name="penulis" placeholder="e.g. Rintik Sedu" required>
                </div>

                <div class="col-4">
                    <label class="form-label">Publisher</label>
                    <input class="form-control" type="text" name="penerbit" placeholder="Gramedia Pusta...">
                </div>

                <div class="col-4">
                    <label class="form-label">Publication Year</label>
                    <input class="form-control" type="number" name="tahun_terbit" placeholder="2023" min="1000" max="2099">
                </div>

                <div class="col-4">
                    <label class="form-label">Genre</label>
                    <select class="form-select" name="genre" required>
                        <option value="">Select Genre</option>
                        <?php foreach ($genre_list as $genre) : ?>
                            <option value="<?php echo safe_text($genre); ?>">
                                <?php echo safe_text($genre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">ISBN-13</label>
                    <input class="form-control" type="text" name="isbn" placeholder="xx-xxx-xxxx-xx">
                </div>

                <div class="col-6">
                    <label class="form-label">Total Pages</label>
                    <input class="form-control" type="number" name="jumlah_halaman" placeholder="204">
                </div>

                <div class="col-12">
                    <label class="form-label">Synopsis</label>
                    <textarea class="form-control" name="sinopsis" placeholder="Write synopsis here..."></textarea>
                </div>
            </div>

            <div class="form-action">
                <a href="books.php" class="btn-cancel">Cancel</a>
                <button type="submit" name="save" class="btn-save">
                    <i class="bi bi-floppy"></i> Save Book
                </button>
            </div>
        </section>
    </form>
</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-left">
            <h4>BookLens</h4>
            <p>&copy; 2026 BookLens. All rights reserved.</p>
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
    const coverInput = document.getElementById('cover');
    const previewCover = document.getElementById('preview-cover');
    const uploadPlaceholder = document.getElementById('upload-placeholder');

    coverInput.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (event) {
                previewCover.src = event.target.result;
                previewCover.style.display = 'block';
                uploadPlaceholder.style.display = 'none';
            };

            reader.readAsDataURL(file);
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>