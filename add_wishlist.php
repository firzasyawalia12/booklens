<?php
// 1. Memulai session agar bisa menyimpan data ke wishlist
session_start();

// 2. Tangkap ID buku yang dikirim dari halaman detail_books.php
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. Database internal penyeimbang untuk mengambil info lengkap buku berdasarkan ID
$database_buku = [
    1 => [
        "title" => "Hujan",
        "author" => "Tere Liye",
        "cover" => "hujan.png",
        "rating" => "5.0",
        "genres" => ["Sci-Fi", "Romance"]
    ],
    2 => [
        "title" => "Di Tanah Lada",
        "author" => "Ziggy Zezsyazeoviennazabrizkie",
        "cover" => "ditanah.png",
        "rating" => "5.0",
        "genres" => ["Mystery", "Drama"]
    ],
    3 => [
        "title" => "Dilan 1990",
        "author" => "Pidi Baiq",
        "cover" => "dilan.png",
        "rating" => "5.0",
        "genres" => ["Romance", "Drama"]
    ],
    4 => [
        "title" => "Pukul Setengah Lime",
        "author" => "Rintik Sedu",
        "cover" => "pukul.png",
        "rating" => "4.8",
        "genres" => ["Romance", "Mystery"]
    ],
    5 => [
        "title" => "Dompet Ayah Sepatu Ibu",
        "author" => "J.S. Khairen",
        "cover" => "dompet.png",
        "rating" => "4.9",
        "genres" => ["Drama", "Family"]
    ],
    6 => [
        "title" => "A Gentle Reminder",
        "author" => "Bianca Sparacino",
        "cover" => "gentle.png",
        "rating" => "5.0",
        "genres" => ["Self Help", "Poetry"]
    ]
];

// 4. Proses memasukkan data ke dalam Session Wishlist
if ($id_buku > 0 && isset($database_buku[$id_buku])) {
    // Inisialisasi array wishlist jika belum ada
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }

    $buku_dipilih = $database_buku[$id_buku];

    // Cek apakah buku sudah ada di dalam wishlist agar tidak duplikat
    $sudah_ada = false;
    foreach ($_SESSION['wishlist'] as $item) {
        if ($item['id'] == $id_buku) {
            $sudah_ada = true;
            break;
        }
    }

    // Jika belum ada di list wishlist, tambahkan data lengkapnya
    if (!$sudah_ada) {
        $_SESSION['wishlist'][] = [
            "id" => $id_buku,
            "title" => $buku_dipilih['title'],
            "author" => $buku_dipilih['author'],
            "cover" => $buku_dipilih['cover'],
            "rating" => $buku_dipilih['rating'],
            "genres" => $buku_dipilih['genres']
        ];
    }
}

// 5. Alihkan halaman langsung ke wishlist.php untuk melihat hasilnya
header("Location: wishlist.php");
exit();
?>