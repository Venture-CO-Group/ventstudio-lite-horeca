-- =========================================================
-- VentStudio — MySQL schema (optional; the site also runs on flat files).
-- Import once, then set LT_DB_ENABLED=1 in .env and run
-- /admin/migrate.php to copy content.json + posts.json into the DB.
-- =========================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS lt_content (
  id   TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  data LONGTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lt_posts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(200) NOT NULL,
  date       DATE NOT NULL,
  published  TINYINT(1) NOT NULL DEFAULT 0,
  cover      VARCHAR(500) NOT NULL DEFAULT '',
  category   VARCHAR(120) NOT NULL DEFAULT '',
  data       LONGTEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_slug (slug),
  KEY idx_pub_date (published, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lt_subscribers (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(255) NOT NULL,
  locale       VARCHAR(5) NOT NULL DEFAULT 'en',
  status       VARCHAR(20) NOT NULL DEFAULT 'subscribed',
  mailchimp_id VARCHAR(64) NOT NULL DEFAULT '',
  consent_at   TIMESTAMP NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lt_submissions (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  source     VARCHAR(20) NOT NULL DEFAULT 'popup',
  name       VARCHAR(160) NOT NULL DEFAULT '',
  email      VARCHAR(255) NOT NULL DEFAULT '',
  company    VARCHAR(200) NOT NULL DEFAULT '',
  message    TEXT,
  locale     VARCHAR(5) NOT NULL DEFAULT 'en',
  ip         VARCHAR(45) NOT NULL DEFAULT '',
  ua         VARCHAR(255) NOT NULL DEFAULT '',
  is_spam    TINYINT(1) NOT NULL DEFAULT 0,
  status     VARCHAR(20) NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lt_redirects (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  from_path VARCHAR(300) NOT NULL,
  to_path   VARCHAR(500) NOT NULL,
  code      SMALLINT NOT NULL DEFAULT 301,
  active    TINYINT(1) NOT NULL DEFAULT 1,
  hits      INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_from (from_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Online orders (flat-file fallback: data/orders.json)
CREATE TABLE IF NOT EXISTS lt_orders (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  number     VARCHAR(20) NOT NULL,
  status     VARCHAR(24) NOT NULL DEFAULT 'received',
  fulfilment VARCHAR(20) NOT NULL DEFAULT 'delivery',
  payment    VARCHAR(20) NOT NULL DEFAULT 'stripe',
  total      INT UNSIGNED NOT NULL DEFAULT 0,   -- pence, incl. delivery
  currency   VARCHAR(6) NOT NULL DEFAULT 'gbp',
  data       LONGTEXT NOT NULL,                  -- full JSON (items, customer, address, fees)
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_number (number),
  KEY idx_status (status), KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
