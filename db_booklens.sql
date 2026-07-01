-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 02:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE DATABASE IF NOT EXISTS db_booklens;
USE db_booklens;

-- =====================================================
-- TABEL ROLE
-- =====================================================

CREATE TABLE mst_role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO mst_role (id_role, nama_role)
VALUES
(1, 'admin'),
(2, 'user');

-- =====================================================
-- TABEL USERS
-- =====================================================

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    id_role INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role
        FOREIGN KEY (id_role)
        REFERENCES mst_role(id_role)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- AKUN ADMIN DEFAULT
-- Username : Firza Syawalia
-- Password : firza123
-- =====================================================

INSERT INTO users (
    id_role,
    nama,
    username,
    email,
    password
)
VALUES (
    1,
    'Firza Syawalia',
    'admin',
    'firza@booklens.com',
    'firza123'
);

-- =====================================================
-- TABEL BOOKS
-- =====================================================

CREATE TABLE books (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(150) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    isbn VARCHAR(20) DEFAULT NULL,
    penerbit VARCHAR(100) DEFAULT NULL,
    tahun_terbit YEAR DEFAULT NULL,
    jumlah_halaman INT DEFAULT NULL,
    sinopsis TEXT DEFAULT NULL,
    cover VARCHAR(255) DEFAULT NULL,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL RATINGS
-- =====================================================

CREATE TABLE ratings (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    nilai_rating TINYINT NOT NULL CHECK (nilai_rating BETWEEN 1 AND 5),
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_rating_user_buku (id_user, id_buku),

    CONSTRAINT fk_rating_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE,

    CONSTRAINT fk_rating_buku
        FOREIGN KEY (id_buku)
        REFERENCES books(id_buku)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL REVIEWS
-- =====================================================

CREATE TABLE reviews (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    isi_review TEXT NOT NULL,
    tanggal_review DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE,

    CONSTRAINT fk_review_buku
        FOREIGN KEY (id_buku)
        REFERENCES books(id_buku)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL WISHLIST
-- =====================================================

CREATE TABLE wishlist (
    id_wishlist INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_ditambahkan DATE NOT NULL,

    UNIQUE KEY uk_wishlist_user_buku (id_user, id_buku),

    CONSTRAINT fk_wishlist_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE,

    CONSTRAINT fk_wishlist_buku
        FOREIGN KEY (id_buku)
        REFERENCES books(id_buku)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
