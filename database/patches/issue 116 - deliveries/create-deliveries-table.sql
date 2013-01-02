-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 02/01/2013 às 13h56min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Banco de Dados: `testing_c3op`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `projects_deliveries`
--

CREATE TABLE IF NOT EXISTS `projects_deliveries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL,
  `receivable` int(10) unsigned NOT NULL,
  `predicted_date` date DEFAULT NULL,
  `real_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receivable` (`receivable`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
