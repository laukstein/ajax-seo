CREATE TABLE IF NOT EXISTS `address` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `orderid` mediumint(8) unsigned NOT NULL,
  `url` varchar(70) collate utf8_unicode_ci NOT NULL,
  `title` varchar(70) collate utf8_unicode_ci NOT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

INSERT INTO `address` VALUES
  (52338, 1, '', 'Home', 'Home content'),
  (70104, 2, 'about', 'About', 'About content'),
  (27034, 3, 'portfolio', 'Portfolio', 'Portfolio  content'),
  (39111, 4, 'contact', 'Contact', 'Contact  content'),
  (50128, 5, 'контакты', 'Контакты', 'Содержание контактом'),
  (74224, 6, 'צור-קשר', 'צור קשר', 'תוכן לצור קשר');