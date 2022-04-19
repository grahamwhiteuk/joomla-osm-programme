CREATE TABLE IF NOT EXISTS `#__mod_osm` (
	`url` VARCHAR(191) NOT NULL,
	`fetched_at` DATETIME NOT NULL,
	`response` text,
  PRIMARY KEY (`url`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
