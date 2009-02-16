CREATE TABLE `import` (
  `import_id` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) NOT NULL default '',
  `reverted` int(1) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `import_errors` longtext,
  `added_lines` int(11) default NULL,
  PRIMARY KEY  (`import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='InnoDB free: 4096 kB; InnoDB free: 4096 kB; InnoDB free: 143';

ALTER TABLE `candidate` ADD COLUMN `import_id` INTEGER(11) NOT NULL DEFAULT '0';
ALTER TABLE `contact` ADD COLUMN `import_id` INTEGER(11) NOT NULL DEFAULT '0';
ALTER TABLE `client` ADD COLUMN `import_id` INTEGER(11) NOT NULL DEFAULT '0';