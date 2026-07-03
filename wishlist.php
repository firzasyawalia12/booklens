<?php
// 1. Memulai session di bagian paling atas halaman
session_start();

// 2. LOGIKA OTOMATIS MENANGKAP KLIK DARI TOMBOL BOOKMARK TANPA MENGUBAH FILE LAIN
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    
    // Menangkap data yang dikirimkan melalui URL parameter
    $book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $book_title = isset($_GET['title']) ? urldecode($_GET['title']) : "Untitled Book";
    $book_author = isset($_GET['author']) ? urldecode($_GET['author']) : "Unknown Author";
    $book_cover = isset($_GET['cover']) ? urldecode($_GET['cover']) : "default.png";

    if ($book_id > 0) {
        // Cek duplikasi item di session wishlist agar tidak double
        $is_exist = false;
        foreach ($_SESSION['wishlist'] as $item) {
            if ($item['id'] == $book_id) {
                $is_exist = true;
                break;
            }
        }

        // Jika belum ada di list, masukkan data buku baru ke dalam session
        if (!$is_exist) {
            $_SESSION['wishlist'][] = [
                "id" => $book_id,
                "title" => $book_title,
                "author" => $book_author,
                "cover" => $book_cover,
                "rating" => "5.0", // Nilai default rating tampilan
                "genres" => ["Genres"] // Nilai default genre tampilan
            ];
        }
    }
    
    // Redirect kembali ke wishlist.php agar URL bersih dan rapi
    header("Location: wishlist.php");
    exit();
}

// 3. LOGIKA PROSES HAPUS ITEM DARI WISHLIST (Tombol Sampah Merah)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    if (isset($_SESSION['wishlist'])) {
        foreach ($_SESSION['wishlist'] as $key => $item) {
            if ($item['id'] == $delete_id) {
                unset($_SESSION['wishlist'][$key]);
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']); // Reset urutan index array
                break;
            }
        }
    }
    header("Location: wishlist.php");
    exit();
}

