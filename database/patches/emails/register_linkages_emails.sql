SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- --------------------------------------------------------

--
-- Estrutura da tabela 'register_linkages_emails'
--

CREATE TABLE IF NOT EXISTS register_linkages_emails (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  linkage int(10) unsigned DEFAULT NULL,
  email varchar(120) DEFAULT NULL,
  label varchar(30) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY linkage (linkage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

