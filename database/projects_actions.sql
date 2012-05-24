-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 24/05/2012 às 16h39min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Banco de Dados: `testing_c3op`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_actions`
--

CREATE TABLE IF NOT EXISTS `projects_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  `project` int(10) unsigned DEFAULT NULL,
  `status` tinyint(4) unsigned DEFAULT NULL,
  `description` text COLLATE latin1_general_ci,
  `begin` date DEFAULT NULL,
  `subordinated_to` int(10) unsigned DEFAULT NULL,
  `responsible` int(10) unsigned DEFAULT NULL,
  `milestone` tinyint(1) DEFAULT NULL,
  `requirement_for_receiving` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;
