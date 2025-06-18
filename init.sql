CREATE DATABASE IF NOT EXISTS video_db;
USE video_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    approved TINYINT(1) DEFAULT 0
);

CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255),
    title VARCHAR(255),
    description TEXT,
    uploaded_by VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100),
    name VARCHAR(255)
);

CREATE TABLE playlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT,
    video_id INT,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);
ALTER TABLE users ADD role ENUM('user', 'admin') DEFAULT 'user';
INSERT INTO users (name, email, password, role)
ALTER TABLE users ADD approved TINYINT(1) DEFAULT 0;
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT,
    user_name VARCHAR(100),
    content TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
