-- ==========================================================
-- SSD Prayas: SQL Migration for Blog SEO & Control Panel Features
-- Features added:
--  - image_alt
--  - meta_keywords
--  - schema_article (JSON-LD Article Schema)
--  - schema_faq (JSON-LD FAQ Schema)
--  - og_title, og_description, og_image (Open Graph tags)
--  - twitter_title, twitter_description, twitter_image (Twitter Cards)
-- ==========================================================

ALTER TABLE `blogs`
  ADD COLUMN IF NOT EXISTS `image_alt` varchar(255) DEFAULT NULL AFTER `image`,
  ADD COLUMN IF NOT EXISTS `meta_keywords` text DEFAULT NULL AFTER `meta_description`,
  ADD COLUMN IF NOT EXISTS `schema_article` longtext DEFAULT NULL AFTER `meta_keywords`,
  ADD COLUMN IF NOT EXISTS `schema_faq` longtext DEFAULT NULL AFTER `schema_article`,
  ADD COLUMN IF NOT EXISTS `og_title` varchar(255) DEFAULT NULL AFTER `schema_faq`,
  ADD COLUMN IF NOT EXISTS `og_description` text DEFAULT NULL AFTER `og_title`,
  ADD COLUMN IF NOT EXISTS `og_image` varchar(255) DEFAULT NULL AFTER `og_description`,
  ADD COLUMN IF NOT EXISTS `twitter_title` varchar(255) DEFAULT NULL AFTER `og_image`,
  ADD COLUMN IF NOT EXISTS `twitter_description` text DEFAULT NULL AFTER `twitter_title`,
  ADD COLUMN IF NOT EXISTS `twitter_image` varchar(255) DEFAULT NULL AFTER `twitter_description`;
