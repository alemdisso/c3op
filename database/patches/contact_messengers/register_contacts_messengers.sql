
-- --------------------------------------------------------

--
-- Estrutura da tabela 'register_contacts_messengers'
--

CREATE TABLE  `register_contacts_messengers` (
`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
 `contact` INT( 10 ) UNSIGNED DEFAULT NULL ,
 `address` VARCHAR( 120 ) DEFAULT NULL ,
 `service` VARCHAR( 30 ) DEFAULT NULL ,
PRIMARY KEY (  `id` ) ,
KEY  `contact` (  `contact` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;