<?php
// 1. Tangkap ID dari URL (?id=), jika kosong default ke ID 1
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 2. Database internal simulasi 6 buku
$database_buku = [
    1 => [
        "title" => "Hujan",
        "author" => "Tere Liye",
        "cover" => "hujan.png",
        "rating" => "5.0",
        "reviews_count" => "45 Reviews",
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
        "rating" => "5.0",
        "reviews_count" => "18 Reviews",
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
        "rating" => "5.0",
        "reviews_count" => "88 Reviews",
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
        "rating" => "4.8",
        "reviews_count" => "30 Reviews",
        "publisher" => "Gramedia Pustaka Utama",
        "year" => "2023",
        "genre" => "Romance",
        "isbn" => "9786020672748",
        "pages" => "208",
        "synopsis" => "Alina yang membenci seisi hidupnya, berusaha untuk menciptakan realita baru melalui kebohongan yang ia ciptakan dengan menjelma seseorang bernama Marni, ketika ia berkenalan dengan seorang laki-laki yang ia temui di bus pada pukul setengah lima. Apakah kebohongan itu berhasil menyelamatkannya?"
    ],
    5 => [
        "title" => "Dompet Ayah Sepatu Ibu",
        "author" => "J.S. Khairen",
        "cover" => "dompet.png",
        "rating" => "4.9",
        "reviews_count" => "25 Reviews",
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
        "rating" => "5.0",
        "reviews_count" => "52 Reviews",
        "publisher" => "Thought Catalog Books",
        "year" => "2020",
        "genre" => "Self Help / Poetry",
        "isbn" => "9781949759297",
        "pages" => "160",
        "synopsis" => "A Gentle Reminder is a book full of gentle thoughts and poems reminding you to be kind to yourself, to accept your growth, and to realize that you are worthy of being loved deeply. It serves as a warm embrace during your hardest healing days."
    ]
];

// 3. Database internal review khusus untuk masing-masing ID Buku
$database_reviews = [
    1 => [
        ["user" => "Musyira Amalia", "date" => "Juni 07, 2026", "stars" => 5, "text" => "Suka sekali dengan pembawaan alur ceritanya, sangat menyentuh hati dan bikin nangis!"],
        ["user" => "Daeng Aulya Nurfadilah", "date" => "Mei 28, 2026", "stars" => 5, "text" => "Persahabatan Lail dan Esok benar-benar membekas. Plot fiksi ilmiahnya luar biasa rapi."]
    ],
    2 => [
        ["user" => "Firza Syawalia", "date" => "Juni 02, 2026", "stars" => 5, "text" => "Gaya bahasa Ziggy sangat unik dan tidak biasa. Karakter Salva yang polos bikin gemas sekaligus sedih."],
        ["user" => "Nur Aisyah", "date" => "Mei 15, 2026", "stars" => 4, "text" => "Kamus bahasa Indonesia jadi teman setianya, ceritanya penuh makna tersirat yang sangat dalam."]
    ],
    3 => [
        ["user" => "Gitaria", "date" => "Juni 05, 2026", "stars" => 5, "text" => "Nostalgia masa-masa SMA tahun 90an terasa nyata banget. Gombalan Dilan gak pernah gagal bikin senyum-senyum!"],
        ["user" => "Resky Astika", "date" => "April 19, 2026", "stars" => 5, "text" => "Buku paling romantis yang pernah saya baca. Karakter Dilan ikonik sekali."]
    ],
    4 => [
        ["user" => "Andi Thsabitha Hulwa", "date" => "Juni 01, 2026", "stars" => 4, "text" => "Kebohongan Alina sebagai Marni bikin deg-degan sepanjang cerita. Sangat khas tulisan Rintik Sedu."],
        ["user" => "Refina Indah Rahmawati", "date" => "Mei 10, 2026", "stars" => 5, "text" => "Relatable banget buat yang ingin kabur sejenak dari pahitnya realita hidup. Buku yang indah."]
    ],
    5 => [
        ["user" => "Andi Ade Justika", "date" => "Juni 06, 2026", "stars" => 5, "text" => "Kisah perjuangan orang tua yang luar biasa hebat. Sangat direkomendasikan dibaca bersama keluarga."],
        ["user" => "Siti Fadila", "date" => "Mei 22, 2026", "stars" => 5, "text" => "Nangis pas bagian pengorbanan ibunya. J.S. Khairen selalu sukses mengaduk emosi pembaca."]
    ],
    6 => [
        ["user" => "Arvianti", "date" => "Juni 04, 2026", "stars" => 5, "text" => "This book feels like a warm hug on my hardest days. Perfect reminder to love yourself more."],
        ["user" => "Nurfadilah", "date" => "April 30, 2026", "stars" => 5, "text" => "Beautiful poetry and gentle thoughts. Highly recommend it to anyone on a healing journey."]
    ]
];

