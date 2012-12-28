-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 18/12/2012 às 22h55min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Banco de Dados: `testing_c3op`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_contracts`
--

CREATE TABLE IF NOT EXISTS `projects_contracts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned DEFAULT NULL,
  `amendment` tinyint(1) DEFAULT '0',
  `begin_date` date NOT NULL,
  `finish_date` date NOT NULL,
  `signing_date` date DEFAULT NULL,
  `value` decimal(10,2) NOT NULL,
  `contract_nature` tinyint(3) unsigned DEFAULT NULL,
  `management_fee` decimal(10,2) DEFAULT NULL,
  `object` text,
  `summary` text,
  `observation` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
