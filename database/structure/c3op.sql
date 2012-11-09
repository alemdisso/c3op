-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 09/11/2012 às 02h26min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Banco de Dados: `testing_c3op`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `auth_users`
--

DROP TABLE IF EXISTS `auth_users`;
CREATE TABLE IF NOT EXISTS `auth_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `password` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `role` int(10) unsigned NOT NULL,
  `status` varchar(80) DEFAULT NULL,
  `first_login` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apelidoUsuario` (`password`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_actions`
--

DROP TABLE IF EXISTS `projects_actions`;
CREATE TABLE IF NOT EXISTS `projects_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `project` int(10) unsigned DEFAULT NULL,
  `done` tinyint(1) unsigned DEFAULT NULL,
  `status` int(10) unsigned DEFAULT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `subordinated_to` int(10) unsigned DEFAULT NULL,
  `responsible` int(10) unsigned DEFAULT NULL,
  `milestone` tinyint(1) DEFAULT NULL,
  `requirement_for_receiving` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=103 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_actions_dates`
--

DROP TABLE IF EXISTS `projects_actions_dates`;
CREATE TABLE IF NOT EXISTS `projects_actions_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(10) unsigned DEFAULT NULL,
  `predicted_begin_date` date DEFAULT NULL,
  `predicted_finish_date` date DEFAULT NULL,
  `real_begin_date` date DEFAULT NULL,
  `real_finish_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_index` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=103 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_actions_events`
--

DROP TABLE IF EXISTS `projects_actions_events`;
CREATE TABLE IF NOT EXISTS `projects_actions_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(10) unsigned NOT NULL,
  `type` mediumint(8) unsigned DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `observation` varchar(250) DEFAULT NULL,
  `responsible` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=156 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_outlays`
--

DROP TABLE IF EXISTS `projects_outlays`;
CREATE TABLE IF NOT EXISTS `projects_outlays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `action` int(10) unsigned DEFAULT NULL,
  `human_resource` int(10) unsigned DEFAULT NULL,
  `predicted_value` float DEFAULT NULL,
  `predicted_date` date DEFAULT NULL,
  `recurrent` smallint(6) DEFAULT NULL,
  `observation` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_projects`
--

DROP TABLE IF EXISTS `projects_projects`;
CREATE TABLE IF NOT EXISTS `projects_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `short_title` varchar(32) NOT NULL,
  `client` int(10) unsigned DEFAULT NULL,
  `our_responsible` int(10) unsigned DEFAULT NULL,
  `responsible_at_client` int(10) unsigned DEFAULT NULL,
  `begin_date` date DEFAULT NULL,
  `finish_date` date DEFAULT NULL,
  `value` float(10,2) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `contract_nature` tinyint(10) unsigned DEFAULT NULL,
  `area_activity` int(10) unsigned DEFAULT NULL,
  `overhead` float(10,2) DEFAULT NULL,
  `management_fee` float(10,2) DEFAULT NULL,
  `object` text,
  `summary` text,
  `observation` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_receivables`
--

DROP TABLE IF EXISTS `projects_receivables`;
CREATE TABLE IF NOT EXISTS `projects_receivables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `predicted_date` date DEFAULT NULL,
  `real_date` date DEFAULT NULL,
  `predicted_value` float(10,2) DEFAULT NULL,
  `real_value` float(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_team_members`
--

DROP TABLE IF EXISTS `projects_team_members`;
CREATE TABLE IF NOT EXISTS `projects_team_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(10) unsigned NOT NULL,
  `description` varchar(120) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `contact` int(10) unsigned NOT NULL,
  `value` float unsigned NOT NULL,
  `status` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_contacts`
--

DROP TABLE IF EXISTS `register_contacts`;
CREATE TABLE IF NOT EXISTS `register_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `type` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_contacts_emails`
--

DROP TABLE IF EXISTS `register_contacts_emails`;
CREATE TABLE IF NOT EXISTS `register_contacts_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` int(10) unsigned DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `label` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_contacts_messengers`
--

DROP TABLE IF EXISTS `register_contacts_messengers`;
CREATE TABLE IF NOT EXISTS `register_contacts_messengers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` int(10) unsigned DEFAULT NULL,
  `address` varchar(120) DEFAULT NULL,
  `service` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_contacts_phone_numbers`
--

DROP TABLE IF EXISTS `register_contacts_phone_numbers`;
CREATE TABLE IF NOT EXISTS `register_contacts_phone_numbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` int(10) unsigned DEFAULT NULL,
  `area_code` varchar(10) DEFAULT NULL,
  `local_number` varchar(20) DEFAULT NULL,
  `label` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_institutions`
--

DROP TABLE IF EXISTS `register_institutions`;
CREATE TABLE IF NOT EXISTS `register_institutions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `short_name` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `legal_entity` tinyint(1) DEFAULT NULL,
  `register_number` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `state_registration` tinyint(1) DEFAULT NULL,
  `local_register_number` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `street` varchar(180) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `street_number` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `address_complement` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `zip_code` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `district` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `state` varchar(2) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `website` varchar(120) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `type` smallint(5) unsigned DEFAULT NULL,
  `relationship_type` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_linkages`
--

DROP TABLE IF EXISTS `register_linkages`;
CREATE TABLE IF NOT EXISTS `register_linkages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` int(10) unsigned DEFAULT NULL,
  `institution` int(10) unsigned DEFAULT NULL,
  `department` varchar(180) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `position` varchar(120) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contato` (`contact`,`institution`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_linkages_emails`
--

DROP TABLE IF EXISTS `register_linkages_emails`;
CREATE TABLE IF NOT EXISTS `register_linkages_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `linkage` int(10) unsigned DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `label` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `linkage` (`linkage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `register_linkages_phone_numbers`
--

DROP TABLE IF EXISTS `register_linkages_phone_numbers`;
CREATE TABLE IF NOT EXISTS `register_linkages_phone_numbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `linkage` int(10) unsigned DEFAULT NULL,
  `area_code` varchar(10) DEFAULT NULL,
  `local_number` varchar(20) DEFAULT NULL,
  `label` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `linkage` (`linkage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;
