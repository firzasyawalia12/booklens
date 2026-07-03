<?php
// 1. Memulai session untuk mempertahankan status login user
session_start();
// ========================================================
// LOGIKA BACKEND: MENERIMA DATA FORM DAN MEMASUKKAN KE SESSION
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating_user = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
    $content_user = isset($_POST['content']) ? trim($_POST['content']) : '';

    if (!empty($content_user)) {
        if (!isset($_SESSION['my_reviews'])) {
            $_SESSION['my_reviews'] = [];
        }

        $new_id = time(); // Membuat ID unik berdasarkan waktu

        // Format data ulasan baru untuk disimpan ke dalam session
        $new_review = [
            "id" => $new_id,
            "book_id" => $id_buku,
            "title" => $buku_terpilih['title'],
            "author" => $buku_terpilih['author'],
            "cover" => $buku_terpilih['cover'],
            "rating" => $rating_user,
            "date" => date('d/m/Y'),
            "content" => htmlspecialchars($content_user)
        ];

        // Masukkan data baru ke baris paling depan session
        array_unshift($_SESSION['my_reviews'], $new_review);

        // Setelah tersimpan, oper (redirect) ke reviews.php
        header("Location: reviews.php");
        exit();
    }
}

// 2. Tangkap ID dari URL (?id=), jika kosong default ke ID 4 (Pukul Setengah Lima) agar sinkron dengan asal halaman
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 4;

// 3. Database internal simulasi yang sama dengan detail_books.php
$database_buku = [
    1 => [
        "title" => "Hujan",
        "author" => "Tere Liye",
        "cover" => "hujan.png",
        "publisher" => "Gramedia Pustaka Utama",
        "year" => "2016",
        "genre" => "Sci-Fi / Romance",
        "isbn" => "9786020324784",
        "pages" => "320",
        "synopsis" => "Tentang persahabatan, tentang cinta, tentang perpisahan, tentang melupakan, dan tentang hujan. Lail baru berusia tiga belas tahun ketika sebuah bencana alam berskala besar menghancurkan bumi dan merenggut keluarganya. Di tengah kehancuran, ia bertemu Esok, pemuda jenius yang menyelamatkannya."
    ],
    2 => [
        "title" => "Di Tanah Lada",
        "author" => "Ziggy Zezsyazeoviennazabrizkie",
        "cover" => "ditanah.png",
        "publisher" => "Gramedia Pustaka Utama",
        "year" => "2015",
        "genre" => "Mystery / Drama",
        "isbn" => "9786020318912",
        "pages" => "244",
        "synopsis" => "Mengisahkan tentang Salva, anak perempuan berumur enam tahun yang memiliki kamus bahasa Indonesia sebagai teman setianya. Ia pindah bersama keluarganya ke sebuah rumah kontrakan di Tanah Lada demi menghindari perlakuan kasar ayahnya. Di sana ia bertemu seseorang bernama P."
    ],
    3 => [
        "title" => "Dilan 1990",
        "author" => "Pidi Baiq",
        "cover" => "dilan.png",
        "publisher" => "Pastel Books",
        "year" => "2014",
        "genre" => "Romance / Drama",
        "isbn" => "9786027870413",
        "pages" => "330",
        "synopsis" => "Milea bertemu dengan Dilan di sebuah SMA di Bandung. Itu adalah tahun 1990, saat Milea pindah dari Jakarta ke Bandung. Perkenalan yang tidak biasa kemudian membawa Milea mulai mengenal keunikan Dilan, panglima tempur geng motor yang pintar, baik hati, dan sangat romantis."
    ],
    4 => [
        "title" => "Pukul Setengah Lima",
        "author" => "Rintik Sedu",
        "cover" => "pukul.png",
        "publisher" => "Gramedia Pustaka Utama",
        "year" => "2023",
        "genre" => "Romance",
        "isbn" => "9786020672748",
        "pages" => "208",
        "synopsis" => "Alina yang membenci seisi hidupnya, berusaha untuk menciptakan realita baru melalui kebohongan yang ia ciptakan dengan menjelma seseorang bernama Marni, ketika ia berkenalan dengan seorang laki-laki yang ia temui di bus pada pukul setengah lima. Apakah kebohongan itu berhasil menyelamatkannya? Atau malah menjatuhkan hatinya pada Marni? Apakah Alina mampu menjaga rahasianya sendiri? Apakah Alina bisa menyukai hidupnya meski dalam sebuah kepalsuan yang sempurna?"
    ],
    5 => [
        "title" => "Dompet Ayah Sepatu Ibu",
        "author" => "J.S. Khairen",
        "cover" => "dompet.png",
        "publisher" => "Bukune",
        "year" => "2020",
        "genre" => "Drama / Family",
        "isbn" => "9786022203520",
        "pages" => "280",
        "synopsis" => "Sebuah novel kisah keluarga yang sangat menyentuh hati. Menceritakan perjuangan seorang ayah demi mengisi dompetnya demi masa depan anak-anaknya, serta kasih sayang dan pengorbanan seorang ibu yang melangkah sejauh mungkin meski menggunakan sepatu usang."
    ],
    6 => [
        "title" => "A Gentle Reminder",
        "author" => "Bianca Sparacino",
        "cover" => "gentle.png",
        "publisher" => "Thought Catalog Books",
        "year" => "2020",
        "genre" => "Self Help / Poetry",
        "isbn" => "9781949759297",
        "pages" => "160",
        "synopsis" => "A Gentle Reminder is a book full of gentle thoughts and poems reminding you to be kind to yourself, to accept your growth, and to realize that you are worthy of being loved deeply. It serves as a warm embrace during your hardest healing days."
    ]
];

