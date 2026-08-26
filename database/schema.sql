-- AI Native CMS — MySQL schema v8 extraction baseline
-- MySQL 5.7+/8.0 compatible; utf8mb4 throughout.
-- Contains structure only. Adopter-authored content belongs in adopter seeds or canonical runtime state.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS app_meta (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  schema_version INT UNSIGNED NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO app_meta (id, schema_version) VALUES (1, 8)
  ON DUPLICATE KEY UPDATE schema_version=GREATEST(schema_version, 8);

CREATE TABLE IF NOT EXISTS cms_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(191) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(191) NOT NULL DEFAULT '',
  email VARCHAR(254) NOT NULL DEFAULT '',
  role VARCHAR(64) NOT NULL DEFAULT 'Owner',
  session_version INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cms_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_activity (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(64) NOT NULL,
  message VARCHAR(500) NOT NULL,
  context_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cms_activity_created (created_at),
  KEY idx_cms_activity_user (user_id),
  CONSTRAINT fk_cms_activity_user FOREIGN KEY (user_id) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limit_counters (
  bucket VARCHAR(80) NOT NULL,
  rate_key CHAR(64) NOT NULL,
  window_id BIGINT UNSIGNED NOT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 1,
  expires_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (bucket, rate_key, window_id),
  KEY idx_rate_limit_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seo_overrides (
  page_path VARCHAR(512) NOT NULL PRIMARY KEY,
  title VARCHAR(512) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  canonical VARCHAR(1024) NOT NULL DEFAULT '',
  robots VARCHAR(255) NOT NULL DEFAULT '',
  og_title VARCHAR(512) NOT NULL DEFAULT '',
  og_description TEXT NOT NULL,
  twitter_title VARCHAR(512) NOT NULL DEFAULT '',
  twitter_description TEXT NOT NULL,
  indexable TINYINT(1) NOT NULL DEFAULT 1,
  follow_links TINYINT(1) NOT NULL DEFAULT 1,
  archive_allowed TINYINT(1) NOT NULL DEFAULT 1,
  snippet_limit INT NOT NULL DEFAULT -1,
  image_preview VARCHAR(16) NOT NULL DEFAULT 'large',
  video_preview_limit INT NOT NULL DEFAULT -1,
  canonical_mode VARCHAR(16) NOT NULL DEFAULT 'self',
  social_mode VARCHAR(16) NOT NULL DEFAULT 'inherit',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_blocks (
  page_path VARCHAR(512) NOT NULL,
  block_id VARCHAR(191) NOT NULL,
  tag_name VARCHAR(32) NOT NULL,
  html_content LONGTEXT NOT NULL,
  content_sha256 CHAR(64) NOT NULL,
  source_sha256 CHAR(64) NOT NULL DEFAULT '',
  source_ref VARCHAR(191) NOT NULL DEFAULT '',
  source_updated_at DATETIME NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (page_path, block_id),
  KEY idx_page_blocks_user (updated_by),
  CONSTRAINT fk_page_blocks_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_block_templates (
  template_key VARCHAR(191) NOT NULL PRIMARY KEY,
  label VARCHAR(191) NOT NULL,
  category VARCHAR(96) NOT NULL DEFAULT 'Section',
  source_page VARCHAR(512) NOT NULL,
  source_ordinal INT UNSIGNED NOT NULL,
  source_hash CHAR(64) NOT NULL,
  html_content LONGTEXT NOT NULL,
  variables_json LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_page_block_templates_source (source_page(191), source_ordinal),
  KEY idx_page_block_templates_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_compositions (
  page_path VARCHAR(512) NOT NULL PRIMARY KEY,
  label VARCHAR(191) NOT NULL,
  title VARCHAR(512) NOT NULL DEFAULT '',
  shell_path VARCHAR(512) NOT NULL,
  parent_path VARCHAR(512) NULL,
  blocks_json LONGTEXT NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_page_compositions_user (updated_by),
  KEY idx_page_compositions_parent (parent_path(191)),
  CONSTRAINT fk_page_compositions_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_branding (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  settings_json LONGTEXT NOT NULL,
  css_sha256 CHAR(64) NOT NULL DEFAULT '',
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_site_branding_user (updated_by),
  CONSTRAINT fk_site_branding_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_library (
  asset_key VARCHAR(191) NOT NULL PRIMARY KEY,
  public_path VARCHAR(512) NOT NULL,
  file_sha256 CHAR(64) NOT NULL DEFAULT '',
  mime_type VARCHAR(96) NOT NULL DEFAULT '',
  width INT UNSIGNED NOT NULL DEFAULT 0,
  height INT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(191) NOT NULL DEFAULT '',
  alt_text VARCHAR(1000) NOT NULL DEFAULT '',
  caption TEXT NOT NULL,
  source_kind VARCHAR(32) NOT NULL DEFAULT 'site',
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_media_library_path (public_path(191)),
  KEY idx_media_library_user (updated_by),
  CONSTRAINT fk_media_library_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_navigation (
  nav_key VARCHAR(64) NOT NULL PRIMARY KEY,
  items_json LONGTEXT NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_site_navigation_user (updated_by),
  CONSTRAINT fk_site_navigation_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS redirect_records (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  source_path VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  target_path VARCHAR(1024) NOT NULL,
  status_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  preserve_query TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  managed_by VARCHAR(32) NOT NULL DEFAULT 'manual',
  note VARCHAR(1000) NOT NULL DEFAULT '',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_redirect_records_source (source_path),
  KEY idx_redirect_records_active (is_active, source_path(191)),
  KEY idx_redirect_records_updated (updated_at),
  CONSTRAINT fk_redirect_records_created_user FOREIGN KEY (created_by) REFERENCES cms_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_redirect_records_updated_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_documents (
  document_key VARCHAR(512) NOT NULL PRIMARY KEY,
  document_type VARCHAR(64) NOT NULL,
  label VARCHAR(191) NOT NULL DEFAULT '',
  source_format VARCHAR(32) NOT NULL DEFAULT 'json',
  content LONGTEXT NOT NULL,
  content_sha256 CHAR(64) NOT NULL,
  source_sha256 CHAR(64) NOT NULL DEFAULT '',
  source_ref VARCHAR(191) NOT NULL DEFAULT '',
  source_updated_at DATETIME NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_content_documents_type (document_type),
  KEY idx_content_documents_user (updated_by),
  CONSTRAINT fk_content_documents_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_change_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  target_type VARCHAR(64) NOT NULL,
  target_key VARCHAR(700) NOT NULL,
  origin VARCHAR(64) NOT NULL,
  origin_ref VARCHAR(191) NOT NULL DEFAULT '',
  outcome ENUM('applied','already_current','preserved_newer','conflict') NOT NULL,
  before_sha256 CHAR(64) NOT NULL DEFAULT '',
  after_sha256 CHAR(64) NOT NULL DEFAULT '',
  source_sha256 CHAR(64) NOT NULL DEFAULT '',
  message VARCHAR(1000) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_content_change_target (target_type, target_key(191), created_at),
  KEY idx_content_change_origin (origin, origin_ref, created_at),
  KEY idx_content_change_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_update_sets (
  update_id VARCHAR(191) NOT NULL PRIMARY KEY,
  origin VARCHAR(64) NOT NULL,
  origin_ref VARCHAR(191) NOT NULL DEFAULT '',
  update_sha256 CHAR(64) NOT NULL,
  applied_count INT UNSIGNED NOT NULL DEFAULT 0,
  preserved_count INT UNSIGNED NOT NULL DEFAULT 0,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_revisions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  page_path VARCHAR(512) NOT NULL,
  revision_kind VARCHAR(32) NOT NULL,
  content_sha256 CHAR(64) NOT NULL,
  html_content LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_page_revisions_page (page_path(191), created_at),
  KEY idx_page_revisions_user (user_id),
  CONSTRAINT fk_page_revisions_user FOREIGN KEY (user_id) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(512) NOT NULL,
  dek TEXT NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'ideas',
  category_label VARCHAR(191) NOT NULL DEFAULT 'Ideas',
  published_date DATE NOT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  tags_json LONGTEXT NOT NULL,
  thesis TEXT NOT NULL,
  related_json LONGTEXT NOT NULL,
  territory_image VARCHAR(512) NOT NULL DEFAULT '',
  featured_image VARCHAR(512) NOT NULL DEFAULT '',
  social_image VARCHAR(512) NOT NULL DEFAULT '',
  image_alt VARCHAR(1000) NOT NULL DEFAULT '',
  body LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_date (status, published_date),
  KEY idx_posts_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_revisions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  original_slug VARCHAR(191) NOT NULL,
  snapshot_json LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_post_revisions_post (post_id, created_at),
  KEY idx_post_revisions_user (user_id),
  CONSTRAINT fk_post_revisions_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL,
  CONSTRAINT fk_post_revisions_user FOREIGN KEY (user_id) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional built-in subscription primitive. Sites that do not expose subscriptions may leave these tables unused.
CREATE TABLE IF NOT EXISTS subscribers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  list_key VARCHAR(64) NOT NULL,
  email VARCHAR(254) NOT NULL,
  status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
  requested_at DATETIME NOT NULL,
  last_confirmation_sent_at DATETIME NULL,
  confirmed_at DATETIME NULL,
  confirmation_token_hash CHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_subscribers_list_email (list_key, email),
  KEY idx_subscribers_status (list_key, status),
  KEY idx_subscribers_token (confirmation_token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Used only when AINCMS_MAIL_TRANSPORT=log for local/testing environments.
CREATE TABLE IF NOT EXISTS mail_outbox (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  recipient VARCHAR(254) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  body LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_mail_outbox_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
