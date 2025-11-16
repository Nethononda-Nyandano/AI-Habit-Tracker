-- USERS TABLE
CREATE TABLE habitdb.users
(
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)        NOT NULL,
    surname       VARCHAR(100)        NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    phone         VARCHAR(20),
    password_hash VARCHAR(255)        NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE habitdb.addresses
(
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    street     VARCHAR(255),
    city       VARCHAR(100),
    state      VARCHAR(100),
    zip_code   VARCHAR(20),
    country    VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- HABITS TABLE
CREATE TABLE habitdb.habits
(
    habit_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    habit_name  VARCHAR(150) NOT NULL,
    description TEXT,
    frequency   ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- HABIT LOGS (daily tracking of habits)
CREATE TABLE habitdb.habit_logs
(
    log_id   INT AUTO_INCREMENT PRIMARY KEY,
    habit_id INT  NOT NULL,
    log_date DATE NOT NULL,
    status   ENUM('done', 'missed', 'skipped') DEFAULT 'done',
    notes    TEXT,
    FOREIGN KEY (habit_id) REFERENCES habits (habit_id) ON DELETE CASCADE
);

CREATE TABLE `diary_entries` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `entry_date` date NOT NULL,
  `entry_text` text,
  `mood_level` varchar(50),
  `song_mood` varchar(50),
  `sentiment_score` float,
  `suggested_song_title` varchar(255),
  `suggested_song_artist` varchar(255),
  `ai_reflection` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- INDEXES FOR PERFORMANCE
CREATE INDEX idx_user_habits ON habits (user_id);
CREATE INDEX idx_habit_logs ON habit_logs (habit_id, log_date);
