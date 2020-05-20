
CREATE DATABASE IF NOT EXISTS `mabdd_dev` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `mabdd_dev`;

CREATE TABLE IF NOT EXISTS `matable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) NOT NULL DEFAULT '0',
  `commentaire` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='table d''exemple';

DELETE FROM `matable`;

INSERT INTO `matable` (`id`, `libelle`, `commentaire`) VALUES
	(1, 'test1', 'ceci est un test'),
	(2, 'test2', 'ceci est un autre test'),
	(3, 'test1', 'le meme que le premier');
