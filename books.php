<?php
// 1. Inisialisasi Koleksi Data Buku Berdasarkan Struktur Data Terbaru Kamu
$books_collection = [
    [
        "id" => 1,
        "title" => "Hujan",
        "author" => "Tere Liye",
        "cover" => "hujan.png",
        "rating" => "5.0",
        "genres" => ["Sci-Fi", "Romance"]
    ],
    [
        "id" => 2,
        "title" => "Di Tanah Lada",
        "author" => "Ziggy Zezsyazeoviennazabrizkie",
        "cover" => "ditanah.png",
        "rating" => "5.0",
        "genres" => ["Mystery", "Drama"]
    ],
    [
        "id" => 3,
        "title" => "Dilan 1990",
        "author" => "Pidi Baiq",
        "cover" => "dilan.png",
        "rating" => "5.0",
        "genres" => ["Romance", "Drama"]
    ],
    [
        "id" => 4,
        "title" => "Pukul Setengah Lima",
        "author" => "Rintik Sedu",
        "cover" => "pukul.png",
        "rating" => "5.0",
        "genres" => ["Romance", "Mystery"]
    ],
    [
        "id" => 5,
        "title" => "Dompet Ayah Sepatu Ibu",
        "author" => "J.S. Khairen",
        "cover" => "dompet.png",
        "rating" => "5.0",
        "genres" => ["Drama", "Family"]
    ],
    [
        "id" => 6,
        "title" => "A Gentle Reminder",
        "author" => "Bianca Sparacino",
        "cover" => "gentle.png",
        "rating" => "5.0",
        "genres" => ["Self Help", "Poetry"]
    ]
];

// 2. Tangkap Parameter Filter dan Keyword Pencarian dari URL (HTTP GET)
$search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_genres = isset($_GET['genres']) ? $_GET['genres'] : [];
$selected_rating = isset($_GET['rating']) ? $_GET['rating'] : 'all';

// 3. Logika Proses Penyaringan Data Koleksi Buku (Case-Insensitive untuk Search & Genre)
$filtered_books = [];

