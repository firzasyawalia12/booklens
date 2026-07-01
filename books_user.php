<?php
// Masukkan koneksi database kamu di sini
// include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLens - Explore Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }

        /* --- NAVBAR STYLING --- */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-link {
            color: #475569;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            color: #0f172a;
        }
        .search-container {
            position: relative;
            width: 300px;
        }
        .search-container input {
            background-color: #f1f5f9;
            border: none;
            border-radius: 8px;
            padding: 8px 16px 8px 40px;
            font-size: 0.9rem;
        }
        .search-container .bi-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* --- SIDEBAR FILTER STYLING --- */
        .filter-sidebar {
            background-color: #eff6ff; /* Warna background biru muda khas figma */
            border-radius: 12px;
            padding: 24px;
        }
        .filter-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-section-label {
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 20px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-check-label {
            font-size: 0.9rem;
            color: #334155;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: #1e293b;
            border-color: #1e293b;
        }
        .btn-reset {
            background-color: #334155;
            color: #ffffff;
            border: none;
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 25px;
            transition: background-color 0.2s;
        }
        .btn-reset:hover {
            background-color: #1e293b;
            color: #ffffff;
        }

        /* --- BOOK CARD STYLING --- */
        .book-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .book-img-container {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4;
            background-color: #f1f5f9;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .book-img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .rating-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .book-details {
            padding: 16px;
            background-color: #eff6ff; /* Sisi bawah kartu berwarna soft blue */
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .genre-badge-container {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .genre-badge {
            background-color: #fef08a; /* Kuning figma */
            color: #854d0e;
            font-size: 0.75rem;
            font-weight: 400 !important; /* Kunci: Tidak bold sesuai request sebelumnya */
            padding: 3px 8px;
            border-radius: 4px;
        }
        .book-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.3;
        }
        .book-author {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 15px;
        }
        .book-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        .btn-detail {
            background-color: #1e293b;
            color: #ffffff;
            border: none;
            flex-grow: 1;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 6px;
            border-radius: 4px;
        }
        .btn-detail:hover {
            background-color: #0f172a;
            color: #ffffff;
        }
        .btn-wishlist {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 6px 10px;
            border-radius: 4px;
        }
        .btn-wishlist:hover {
            background-color: #f1f5f9;
        }

        /* --- PAGINATION STYLING --- */
        .pagination .page-link {
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            font-size: 0.9rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #1e293b;
            border-color: #1e293b;
            color: #ffffff;
        }

        /* --- FOOTER STYLING --- */
        footer {
            background-color: #bfdbfe; /* Soft blue footer figma */
            padding: 20px 0;
            font-size: 0.85rem;
            color: #475569;
            margin-top: 80px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <i class="bi bi-book-half" style="font-size: 1.4rem;"></i> BookLens
            </a>
            <button class="navbar-collapse collapse navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4 gap-3">
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="books_user.php">Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="wishlist.php">My wishlist</a></li>
                    <li class="nav-item"><a class="nav-link" href="review.php">My Review</a></li>
                </ul>
                <div class="d-flex align-items-center gap-4">
                    <div class="search-container">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Search for titles, author...">
                    </div>
                    <a href="profile.php">
                        <img src="assets/images/ui/avatar.jpg" alt="Profile" class="profile-avatar" onerror="this.src='https://via.placeholder.com/150'">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="mb-4">
            <h1 class="fw-bold" style="font-size: 2.2rem; color: #0f172a;">Explore Books</h1>
            <p class="text-muted" style="font-size: 0.95rem;">Discover thousands of books from various genres and find your next favorite read.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4 col-lg-3">
                <form method="GET" action="books_user.php" class="filter-sidebar">
                    <div class="filter-title">
                        <i class="bi bi-sliders"></i> Genre
                    </div>
                    
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $genres = ['Fantasy', 'Mystery', 'Romance', 'Horor', 'Thriller', 'Sci-Fi', 'Self Help', 'Business'];
                        foreach($genres as $g) {
                            echo '
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="genre[]" value="'.$g.'" id="genre_'.$g.'">
                                <label class="form-check-label" for="genre_'.$g.'">'.$g.'</label>
                            </div>';
                        }
                        ?>
                    </div>

                    <div class="filter-section-label">
                        <i class="bi bi-star"></i> Rating
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rating" value="all" id="rate_all" checked>
                            <label class="form-check-label" for="rate_all">All Rating</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rating" value="5" id="rate_5">
                            <label class="form-check-label" for="rate_5">5 Star</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rating" value="4" id="rate_4">
                            <label class="form-check-label" for="rate_4">4+ Star</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rating" value="3" id="rate_3">
                            <label class="form-check-label" for="rate_3">3+ Star</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rating" value="2" id="rate_2">
                            <label class="form-check-label" for="rate_2">2+ Star</label>
                        </div>
                    </div>

                    <button type="reset" class="btn btn-reset">Reset</button>
                </form>
            </div>

            <div class="col-12 col-md-8 col-lg-9">
                <div class="row g-4">
                    
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="book-card">
                            <div class="book-img-container">
                                <span class="rating-badge"><i class="bi bi-star-fill text-warning"></i> 5.0</span>
                                <img src="assets/images/books/hujan.jpg" alt="Cover Buku" onerror="this.src='https://via.placeholder.com/150x200?text=Cover+Buku'">
                            </div>
                            <div class="book-details">
                                <div class="genre-badge-container">
                                    <span class="genre-badge">Sci-Fi</span>
                                    <span class="genre-badge">Romance</span>
                                </div>
                                <h3 class="book-title">Hujan</h3>
                                <div class="book-author">Tere Liye</div>
                                <div class="book-actions">
                                    <button class="btn-detail" onclick="location.href='detail.php?id=1'">Detail</button>
                                    <button class="btn-wishlist"><i class="bi bi-bookmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="book-card">
                            <div class="book-img-container">
                                <span class="rating-badge"><i class="bi bi-star-fill text-warning"></i> 5.0</span>
                                <img src="assets/images/books/tanah_lada.jpg" alt="Cover Buku" onerror="this.src='https://via.placeholder.com/150x200?text=Cover+Buku'">
                            </div>
                            <div class="book-details">
                                <div class="genre-badge-container">
                                    <span class="genre-badge">Mystery</span>
                                    <span class="genre-badge">Drama</span>
                                </div>
                                <h3 class="book-title">Di Tanah Lada</h3>
                                <div class="book-author">Ziggy Zezsyazeoviennazabrizkie</div>
                                <div class="book-actions">
                                    <button class="btn-detail" onclick="location.href='detail.php?id=2'">Detail</button>
                                    <button class="btn-wishlist"><i class="bi bi-bookmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="book-card">
                            <div class="book-img-container">
                                <span class="rating-badge"><i class="bi bi-star-fill text-warning"></i> 5.0</span>
                                <img src="assets/images/books/dilan.jpg" alt="Cover Buku" onerror="this.src='https://via.placeholder.com/150x200?text=Cover+Buku'">
                            </div>
                            <div class="book-details">
                                <div class="genre-badge-container">
                                    <span class="genre-badge">Romance</span>
                                    <span class="genre-badge">Drama</span>
                                </div>
                                <h3 class="book-title">Dilan 1990</h3>
                                <div class="book-author">Pidi Baiq</div>
                                <div class="book-actions">
                                    <button class="btn-detail" onclick="location.href='detail.php?id=3'">Detail</button>
                                    <button class="btn-wishlist"><i class="bi bi-bookmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>

                <nav class="d-flex justify-content-center mt-5">
                    <ul class="pagination align-items-center gap-1">
                        <li class="page-item"><a class="page-link border-0" href="#"><i class="bi bi-chevron-left"></i></a></li>
                        <li class="page-item active"><a class="page-link rounded" href="#">1</a></li>
                        <li class="page-item"><a class="page-link rounded" href="#">2</a></li>
                        <li class="page-item disabled"><span class="page-link border-0">...</span></li>
                        <li class="page-item"><a class="page-link rounded" href="#">5</a></li>
                        <li class="page-item"><a class="page-link border-0" href="#"><i class="bi bi-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <footer>
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                <strong>BookLens</strong><br>
                <span class="text-secondary" style="font-size: 0.8rem;">© 2026 BookLens. All rights reserved.</span>
            </div>
            <div class="d-flex gap-4">
                <a href="#" class="text-decoration-none text-secondary">About</a>
                <a href="#" class="text-decoration-none text-secondary">Contact</a>
                <a href="#" class="text-decoration-none text-secondary">Privacy Policy</a>
                <a href="#" class="text-decoration-none text-secondary">Terms</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>