// 4. MENGAMBIL TOTAL DATA DARI SESSION UNTUK DITAMPILKAN
$wishlist_items = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];
$total_wishlist = count($wishlist_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>My Wishlist - BookLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="main.css">
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #ebf3f9; color: #1e293b; }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .wishlist-container {
            flex: 1;
        }

        .footer-wishlist {
            margin-top: auto;
        }
        /* STYLE NAVBAR */
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
        
        .nav-links { display: flex; gap: 24px; margin-left: 48px; margin-right: auto; }
        .nav-links a { text-decoration: none; color: #475569; font-weight: 500; font-size: 0.95rem; }
        .nav-links a:hover { color: #0f172a; }
        .nav-links a.active { color: #0f172a; font-weight: 700; }

        /* AREA UTAMA STRUKTUR WISHLIST */
        .wishlist-container { max-width: 1200px; margin: 80px auto; padding: 0 40px; box-sizing: border-box; }
        .wishlist-header-box { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        
        .total-counter-card { background-color: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 24px; text-align: center; }
        .total-counter-card span { font-size: 0.75rem; font-weight: 700; color: #1e40af; text-transform: uppercase; }
        .total-counter-card h2 { margin: 2px 0 0 0; font-size: 1.6rem; color: #1e3a8a; font-weight: 800; }

        /* GRID STRUKTUR CARD */
        .wishlist-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px; }
        .wishlist-card { background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px; display: flex; flex-direction: column; position: relative; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        
        .card-badge-rating { position: absolute; top: 24px; left: 24px; background: rgba(255, 255, 255, 0.9); padding: 3px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 3px; }
        .wishlist-cover-wrapper { width: 100%; height: 280px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .wishlist-cover-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }

        .wishlist-tags { display: flex; gap: 6px; margin: 12px 0 6px 0; }
        .wishlist-tags .tag-lbl { background-color: #fef08a; color: #854d0e; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        
        .wishlist-action-group { display: flex; gap: 8px; margin-top: 16px; }
        .btn-wishlist-detail { flex: 1; background-color: #1e293b; color: #ffffff; border: none; padding: 9px; font-weight: 600; border-radius: 4px; cursor: pointer; font-size: 0.85rem;}
        .btn-wishlist-delete { background-color: #ffffff; color: #ef4444; border: 1px solid #fca5a5; width: 36px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; cursor: pointer; }

        /* FOOTER */
        .footer-wishlist { background-color: #ebf3f9; padding: 30px 40px; border-top: 1px solid #cbd5e1; margin-top: 60px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; color: #64748b; font-size: 0.85rem; }
        .footer-links a { color: #64748b; text-decoration: none; margin-left: 16px; }

        .explore-footer {
            background-color: #cbd5e1;
            padding: 25px 0;
            font-size: 0.85rem;
            color: #475569;
            flex-shrink: 0;
            width: 100%;
        }

        .explore-footer a {
            color: #475569;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.2s ease;
        }
        
        .explore-footer a:hover {
            color: #0f172a;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light d-flex justify-content-between align-items-center">
        <div class="logo-brand ms-4">
            <a href="home.php" style="text-decoration: none; color: #000000; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="assets/images/ui/boxicons_book.png" alt="BookLens Logo" style="width: 24px; height: 24px;" onerror="this.style.display='none'">
                <span>BookLens</span>
            </a>
        </div>
        <div class="nav-links" style="display: flex; gap: 28px;">
            <a href="home.php" style="text-decoration: none; font-weight: 600;">Home</a>
            <a href="books_user.php" class="nav-item-normal" style="text-decoration: none;">Books</a>
            <a href="wishlist.php" class="nav-item-normal" style="text-decoration: none;">My wishlist</a>
            <a href="reviews.php" class="nav-item-normal" style="text-decoration: none;">My Review</a>
        </div>
        <div class="nav-right-container me-4" style="display: flex; align-items: center; gap: 20px;">
            <div class="nav-search-wrapper" style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
                <input type="text" placeholder="Search for titles, author..." style="padding: 6px 12px 6px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 0.8rem; outline: none; width: 210px; background-color: #ffffff; font-family: 'Poppins', sans-serif;">
            </div>
            
            <<a href="profil_user.php" title="My Profile" style="text-decoration: none;">
    <div class="profile-avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #1e293b;">
        <i class="fa-regular fa-user"></i>
    </div>
</a>
        </div>
    </nav>


<main class="wishlist-container">
    <div class="wishlist-header-box">
        <div>
            <h1 style="margin:0; font-size: 2.2rem; font-weight: 700;">My Wishlist</h1>
            <p style="color:#64748b; margin: 4px 0 0 0;">Capture your insights and reflections as you explore your collection.</p>
        </div>
        <div class="total-counter-card">
            <span>Total Wishlist</span>
            <h2><?php echo $total_wishlist; ?></h2>
        </div>
    </div>

    <div class="wishlist-grid">
        <?php if($total_wishlist > 0): ?>
            <?php foreach($wishlist_items as $book): ?>
            <div class="wishlist-card">
                <div class="card-badge-rating"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> <?php echo $book['rating']; ?></div>
                <div class="wishlist-cover-wrapper">
                    <img src="assets/images/books/<?php echo $book['cover']; ?>" alt="Cover">
                </div>
                <div class="wishlist-tags">
                    <?php foreach($book['genres'] as $genre): ?>
                        <span class="tag-lbl"><?php echo $genre; ?></span>
                    <?php endforeach; ?>
                </div>
                <h3 style="margin:4px 0; font-size:1.1rem; font-weight: 700;"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p style="margin:0; color:#64748b; font-size:0.85rem;"><?php echo htmlspecialchars($book['author']); ?></p>
                
                <div class="wishlist-action-group">
                    <button type="button" class="btn-wishlist-detail" onclick="window.location.href='detail_books.php?id=<?php echo $book['id']; ?>'">Detail</button>
                    <button type="button" class="btn-wishlist-delete" onclick="if(confirm('Hapus buku ini dari wishlist?')) window.location.href='wishlist.php?action=delete&id=<?php echo $book['id']; ?>'">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
      <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 80px 20px; color: #64748b; background: white; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <i class="fa-regular fa-heart" style="font-size: 3rem; margin-bottom: 20px; color: #94a3b8; display: block;"></i>
                <p style="margin: 0 0 10px 0; font-size: 1.1rem; font-weight: 600; color: #0f172a;">Wishlist kamu masih kosong.</p>
                <p style="margin: 0; font-size: 0.9rem; color: #64748b; max-width: 450px; margin-left: auto; margin-right: auto; line-height: 1.6;">Cari buku favoritmu di halaman katalog dan klik tombol bookmark untuk menambahkan!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

   <footer class="explore-footer">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="footer-left">
                <strong>BookLens</strong>
                <p class="m-0" style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">&copy; 2026 BookLens. All rights reserved.</p>
            </div>
            <div class="footer-right">
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms</a>
            </div>
        </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>