DROP TABLE IF EXISTS `tree`;

CREATE TABLE `tree` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `left` INT(11) NOT NULL DEFAULT '0',
  `right` INT(11) NOT NULL DEFAULT '0',
  `level` INT(11) NOT NULL DEFAULT '0',
  `root_id` INT(11) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
  PRIMARY KEY (`id`),
  INDEX `nested_set_nested_set_idx` (`root_id`)
)
ENGINE=InnoDB
;