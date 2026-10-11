-- Heaven of Web — database schema (MySQL 5.7+/MariaDB 10.4+).
-- On Hostinger without SSH, import this file then 002_seed.sql with phpMyAdmin.

CREATE TABLE IF NOT EXISTS migrations (
  name VARCHAR(100) NOT NULL PRIMARY KEY,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings; lang = '' for values that are not translated.
CREATE TABLE settings (
  skey VARCHAR(100) NOT NULL,
  lang VARCHAR(5) NOT NULL DEFAULT '',
  value MEDIUMTEXT NOT NULL,
  PRIMARY KEY (skey, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translated fields of services, features, projects and reviews.
CREATE TABLE translations (
  entity VARCHAR(30) NOT NULL,
  entity_id INT UNSIGNED NOT NULL,
  lang VARCHAR(5) NOT NULL,
  field VARCHAR(50) NOT NULL,
  value MEDIUMTEXT NOT NULL,
  PRIMARY KEY (entity, entity_id, lang, field)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(40) NOT NULL DEFAULT 'code',
  sort INT NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Why choose us" items.
CREATE TABLE features (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(40) NOT NULL DEFAULT 'check',
  sort INT NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  client VARCHAR(150) NOT NULL,
  url VARCHAR(255) NULL,
  cover VARCHAR(255) NULL,
  year SMALLINT UNSIGNED NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1,
  sort INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  sort INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_project_images FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author VARCHAR(120) NOT NULL,
  company VARCHAR(150) NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  avatar VARCHAR(255) NULL,
  project_id INT UNSIGNED NULL,
  published TINYINT(1) NOT NULL DEFAULT 1,
  sort INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact form submissions.
CREATE TABLE messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  company VARCHAR(150) NULL,
  service VARCHAR(120) NULL,
  budget VARCHAR(60) NULL,
  message TEXT NOT NULL,
  lang VARCHAR(5) NOT NULL DEFAULT 'fr',
  status ENUM('new','read','replied','archived','spam') NOT NULL DEFAULT 'new',
  ip_hash CHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  INDEX idx_messages_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_replies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NULL,
  subject VARCHAR(255) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  sent_ok TINYINT(1) NOT NULL DEFAULT 0,
  error VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reply_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_reply_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE push_subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  endpoint TEXT NOT NULL,
  endpoint_hash CHAR(64) NOT NULL UNIQUE,
  p256dh VARCHAR(255) NOT NULL,
  auth VARCHAR(255) NOT NULL,
  device VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME NULL,
  CONSTRAINT fk_push_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics hits (pageviews, section views, contact conversions). No raw IPs.
CREATE TABLE visits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(12) NOT NULL DEFAULT 'pageview',
  day DATE NOT NULL,
  visited_at DATETIME NOT NULL,
  path VARCHAR(255) NOT NULL,
  label VARCHAR(80) NULL,
  lang VARCHAR(5) NULL,
  visitor CHAR(64) NOT NULL,
  referrer VARCHAR(120) NULL,
  browser VARCHAR(30) NULL,
  os VARCHAR(30) NULL,
  device VARCHAR(20) NULL,
  country CHAR(2) NULL,
  INDEX idx_visits_day (day, type),
  INDEX idx_visits_visitor (visitor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limits (
  rkey CHAR(64) NOT NULL PRIMARY KEY,
  hits INT UNSIGNED NOT NULL DEFAULT 0,
  reset_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (name) VALUES ('001_init');
