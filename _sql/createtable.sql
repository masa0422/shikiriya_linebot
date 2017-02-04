CREATE TABLE IF NOT EXISTS `lba_users` (
  `id` bigint(20) unsigned NOT NULL,
  `timestamp` text DEFAULT NULL,
  `userid` varchar(255) DEFAULT NULL,
  `keyword` varchar(200) DEFAULT NULL,
  `displayName` varchar(200) DEFAULT NULL,
  `pictureUrl` text DEFAULT NULL,
  `statusMessage` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lba_schedule` (
  `id` bigint(20) unsigned NOT NULL,
  `keyword` varchar(200) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '0',
  `result` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lba_rawdata` (
  `id` bigint(20) unsigned NOT NULL,
  `data` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `flg` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_log_error` (
  `id` bigint(20) unsigned NOT NULL,
  `url` text,
  `file_path` varchar(255) DEFAULT NULL,
  `line_number` varchar(255) DEFAULT NULL,
  `error_content` text,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `flg` tinyint(1) unsigned DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


ALTER TABLE `lba_users`
ADD PRIMARY KEY (`id`);

ALTER TABLE `lba_schedule`
ADD PRIMARY KEY (`id`);

ALTER TABLE `lba_rawdata`
ADD PRIMARY KEY (`id`);

ALTER TABLE `db_log_error`
ADD PRIMARY KEY (`id`);

ALTER TABLE `lba_users`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `lba_schedule`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `lba_rawdata`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `db_log_error`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;