// 4. Ambil data spesifik sesuai ID, jika ID aneh/tidak ada, kunci ke ID 1
if (array_key_exists($id_buku, $database_buku)) {
    $buku = $database_buku[$id_buku];
    $reviews_buku = $database_reviews[$id_buku];
} else {
    $buku = $database_buku[1];
    $reviews_buku = $database_reviews[1];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $buku['title']; ?> - BookLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./main.css?v=1.2">
</head>
<body>

    <nav class="navbar">
        <div class="logo-brand">
            <a href="index.php" class="brand-link">
                <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo" class="nav-logo-img">
                <span class="brand-text">BookLens</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="books.php">Books</a>
        </div>
        <div class="nav-auth-buttons">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Register</a>
        </div>
    </nav>

    <main class="container detail-section">
        
        <a href="books.php" class="btn-back">
            <i class="fa-solid fa-chevron-left"></i> Back
        </a>

        <div class="book-detail-wrapper">
            
            <div class="detail-left-column">
                <div class="detail-cover-box">
                    <img src="assets/images/books/<?php echo $buku['cover']; ?>" alt="<?php echo $buku['title']; ?>" style="width: 100%; height: auto;">
                </div>
                <button class="btn-action-guest btn-wishlist-disabled" onclick="location.href='login.php'">
                    Log in to Add This Book to Your Wishlist
                </button>
                <button class="btn-action-guest btn-review-disabled" onclick="location.href='login.php'">
                    Log in to Write a Review
                </button>
            </div>

            <div class="detail-right-column">
                <h1 class="book-main-title"><?php echo $buku['title']; ?></h1>
                <p class="book-main-author">by <?php echo $buku['author']; ?></p>

                <div class="detail-rating-wrapper">
                    <div class="stars-group">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <span class="rating-score">(<?php echo $buku['rating']; ?>/5) &bull; <?php echo $buku['reviews_count']; ?></span>
                </div>

                <div class="synopsis-container">
                    <h3>Synopsis</h3>
                    <p><?php echo $buku['synopsis']; ?></p>
                </div>

                <div class="book-spec-grid">
                    <div class="spec-item">
                        <span class="spec-label">Publisher</span>
                        <span class="spec-value"><?php echo $buku['publisher']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Year</span>
                        <span class="spec-value"><?php echo $buku['year']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Genre</span>
                        <span class="spec-tag"><?php echo $buku['genre']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">ISBN</span>
                        <span class="spec-value"><?php echo $buku['isbn']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Pages</span>
                        <span class="spec-value"><?php echo $buku['pages']; ?></span>
                    </div>
                </div>

                <div class="reviews-section">
                    <div class="reviews-header">
                        <h3>User Reviews</h3>
                        <span class="showing-count">Showing <?php echo count($reviews_buku); ?> reviews</span>
                    </div>

                    <div class="review-list">
                        <?php foreach ($reviews_buku as $review) : ?>
                            <div class="review-card">
                                <div class="review-user-info">
                                    <div class="user-avatar-icon"><i class="fa-solid fa-circle-user"></i></div>
                                    <div class="user-meta">
                                        <h4><?php echo $review['user']; ?></h4>
                                        <span><span><?php echo $review['date']; ?></span></span>
                                    </div>
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= $review['stars']; $i++) : ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-text">"<?php echo $review['text']; ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="btn-load-more">Load More Reviews</button>
                </div>

            </div>
        </div>

        <hr class="detail-divider">

        <div class="similar-books-section">
            <h2 class="similar-title">Similar Books</h2>
            <div class="similar-grid">
                <div class="similar-card">
                    <img src="assets/images/books/similar.png" alt="Book">
                    <h4>Title Book</h4>
                    <p>Author Name</p>
                </div>
                <div class="similar-card">
                    <img src="assets/images/books/similar.png" alt="Book">
                    <h4>Title Book</h4>
                    <p>Author Name</p>
                </div>
                <div class="similar-card">
                    <img src="assets/images/books/similar.png" alt="Book">
                    <h4>Title Book</h4>
                    <p>Author Name</p>
                </div>
                <div class="similar-card">
                    <img src="assets/images/books/similar.png" alt="Book">
                    <h4>Title Book</h4>
                    <p>Author Name</p>
                </div>
            </div>
        </div>

    </main>

<footer class="explore-footer">
    <div class="container footer-content">
        <div class="footer-left">
            <strong>BookLens</strong>
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

</body>
</html>