CREATE TABLE IF NOT EXISTS `jos_catonesettings` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `OC_Database_Name` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_Username` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_host` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_password` varchar(250) NOT NULL DEFAULT '',
  `Cats_install` varchar(250) NOT NULL,
  `Cats_local` varchar(250) NOT NULL,
  `ftp_host` varchar(250) NOT NULL,
  `ftp_user` varchar(250) NOT NULL,
  `ftp_password` varchar(250) NOT NULL,
  `ftp_path` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `jos_catonesettings` (`id`, `OC_Database_Name`, `OC_Database_Username`, `OC_Database_host`, `OC_Database_password`, `Cats_install`, `Cats_local`, `ftp_host`, `ftp_user`, `ftp_password`, `ftp_path`, `email`) VALUES
(1, 'opencats_cats', 'opencats_remote', 'www.opencats.org', 'remote', 'http://opencats.org/test/cats/', '', 'opencats.org', 'test@opencats.org', 'cB7r?Ru:F5dc', '/cats/attachments/site_1/0xxx/8b6db30a4c7e2d71ef54beaad5a9c4e1', 'admin@opencats.org');