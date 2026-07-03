<?php
// 1. Memulai session untuk mendeteksi status login user
session_start();

// 2. Inisialisasi Koleksi Data Buku Berdasarkan Struktur Data Terbaru Kamu
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

// 3. Tangkap Parameter Filter dan Keyword Pencarian dari URL (HTTP GET)
$search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_genres = isset($_GET['genres']) ? $_GET['genres'] : [];
$selected_rating = isset($_GET['rating']) ? $_GET['rating'] : 'all';

// 4. Logika Proses Penyaringan Data Koleksi Buku
$filtered_books = [];

foreach ($books_collection as $item) {
    // Kriteria A: Filter Keyword Pencarian
    $match_search = false;
    if ($search_query === '') {
        $match_search = true;
    } else {
        if (stripos($item['title'], $search_query) !== false || stripos($item['author'], $search_query) !== false) {
            $match_search = true;
        }
    }

    // Kriteria B: Filter Multi-Genre
    $match_genre = false;
    if (empty($selected_genres)) {
        $match_genre = true; 
    } else {
        foreach ($selected_genres as $genre_filter) {
            foreach ($item['genres'] as $book_genre) {
                if (strcasecmp($genre_filter, $book_genre) === 0 || stripos($book_genre, $genre_filter) !== false) {
                    $match_genre = true;
                    break 2;
                }
            }
        }
    }

    // Kriteria C: Filter Rating
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
    <link rel="stylesheet" href="main.css">
    <style>
        .nav-right-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-search-wrapper {
            position: relative;
            background-color: #f1f5f9;
            border-radius: 20px;
            padding: 6px 12px 6px 32px;
            display: flex;
            align-items: center;
        }
        .nav-search-wrapper .nav-search-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 13px;
        }
        .nav-search-wrapper input {
            border: none;
            background: none;
            outline: none;
            font-size: 12px;
            width: 160px;
            font-family: 'Poppins', sans-serif;
        }
        .logo-brand .brand-link { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            text-decoration: none; 
            color: #0f172a; 
            font-size: 1.4rem; 
            font-weight: 700; 
        }
        .logo-brand .brand-link img { width: 28px; height: 28px; object-fit: contain; }

        #filterAndSearchForm {
            width: 100%;
            display: contents;
        }
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

        /* Layouting Tombol Sesuai Gambar Realistis */
        .card-action-group {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 12px;
            width: 100%;
        }
        
        .card-action-group .btn-detail {
            flex: 1;
            margin-top: 0 !important;
        }

        .btn-card-wishlist {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 0 12px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .btn-card-wishlist:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }
        .nav-links { display: flex; gap: 24px; margin-left: 48px; margin-right: auto; }
        .nav-links a { text-decoration: none; color: #475569; font-weight: 500; font-size: 0.95rem; }
        .nav-links a:hover { color: #0f172a; }
        .nav-links a.active { color: #0f172a; font-weight: 700; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo-brand">
        <a href="home.php" class="brand-link">
            <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo" class="nav-logo-img">
            <span class="brand-text">BookLens</span>
        </a>
    </div>

    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="books_user.php" class="active">Books</a>
        <a href="wishlist.php">My wishlist</a>
        <a href="MyReview.php">My Review</a>
    </div>

    <div class="nav-right-container" style="display: flex; align-items: center; gap: 20px;">
        <div class="nav-search-wrapper" style="position: relative;">
            <i class="fa-solid fa-magnifying-glass nav-search-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
            <input type="text" placeholder="Search for titles, author..." style="padding: 6px 12px 6px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 0.8rem; outline: none; width: 200px;">
        </div>
        <a href="logout.php" class="profile-btn-link" title="Logout" style="color: #1e293b; font-size: 1.2rem;">
            <div class="profile-avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                <i class="fa-regular fa-user"></i>
            </div>
        </a>
    </div>
</nav>

<form id="filterAndSearchForm" method="GET" action="books_user.php">
    
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
                    <?php 
                    $all_genres = ["Fantasy", "Mystery", "Romance", "Horor", "Thriller", "Sci-Fi", "Self Help", "Business", "Drama"];
                    foreach ($all_genres as $g_name): 
                    ?>
                    <label class="checkbox-container">
                        <input type="checkbox" name="genres[]" value="<?php echo $g_name; ?>" <?php echo in_array($g_name, $selected_genres) ? 'checked' : ''; ?> onchange="jalankanSubmitForm()">
                        <span class="checkmark"></span><?php echo $g_name; ?>
                    </label>
                    <?php endforeach; ?>
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

                <button type="button" class="btn-reset" onclick="window.location.href='books_user.php'">Reset</button>
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
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="author-name"><?php echo htmlspecialchars($item['author']); ?></p>
                                
                                <div class="card-action-group">
                                    <button type="button" class="btn-detail" onclick="window.location.href='detail_books.php?id=<?php echo $item['id']; ?>'">Detail</button>
                                    
                                    <button type="button" class="btn-card-wishlist" onclick="tambahKeWishlist(<?php echo $item['id']; ?>, '<?php echo urlencode($item['title']); ?>', '<?php echo urlencode($item['author']); ?>', '<?php echo urlencode($item['cover']); ?>')" title="Add to Wishlist">
                                        <i class="fa-regular fa-bookmark"></i>
                                    </button>
                                </div>
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

function tungguKetikPencarian() {
    clearTimeout(timerPencarian);
    timerPencarian = setTimeout(function() {
        jalankanSubmitForm();
    }, 600);
}

// LOGIKA JAVASCRIPT YANG TELAH DIPERBAIKI UNTUK MENGIRIM DATA KE WISHLIST.PHP
function tambahKeWishlist(idBuku, judul, penulis, cover) {
    alert("Sukses! Buku dimasukkan ke Wishlist.");
    // Secara otomatis mengalihkan browser dengan membawa query parameter ter-encode
    window.location.href = "wishlist.php?action=add&id=" + idBuku + "&title=" + judul + "&author=" + penulis + "&cover=" + cover;
}
</script>

</body>
</html>