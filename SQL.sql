

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NULL, -- может быть NULL для Google-пользователей
    google_id VARCHAR(255) UNIQUE NULL,
    name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_text TEXT NOT NULL,
    translated_text TEXT NOT NULL,
    source_lang VARCHAR(10) NOT NULL,
    target_lang VARCHAR(10) NOT NULL,
    type ENUM('text','doc') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS image_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    original_name VARCHAR(255) NOT NULL,
    original_path VARCHAR(500) NOT NULL,
    converted_path VARCHAR(500) NOT NULL,
    target_format VARCHAR(10) NOT NULL,
    quality INT DEFAULT 90,
    width INT NULL,
    height INT NULL,
    file_size INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user';

CREATE TABLE IF NOT EXISTS audio_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    original_name VARCHAR(255) NOT NULL,
    original_path VARCHAR(500) NOT NULL,
    converted_path VARCHAR(500) NOT NULL,
    target_format VARCHAR(10) NOT NULL,
    bitrate INT DEFAULT 128,
    sample_rate INT DEFAULT 44100,
    file_size INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
--- обновление 2.4 оптимизация бд
-- Добавляем индексы для ускорения запросов
ALTER TABLE translations ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE image_conversions ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE audio_conversions ADD INDEX idx_user_created (user_id, created_at);

-- Добавляем поле для статуса (если нужно)
ALTER TABLE translations ADD COLUMN status ENUM('pending', 'completed', 'failed') DEFAULT 'completed';

-- Партиционирование для старых записей (опционально)
-- Создаём таблицу-архив для старых переводов
CREATE TABLE translations_archive LIKE translations;

CREATE TABLE IF NOT EXISTS ai_generations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    prompt TEXT NOT NULL,
    response TEXT NOT NULL,
    model VARCHAR(50) DEFAULT 'llama3.2',
    type ENUM('translation', 'summary', 'generation', 'analysis') DEFAULT 'generation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at)
);

CREATE TABLE IF NOT EXISTS ai_vision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    image_path VARCHAR(500) NOT NULL,
    detections JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)