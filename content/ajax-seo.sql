CREATE TABLE IF NOT EXISTS `ajax_seo` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order` mediumint(8) unsigned NOT NULL,
  `url` varchar(70) collate utf8_unicode_ci NOT NULL,
  `fn` varchar(70) collate utf8_unicode_ci NOT NULL,
  `content` text collate utf8_unicode_ci,
  `pubdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7;

INSERT INTO `ajax_seo` (`order`, `url`, `fn`, `content`) VALUES
(1, '', 'Home', 'Home content'),
(2, 'about', 'About', 'About content'),
(3, 'portfolio', 'Portfolio', 'Portfolio  content'),
(4, 'contact', 'Contact', 'Contact  content'),
(5, 'контакты', 'Контакты', 'Содержание контактом'),
(6, 'צור-קשר', 'צור קשר', 'תוכן לצור קשר');