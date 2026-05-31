CREATE TABLE IF NOT EXISTS live_schedules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    live_pict VARCHAR(255) NOT NULL DEFAULT '',
    body_html MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_deleted_at (deleted_at),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO site_settings (setting_key, setting_value, created_at, updated_at)
SELECT 'live_schedule_per_page', '10', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM site_settings WHERE setting_key = 'live_schedule_per_page'
);