foreach ($books_collection as $item) {
    // Kriteria A: Filter Keyword Pencarian (Judul atau Penulis)
    $match_search = false;
    if ($search_query === '') {
        $match_search = true;
    } else {
        if (stripos($item['title'], $search_query) !== false || stripos($item['author'], $search_query) !== false) {
            $match_search = true;
        }
    }

    // Kriteria B: Filter Multi-Genre dengan Normalisasi Case-Insensitive (strcasecmp / stripos)
    $match_genre = false;
    if (empty($selected_genres)) {
        $match_genre = true; 
    } else {
        foreach ($selected_genres as $genre_filter) {
            foreach ($item['genres'] as $book_genre) {
                // Mencocokkan string secara case-insensitive agar aman meskipun ada perbedaan huruf kapital
                if (strcasecmp($genre_filter, $book_genre) === 0 || stripos($book_genre, $genre_filter) !== false) {
                    $match_genre = true;
                    break 2; // Jika ketemu yang cocok, keluar dari kedua loop filter genre
                }
            }
        }
    }

    // Kriteria C: Filter Batas Nilai Rating
    $match_rating = false;
    $book_rating_float = (float)$item['rating'];
    
    if ($selected_rating === 'all') {
        $match_rating = true;
    } elseif ($selected_rating === '5' && $book_rating_float == 5.0) {
        $match_rating = true;
    } elseif ($selected_rating === '4' && $book_rating_float >= 4.0) {
        $match_rating = true;
    } elseif ($selected_rating === '3' && $book_rating_float >= 3.0) {
        $match_rating = true;
    }

    // Gabungkan seluruh kriteria: Buku harus lolos semua filter aktif
    if ($match_search && $match_genre && $match_rating) {
        $filtered_books[] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Books - BookLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./main.css">
    <style>
        /* Memastikan elemen form bertindak sebagai wrapper transparan tanpa merusak layout CSS kamu */
        #filterAndSearchForm {
            width: 100%;
            display: contents;
        }
        /* Style untuk penanganan card ketika data pencarian/filter kosong */
        .no-data-msg {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            font-size: 1.1rem;
        }
        .btn-reset {
            cursor: pointer;
            width: 100%;
            display: block;
            text-align: center;
        }
    </style>
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
        <a href="books.php" class="active">Books</a>
    </div>

    <div class="nav-auth-buttons">
        <a href="login.php" class="btn-login">Login</a>
        <a href="register.php" class="btn-register">Register</a>
    </div>
</nav>

<form id="filterAndSearchForm" method="GET" action="books.php">
    
    <main class="container explore-section">
        
        <div class="explore-header">
            <div class="header-text">
                <h1>Explore Books</h1>
                <p>Discover thousands of books from various genres and find your next favorite read.</p>
            </div>
            
            <div class="search-box-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="search" placeholder="Search for titles, author..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>" onkeyup="tungguKetikPencarian()">
            </div>
        </div>

        <div class="explore-layout">
            
            <aside class="filter-sidebar">
                <div class="filter-group">
                    <h3><i class="fa-solid fa-layer-group"></i> Genre</h3>
                    
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Fantasy" <?php echo in_array('Fantasy', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Fantasy
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Mystery" <?php echo in_array('Mystery', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Mystery
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Romance" <?php echo in_array('Romance', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Romance
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Horor" <?php echo in_array('Horor', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Horor
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Thriller" <?php echo in_array('Thriller', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Thriller
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Sci-Fi" <?php echo in_array('Sci-Fi', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Sci-Fi
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Self Help" <?php echo in_array('Self Help', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Self Help
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Business" <?php echo in_array('Business', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Business
                    </label>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="Drama" <?php echo in_array('Drama', $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span>Drama
                    </label>
                </div>

                <div class="filter-group">
                    <h3><i class="fa-solid fa-star"></i> Rating</h3>
                    
                    <label class="radio-container">
                        <input type="radio" name="rating" value="all" <?php echo $selected_rating === 'all' ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="radiomark"></span>All Rating
                    </label>
                    <label class="radio-container">
                        <input type="radio" name="rating" value="5" <?php echo $selected_rating === '5' ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="radiomark"></span>5 Star
                    </label>
                    <label class="radio-container">
                        <input type="radio" name="rating" value="4" <?php echo $selected_rating === '4' ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="radiomark"></span>4+ Star
                    </label>
                    <label class="radio-container">
                        <input type="radio" name="rating" value="3" <?php echo $selected_rating === '3' ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="radiomark"></span>3+ Star
                    </label>
                </div>

                <button type="button" class="btn-reset" onclick="window.location.href='books.php'">Reset</button>
            </aside>

            <section class="books-display-area">
                <div class="books-explore-grid">
                
                    <?php if (!empty($filtered_books)): ?>
                        <?php foreach ($filtered_books as $item): ?>
                        <div class="explore-book-card">
                            <span class="badge-rating"><i class="fa-solid fa-star"></i> <?php echo $item['rating']; ?></span>
                            <div class="explore-cover-box">
                                 <img src="assets/images/books/<?php echo $item['cover']; ?>" alt="<?php echo $item['title']; ?>">
                            </div>
                            <div class="explore-book-info">
                                <div class="genres-tags">
                                    <?php foreach ($item['genres'] as $g): ?>
                                        <span class="tag"><?php echo $g; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <h4><?php echo $item['title']; ?></h4>
                                <p class="author-name"><?php echo $item['author']; ?></p>
                                
                                <button type="button" class="btn-detail" onclick="window.open('detail.php?id=<?php echo $item['id']; ?>', '_blank')">Detail</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data-msg">
                            <p><i class="fa-regular fa-folder-open" style="font-size: 2rem; display:block; margin-bottom:10px;"></i> Maaf, tidak ada buku yang sesuai dengan kriteria filter atau pencarian Anda.</p>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="pagination">
                    <a href="#" class="page-arrow"><i class="fa-solid fa-chevron-left"></i></a>
                    <a href="#" class="page-num active">1</a>
                    <a href="#" class="page-num">2</a>
                    <span class="page-dots">...</span>
                    <a href="#" class="page-num">5</a>
                    <a href="#" class="page-num"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </section>

        </div>
    </main>

</form>

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

<script>
let timerPencarian;

function jalankanSubmitForm() {
    document.getElementById('filterAndSearchForm').submit();
}

// Fungsi debounce agar sistem tidak memuat ulang halaman setiap kali tombol keyboard ditekan (menunggu ketikan jeda sejenak)
function tungguKetikPencarian() {
    clearTimeout(timerPencarian);
    timerPencarian = setTimeout(function() {
        jalankanSubmitForm();
    }, 600); // Menunggu user berhenti mengetik selama 600ms, lalu jalankan submit otomatis
}
</script>

</body>
</html>