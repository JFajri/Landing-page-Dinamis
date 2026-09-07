-- =====================================================================
-- Migration: Galeri, Kalender/Event, Aspirasi & Pengaduan
-- Jalankan file ini SEKALI lewat phpMyAdmin/CLI HANYA JIKA website Anda
-- sudah pernah diinstall sebelumnya (sebelum 3 fitur ini ditambahkan).
-- Aman dijalankan berkali-kali (CREATE TABLE IF NOT EXISTS).
-- Instalasi BARU tidak perlu file ini — installer sudah membuat tabel
-- ini otomatis lewat database/schema.sql yang sudah diperbarui.
-- =====================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS gallery (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image       VARCHAR(255) NOT NULL,
    caption     VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_gallery_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    event_date  DATE NOT NULL,
    event_time  TIME DEFAULT NULL,
    location    VARCHAR(150) DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_events_date_status (event_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS complaints (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    contact    VARCHAR(150) NOT NULL,
    kelurahan  VARCHAR(100) DEFAULT NULL,
    message    TEXT NOT NULL,
    status     ENUM('baru','dibaca','diproses','selesai') NOT NULL DEFAULT 'baru',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_complaints_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
