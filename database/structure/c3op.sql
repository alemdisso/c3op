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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `auth_users` (`id`, `login`, `name`, `password`, `email`, `role`, `status`, `first_login`, `last_login`) VALUES
(1, 'admin', '', '1cebede82348cd151992cc41693f795d', 'admin@localhost', 900, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

--
-- Estrutura da tabela `finances_outlays`
--

DROP TABLE IF EXISTS `finances_outlays`;
CREATE TABLE IF NOT EXISTS `finances_outlays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `action` int(10) unsigned DEFAULT NULL,
  `responsible` int(10) unsigned DEFAULT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `predicted_date` date DEFAULT NULL,
  `real_value` decimal(10,2) DEFAULT NULL,
  `real_date` date DEFAULT NULL,
  `recurrent` smallint(6) DEFAULT NULL,
  `observation` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto` (`project`),
  KEY `responsible` (`responsible`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `finances_receivables`
--

DROP TABLE IF EXISTS `finances_receivables`;
CREATE TABLE IF NOT EXISTS `finances_receivables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `description` text,
  `predicted_date` date DEFAULT NULL,
  `real_date` date DEFAULT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `real_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
  `product` tinyint(1) unsigned DEFAULT NULL,
  `supervisor` int(10) unsigned DEFAULT NULL,
  `milestone` tinyint(1) DEFAULT NULL,
  `requirement_for_receiving` int(10) unsigned DEFAULT NULL,
  `budget_forecast` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_actions_dates`
--

DROP TABLE IF EXISTS `projects_actions_dates`;
CREATE TABLE IF NOT EXISTS `projects_actions_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(10) unsigned DEFAULT NULL,
  `baseline_begin_date` date DEFAULT NULL,
  `baseline_finish_date` date DEFAULT NULL,
  `predicted_begin_date` date DEFAULT NULL,
  `predicted_finish_date` date DEFAULT NULL,
  `real_begin_date` date DEFAULT NULL,
  `real_finish_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_index` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_contracts`
--

DROP TABLE IF EXISTS `projects_contracts`;
CREATE TABLE IF NOT EXISTS `projects_contracts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `amendment` tinyint(1) DEFAULT NULL,
  `begin_date` date NOT NULL,
  `finish_date` date NOT NULL,
  `signing_date` date DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `contract_nature` tinyint(3) unsigned DEFAULT NULL,
  `management_fee` decimal(10,2) DEFAULT NULL,
  `object` text,
  `summary` text,
  `observation` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_deliveries`
--

DROP TABLE IF EXISTS `projects_deliveries`;
CREATE TABLE IF NOT EXISTS `projects_deliveries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL,
  `receivable` int(10) unsigned NOT NULL,
  `baseline_date` date DEFAULT NULL,
  `predicted_date` date DEFAULT NULL,
  `real_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receivable` (`receivable`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
  `value` decimal(10,2) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `contract_nature` tinyint(10) unsigned DEFAULT NULL,
  `area_activity` int(10) unsigned DEFAULT NULL,
  `overhead` decimal(10,2) DEFAULT NULL,
  `management_fee` decimal(10,2) DEFAULT NULL,
  `object` text,
  `summary` text,
  `observation` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `resources_material_supplies`
--

DROP TABLE IF EXISTS `resources_material_supplies`;
CREATE TABLE IF NOT EXISTS `resources_material_supplies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL,
  `action` int(10) unsigned NOT NULL,
  `description` varchar(120) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `institution` int(10) unsigned DEFAULT NULL,
  `linkage` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit` varchar(120) DEFAULT NULL,
  `unit_value` decimal(10,2) NOT NULL,
  `total_value` decimal(10,2) unsigned NOT NULL,
  `status` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `status` (`status`),
  KEY `institution` (`institution`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `resources_responsibles`
--

DROP TABLE IF EXISTS `resources_responsibles`;
CREATE TABLE IF NOT EXISTS `resources_responsibles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL,
  `action` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `institution` int(10) unsigned NOT NULL,
  `contact` int(10) unsigned NOT NULL,
  `predicted_value` decimal(10,2) unsigned DEFAULT NULL,
  `contracted_value` decimal(10,2) unsigned DEFAULT NULL,
  `status` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
