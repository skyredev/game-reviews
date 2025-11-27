CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    pass_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_blocked TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS games (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     description TEXT NOT NULL,
     release_year YEAR,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     status ENUM('active', 'pending', 'rejected') NOT NULL DEFAULT 'pending',
     author_id INT NOT NULL,
     cover_full VARCHAR(255),
     cover_thumb VARCHAR(255),
     developer VARCHAR(255),
     publisher VARCHAR(255),
     FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     game_id INT NOT NULL,
     rating INT CHECK (rating >= 1 AND rating <= 10),
     comment TEXT,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
     FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS review_reactions (
     id INT AUTO_INCREMENT PRIMARY KEY,
     review_id INT NOT NULL,
     user_id INT NOT NULL,
     reaction ENUM('like', 'dislike') NOT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
     UNIQUE (review_id, user_id)
);

CREATE TABLE IF NOT EXISTS tags (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(50) UNIQUE NOT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     type ENUM('genre', 'platform') NOT NULL
);

CREATE TABLE IF NOT EXISTS game_tags (
     game_id INT NOT NULL,
     tag_id INT NOT NULL,
     PRIMARY KEY (game_id, tag_id),
     FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
     FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

#test data insert