// Validasi pengambilan data spesifik sesuai ID
if (array_key_exists($id_buku, $database_buku)) {
    $buku = $database_buku[$id_buku];
} else {
    $buku = $database_buku[4];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Write Review - BookLens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./main.css?v=1.3">
    <style>
        /* Penyesuaian spesifik agar mirip persis dengan screenshot UI */
        body {
            background-color: #f8fafc;
        }
        
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 40px;
        }

        .btn-back-custom {
            text-decoration: none;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 25px;
        }

        .book-info-section {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .book-cover-preview img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .book-title-main {
            font-size: 1.85rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .book-author-sub {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 25px;
        }

        .synopsis-box h3, .write-review-section h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
        }

        .synopsis-box p {
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: justify;
        }

        .mini-spec-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            max-width: 600px;
        }

        .mini-spec-item {
            display: flex;
            flex-direction: column;
        }

        .mini-spec-label {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .mini-spec-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        .genre-badge {
            background-color: #e2e8f0;
            color: #475569;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            display: inline-block;
            width: fit-content;
        }

        /* Form Review Styles */
        .write-review-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .review-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 60px;
        }

        .form-label-custom {
            font-size: 0.95rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .star-rating-input {
            display: flex;
            gap: 6px;
            margin-bottom: 25px;
        }

        .star-rating-input i {
            font-size: 1.4rem;
            color: #e2e8f0; /* Default abu-abu kosong sesuai mockup */
            cursor: pointer;
            transition: color 0.15s;
        }

        /* Kelas bantuan untuk bintang aktif jika nanti diberi interaksi js */
        .star-rating-input i.active, .star-rating-input i:hover {
            color: #f59e0b; 
        }

        .textarea-custom {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            font-size: 0.9rem;
            color: #334155;
            resize: none;
        }

        .textarea-custom::placeholder {
            color: #94a3b8;
        }

        .form-actions-container {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-send-review {
            background-color: #243242;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-send-review:hover {
            background-color: #1a2430;
            color: #ffffff;
        }

        .btn-cancel-review {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
        }

        .btn-cancel-review:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Footer khusus mengikuti nuansa mockup */
        .explore-footer {
            background-color: #cbd5e1; /* Warna biru-keabuan muda */
            padding: 25px 0;
            font-size: 0.85rem;
            color: #475569;
        }

        .explore-footer a {
            color: #475569;
            text-decoration: none;
            margin-left: 20px;
        }
        .side-book-info-block {
            flex: 1;
            min-width: 0; /* Menahan teks agar tidak meluap keluar box flex */
        }

        .side-book-title, .side-book-synopsis {
            word-wrap: break-word; /* Memaksa kata panjang pindah baris */
        }
    </style>
</head>
<body>

    <nav class="navbar d-flex justify-content-between align-items-center">
        <div class="logo-brand">
            <a href="home.php" class="brand-link" style="text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo" style="width: 28px; height: 28px;">
                <span class="brand-text">BookLens</span>
            </a>
        </div>
        <div class="nav-links" style="display: flex; gap: 24px;">
            <a href="home.php" style="text-decoration: none; color: #475569;">Home</a>
            <a href="books_user.php" class="active" style="text-decoration: none; color: #0f172a; font-weight: 700;">Books</a>
            <a href="wishlist.php" style="text-decoration: none; color: #475569;">My wishlist</a>
            <a href="reviews.php" style="text-decoration: none; color: #475569;">My Review</a>
        </div>
        <div class="nav-right-container" style="display: flex; align-items: center; gap: 20px;">
            <div class="nav-search-wrapper" style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
                <input type="text" placeholder="Search for titles, author..." style="padding: 6px 12px 6px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 0.8rem; outline: none; width: 200px;">
            </div>
            <a href="logout.php" title="Logout">
                <div class="profile-avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #1e293b;">
                    <i class="fa-regular fa-user"></i>
                </div>
            </a>
        </div>
    </nav>

    <main class="container mt-4">
        
        <a href="detail_books.php?id=<?php echo $id_buku; ?>" class="btn-back-custom">
            <i class="fa-solid fa-chevron-left"></i> Back
        </a>

        <div class="book-info-section">
            <div class="book-cover-preview">
                <img src="assets/images/books/<?php echo $buku['cover']; ?>" alt="<?php echo $buku['title']; ?>">
            </div>
            <div>
                <h1 class="book-title-main"><?php echo $buku['title']; ?></h1>
                <p class="book-author-sub">by <?php echo $buku['author']; ?></p>
                
                <div class="synopsis-box">
                    <h3>Synopsis</h3>
                    <p><?php echo $buku['synopsis']; ?></p>
                </div>

                <div class="mini-spec-grid">
                    <div class="mini-spec-item">
                        <span class="mini-spec-label">Publisher</span>
                        <span class="mini-spec-value"><?php echo $buku['publisher']; ?></span>
                    </div>
                    <div class="mini-spec-item">
                        <span class="mini-spec-label">Year</span>
                        <span class="mini-spec-value"><?php echo $buku['year']; ?></span>
                    </div>
                    <div class="mini-spec-item">
                        <span class="mini-spec-label">Genre</span>
                        <span class="genre-badge"><?php echo $buku['genre']; ?></span>
                    </div>
                    <div class="mini-spec-item">
                        <span class="mini-spec-label">ISBN</span>
                        <span class="mini-spec-value"><?php echo $buku['isbn']; ?></span>
                    </div>
                    <div class="mini-spec-item">
                        <span class="mini-spec-label">Pages</span>
                        <span class="mini-spec-value"><?php echo $buku['pages']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <hr style="border-color: #cbd5e1; margin-bottom: 40px;">

        <h2 class="write-review-title">Write Review</h2>
        
        <div class="review-form-card">
            <form action="write_review.php?id=<?php echo $id_buku; ?>" method="POST">
                <input type="hidden" name="id_buku" value="<?php echo $id_buku; ?>">

                <div class="mb-4">
                    <label class="form-label-custom">Rating</label>
                    <div class="star-rating-input">
                        <i class="fa-regular fa-star" data-index="1"></i>
                        <i class="fa-regular fa-star" data-index="2"></i>
                        <i class="fa-regular fa-star" data-index="3"></i>
                        <i class="fa-regular fa-star" data-index="4"></i>
                        <i class="fa-regular fa-star" data-index="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" value="5">
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Review Content</label>
                    <textarea name="content" class="form-control textarea-custom" rows="6" placeholder="Share your thought on the characters, plot and writing style...." required></textarea>
                </div>

                <div class="form-actions-container">
                    <button type="submit" class="btn-send-review">Send Review</button>
                    <a href="detail_books.php?id=<?php echo $id_buku; ?>" class="btn-cancel-review">Cancel</a>
                </div>
            </form>
        </div>

    </main>

    <footer class="explore-footer">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="footer-left">
                <strong>BookLens</strong>
                <p class="m-0" style="font-size: 0.8rem; color: #64748b;">&copy; 2026 BookLens. All rights reserved.</p>
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
        const stars = document.querySelectorAll('.star-rating-input i');
        const ratingValueInput = document.getElementById('rating-value');

        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                ratingValueInput.value = index + 1;
                
                stars.forEach((s, idx) => {
                    if (idx <= index) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid');
                        s.style.color = '#f59e0b'; // Warna kuning emas saat dipilih
                    } else {
                        s.classList.remove('fa-solid');
                        s.classList.add('fa-regular');
                        s.style.color = '#e2e8f0'; // Kembali abu-abu kosong
                    }
                });
            });
        });
    </script>
</body>
</html>