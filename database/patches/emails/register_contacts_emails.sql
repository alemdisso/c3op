SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- --------------------------------------------------------

--
-- Estrutura da tabela 'register_contacts_emails'
--

CREATE TABLE IF NOT EXISTS register_contacts_emails (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  contact int(10) unsigned DEFAULT NULL,
  email varchar(120) DEFAULT NULL,
  label varchar(30) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY contact (contact)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

