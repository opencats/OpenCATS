CREATE TABLE IF NOT EXISTS `jos_catonesettings` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `OC_Database_Name` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_Username` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_host` varchar(250) NOT NULL DEFAULT '',
  `OC_Database_password` varchar(250) NOT NULL DEFAULT '',
  `Cats_install` varchar(250) NOT NULL,
  `Cats_local` varchar(250) NOT NULL,
  `enable_ftp` enum('0','1') NOT NULL DEFAULT '0',
  `attachment_path` varchar(250) NOT NULL,
  `ftp_host` varchar(250) NOT NULL,
  `ftp_user` varchar(250) NOT NULL,
  `ftp_password` varchar(250) NOT NULL,
  `ftp_path` varchar(250) NOT NULL,
  `ftp_port` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `site_id` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `jos_catonesettings` (`id`, `OC_Database_Name`, `OC_Database_Username`, `OC_Database_host`, `OC_Database_password`, `Cats_install`, `Cats_local`, `ftp_host`, `ftp_user`, `ftp_password`, `ftp_path`, `email`) VALUES
(1, 'database', 'user', 'doman', 'remote', 'http://opencats.org/test/cats/', '', 'opencats.org', 'email', 'pass', '/cats/attachments', 'example@localhost.com');