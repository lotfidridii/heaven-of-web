-- Two-factor authentication (TOTP) for admins.
ALTER TABLE admins ADD COLUMN mfa_secret TEXT NULL AFTER password_hash;
ALTER TABLE admins ADD COLUMN mfa_enabled_at DATETIME NULL AFTER mfa_secret;
ALTER TABLE admins ADD COLUMN mfa_last_step BIGINT NULL AFTER mfa_enabled_at;

CREATE TABLE admin_recovery_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_recovery_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (skey, lang, value) VALUES ('mfa_required', '', '0')
ON DUPLICATE KEY UPDATE value = value;

INSERT INTO migrations (name) VALUES ('008_admin_mfa');
