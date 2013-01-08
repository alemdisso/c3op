-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 02/01/2013 às 14h12min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Banco de Dados: `testing_c3op`
--

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `projects_material_supplies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  KEY `institution` (`institution`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;