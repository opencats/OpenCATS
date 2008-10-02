DROP TABLE  `feedback`;
ALTER TABLE  `candidate`  ADD COLUMN  `date_available`  datetime  AFTER `source`;
ALTER TABLE  `user`  MODIFY COLUMN  `user_name`  varchar(64)  NOT NULL;
ALTER TABLE  `user`  MODIFY COLUMN  `password`  varchar(128)  NOT NULL;
UPDATE `access_level` SET `long_description` = 'Delete - All lower access, plus the ability to delete information on the system.' WHERE `access_level_id` = 300;
