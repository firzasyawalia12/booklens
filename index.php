<?php
// Memulai session untuk mengecek jika suatu saat ada perpindahan status, 
// tapi layout ini fokus 100% pada tampilan Visitor/Guest.
session_start();
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLens - Your Personal Space for Book Discovery and Reviews</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="main.css">
    <style>
        /* CSS tambahan agar kartu buku memiliki efek pointer dan transisi halus saat diarahkan */
        .book-item-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .book-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        <a href="index.php" class="active">Home</a>
        <a href="books.php">Books</a>
    </div>

    <div class="nav-auth-buttons">
        <a href="login.php" class="btn-login">Login</a>
        <a href="register.php" class="btn-register">Register</a>
    </div>
</nav>

    <header class="hero-section">
        <div class="hero-content">
            <h1>Your Personal Space for Book Discovery and Reviews</h1>
            <p>Find books that inspire you, explore their details, and preserve your reading experience through reviews and ratings.</p>
            <form action="books.php" method="GET" class="search-bar-form">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Search for titles, author or genre...">
                </div>
            </form>
        </div>
    </header>

    <section class="container section-padding">
        <h2 class="section-title">Book Categories</h2>
        <div class="category-grid">
            <div class="category-card card-fantasy" onclick="location.href='books.php?genre=Fantasy'">
                <h3>FANTASY</h3>
            </div>
            <div class="category-card card-mystery" onclick="location.href='books.php?genre=Mystery'">
                <h3>MYSTERY</h3> 
            </div>
            <div class="category-card card-romance" onclick="location.href='books.php?genre=Romance'">
                <h3>ROMANCE</h3>
            </div>
            <div class="category-card card-horor" onclick="location.href='books.php?genre=Horor'">
                <h3>HOROR</h3>
            </div>
            <div class="category-card card-thriller" onclick="location.href='books.php?genre=Thriller'">
                <h3>THRILLER</h3>
            </div>
            <div class="category-card card-scific" onclick="location.href='books.php?genre=Sci-Fi'">
                <h3>SCI-FIC</h3>
            </div>
            <div class="category-card card-selfhelp" onclick="location.href='books.php?genre=Self Help'">
                <h3>SELF HELP</h3>
            </div>
            <div class="category-card card-business" onclick="location.href='books.php?genre=Business'">
                <h3>BUSINESS</h3>
            </div>
        </div>
    </section>

    <section class="container section-padding">
    <h2 class="section-title">Top Rated Books</h2>
    
    <div class="books-scroll-grid">
        <div class="book-item-card" onclick="window.open('detail.php?id=1', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/hujan.png" alt="Hujan" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>Hujan</h4>
                <p class="author">Tere Liye</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>

        <div class="book-item-card" onclick="window.open('detail.php?id=2', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/ditanah.png" alt="Di Tanah Lada" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>Di Tanah Lada</h4>
                <p class="author">Ziggy Zezsyazeoviennazabrizkie</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>

        <div class="book-item-card" onclick="window.open('detail.php?id=3', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/dilan.png" alt="Dilan 1990" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>Dilan 1990</h4>
                <p class="author">Pidi Baiq</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>

        <div class="book-item-card" onclick="window.open('detail.php?id=4', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/pukul.png" alt="Pukul Setengah Lima" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>Pukul Setengah Lima</h4>
                <p class="author">Rintik Sedu</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>

        <div class="book-item-card" onclick="window.open('detail.php?id=5', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/dompet.png" alt="Dompet Ayah Sepatu Ibu" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>Dompet Ayah Sepatu Ibu</h4>
                <p class="author">J.S. Khairen</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>

        <div class="book-item-card" onclick="window.open('detail.php?id=6', '_blank')">
            <div class="book-cover-box">
                <img src="assets/images/books/gentle.png" alt="A Gentle Reminder" class="book-cover-img">
            </div>
            <div class="book-info">
                <h4>A Gentle Reminder</h4>
                <p class="author">Bianca Sparacino</p>
                <p class="rating"><i class="fa-solid fa-star text-warning"></i> 5.0</p>
            </div>
        </div>
    </div>
</section>

    <section class="container section-padding text-center">
        <h2 class="features-main-title">Everything You Need</h2>
        <p class="features-sub-title">Designed to help you discover books, organize your personal collection and share your thoughts through ratings and reviews.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-box"><i class="fa-solid fa-compass"></i></div>
                <h3>Find a Books</h3>
                <p>Browse books from various genres and explore detailed information, summaries, and recommendations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-box"><i class="fa-solid fa-book-bookmark"></i></div>
                <h3>Manage Your Collection</h3>
                <p>Save books to your personal collection and keep your favorite titles organized in one place.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-box"><i class="fa-solid fa-star-half-stroke"></i></div>
                <h3>Rate Books</h3>
                <p>Give ratings to books you've read and help highlight the most appreciated titles.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-box"><i class="fa-solid fa-pen-to-square"></i></div>
                <h3>Write Reviews</h3>
                <p>Share your opinions, impressions, and insights about books through detailed reviews.</p>
            </div>
        </div>
    </section>

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