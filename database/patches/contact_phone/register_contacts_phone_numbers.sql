-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 18/08/2012 às 17h20min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: 'testing_c3op'
--

-- --------------------------------------------------------

--
-- Estrutura da tabela 'register_contacts_phone_numbers'
--

CREATE TABLE IF NOT EXISTS register_contacts_phone_numbers (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  contact int(10) unsigned DEFAULT NULL,
  area_code varchar(10) DEFAULT NULL,
  local_number varchar(20) DEFAULT NULL,
  label varchar(30) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY contact (contact)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
