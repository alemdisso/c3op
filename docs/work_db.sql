-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 19/03/2012 às 18h38min
-- Versão do Servidor: 5.5.16
-- Versão do PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `c3op`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `autenticacao_usuarios`
--

DROP TABLE IF EXISTS `autenticacao_usuarios`;
CREATE TABLE IF NOT EXISTS `autenticacao_usuarios` (
  `codUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nomeUsuario` varchar(80) NOT NULL,
  `sobrenomeUsuario` varchar(80) NOT NULL,
  `apelidoUsuario` varchar(80) NOT NULL,
  `emailUsuario` varchar(80) NOT NULL,
  `senhaUsuario` varchar(32) NOT NULL DEFAULT '0',
  `statusUsuario` varchar(80) NOT NULL DEFAULT 'inativo',
  PRIMARY KEY (`codUsuario`),
  KEY `apelidoUsuario` (`apelidoUsuario`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `autenticacao_usuarios`
--

INSERT INTO `autenticacao_usuarios` (`codUsuario`, `nomeUsuario`, `sobrenomeUsuario`, `apelidoUsuario`, `emailUsuario`, `senhaUsuario`, `statusUsuario`) VALUES
(1, 'Rodrigo', 'Machado', 'rod', 'rodrigo.machado@programarepertorios.com.br', 'b8c37e33defde51cf91e1e03e51657da', 'ativo'),
(2, 'Claudio', 'Pires', 'claudiop', 'claudio.pires@programarepertorios.com.br', '2a4047667f30872f6df5b99ee4594ebd', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_contatos`
--

DROP TABLE IF EXISTS `cadastro_contatos`;
CREATE TABLE IF NOT EXISTS `cadastro_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(11) DEFAULT NULL,
  `tipo` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_emails_contatos`
--

DROP TABLE IF EXISTS `cadastro_emails_contatos`;
CREATE TABLE IF NOT EXISTS `cadastro_emails_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(10) unsigned DEFAULT NULL,
  `endereco` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idContato` (`contato`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_emails_vinculos`
--

DROP TABLE IF EXISTS `cadastro_emails_vinculos`;
CREATE TABLE IF NOT EXISTS `cadastro_emails_vinculos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(10) unsigned DEFAULT NULL,
  `endereco` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idContato` (`contato`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_instituicoes`
--

DROP TABLE IF EXISTS `cadastro_instituicoes`;
CREATE TABLE IF NOT EXISTS `cadastro_instituicoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `nome_curto` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `pj` tinyint(1) DEFAULT NULL,
  `numero_cadastro` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `estadual` tinyint(1) DEFAULT NULL,
  `inscricao` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `logradouro` varchar(180) COLLATE latin1_general_ci DEFAULT NULL,
  `numero_endereco` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `complemento_endereco` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `cep` varchar(8) COLLATE latin1_general_ci DEFAULT NULL,
  `bairro` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `cidade` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE latin1_general_ci DEFAULT NULL,
  `website` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  `tipo_instituicao` smallint(5) unsigned DEFAULT NULL,
  `tipo_relacao` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_servicos_mensagens_contatos`
--

DROP TABLE IF EXISTS `cadastro_servicos_mensagens_contatos`;
CREATE TABLE IF NOT EXISTS `cadastro_servicos_mensagens_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(10) unsigned DEFAULT NULL,
  `provedor` smallint(5) unsigned DEFAULT NULL,
  `usuario` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idContato` (`contato`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_telefones_contatos`
--

DROP TABLE IF EXISTS `cadastro_telefones_contatos`;
CREATE TABLE IF NOT EXISTS `cadastro_telefones_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(10) unsigned DEFAULT NULL,
  `ddd` int(11) DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idContato` (`contato`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_telefones_vinculos`
--

DROP TABLE IF EXISTS `cadastro_telefones_vinculos`;
CREATE TABLE IF NOT EXISTS `cadastro_telefones_vinculos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vinculo` int(10) unsigned DEFAULT NULL,
  `ddd` int(11) DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `ramal` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `fax` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idContato` (`vinculo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadastro_vinculos`
--

DROP TABLE IF EXISTS `cadastro_vinculos`;
CREATE TABLE IF NOT EXISTS `cadastro_vinculos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contato` int(10) unsigned DEFAULT NULL,
  `instituicao` int(10) unsigned DEFAULT NULL,
  `departamento` varchar(180) COLLATE latin1_general_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE latin1_general_ci DEFAULT NULL,
  `cargo` int(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contato` (`contato`,`instituicao`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_acoes`
--

DROP TABLE IF EXISTS `caixa_acoes`;
CREATE TABLE IF NOT EXISTS `caixa_acoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  `projeto` int(10) unsigned DEFAULT NULL,
  `status` tinyint(4) unsigned DEFAULT NULL,
  `descricao` text COLLATE latin1_general_ci,
  `inicio` date DEFAULT NULL,
  `subordinada_a` int(10) unsigned DEFAULT NULL,
  `responsavel` int(10) unsigned DEFAULT NULL,
  `marco` tinyint(1) DEFAULT NULL,
  `requisito_para_recebimento` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_dependencias_acoes`
--

DROP TABLE IF EXISTS `caixa_dependencias_acoes`;
CREATE TABLE IF NOT EXISTS `caixa_dependencias_acoes` (
  `acao_que_depende` int(10) unsigned NOT NULL DEFAULT '0',
  `depende_de` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`depende_de`,`acao_que_depende`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_desembolsos`
--

DROP TABLE IF EXISTS `caixa_desembolsos`;
CREATE TABLE IF NOT EXISTS `caixa_desembolsos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projeto` int(10) unsigned DEFAULT NULL,
  `acao` int(10) unsigned DEFAULT NULL,
  `recurso` int(10) unsigned DEFAULT NULL,
  `valor_previsto` float DEFAULT NULL,
  `data_prevista` date DEFAULT NULL,
  `recorrente` smallint(6) DEFAULT NULL,
  `observacao` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto` (`projeto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_pagamentos`
--

DROP TABLE IF EXISTS `caixa_pagamentos`;
CREATE TABLE IF NOT EXISTS `caixa_pagamentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projeto` int(10) unsigned DEFAULT NULL,
  `acao` int(10) unsigned DEFAULT NULL,
  `recurso` int(10) unsigned DEFAULT NULL,
  `observacao` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `valor` int(10) unsigned DEFAULT NULL,
  `data` date DEFAULT NULL,
  `meio_pagamento` smallint(5) unsigned DEFAULT NULL,
  `comprovante` varchar(180) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto` (`projeto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_recebimentos`
--

DROP TABLE IF EXISTS `caixa_recebimentos`;
CREATE TABLE IF NOT EXISTS `caixa_recebimentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` int(10) unsigned DEFAULT NULL,
  `data_prevista` date DEFAULT NULL,
  `data_real` int(11) DEFAULT NULL,
  `valor_previsto` float DEFAULT NULL,
  `valor_real` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_recursos_humanos_acoes`
--

DROP TABLE IF EXISTS `caixa_recursos_humanos_acoes`;
CREATE TABLE IF NOT EXISTS `caixa_recursos_humanos_acoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recurso` int(10) unsigned DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `contrato` varchar(180) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa_recursos_materiais_acoes`
--

DROP TABLE IF EXISTS `caixa_recursos_materiais_acoes`;
CREATE TABLE IF NOT EXISTS `caixa_recursos_materiais_acoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recurso` int(10) unsigned DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `documento` varchar(180) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipes_competencias`
--

DROP TABLE IF EXISTS `equipes_competencias`;
CREATE TABLE IF NOT EXISTS `equipes_competencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipes_competencias_contatos`
--

DROP TABLE IF EXISTS `equipes_competencias_contatos`;
CREATE TABLE IF NOT EXISTS `equipes_competencias_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competencia` int(10) unsigned DEFAULT NULL,
  `disciplina` int(10) unsigned DEFAULT NULL,
  `contato` int(10) unsigned DEFAULT NULL,
  `disponibilidade` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `periodo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `custo_hora` float DEFAULT NULL,
  `observacao` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipes_disciplinas`
--

DROP TABLE IF EXISTS `equipes_disciplinas`;
CREATE TABLE IF NOT EXISTS `equipes_disciplinas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipes_projetos_contatos`
--

DROP TABLE IF EXISTS `equipes_projetos_contatos`;
CREATE TABLE IF NOT EXISTS `equipes_projetos_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` int(10) unsigned DEFAULT NULL,
  `data_prevista` date DEFAULT NULL,
  `data_real` int(11) DEFAULT NULL,
  `valor_previsto` float DEFAULT NULL,
  `valor_real` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores_produtos`
--

DROP TABLE IF EXISTS `fornecedores_produtos`;
CREATE TABLE IF NOT EXISTS `fornecedores_produtos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores_produtos_contatos`
--

DROP TABLE IF EXISTS `fornecedores_produtos_contatos`;
CREATE TABLE IF NOT EXISTS `fornecedores_produtos_contatos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produto` int(10) unsigned DEFAULT NULL,
  `contato` int(10) unsigned DEFAULT NULL,
  `observacao` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projetos_projetos`
--

DROP TABLE IF EXISTS `projetos_projetos`;
CREATE TABLE IF NOT EXISTS `projetos_projetos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `cliente` int(10) unsigned DEFAULT NULL,
  `our_responsible` int(10) unsigned DEFAULT NULL,
  `responsible_at_client` int(10) unsigned DEFAULT NULL,
  `date_begin` date DEFAULT NULL,
  `date_finish` date DEFAULT NULL,
  `value` float DEFAULT NULL,
  `nature_of_contract` int(10) unsigned DEFAULT NULL,
  `area_activity` int(10) unsigned DEFAULT NULL,
  `overhead` float DEFAULT NULL,
  `management_fee` float DEFAULT NULL,
  `object` text COLLATE latin1_general_ci,
  `summary` text COLLATE latin1_general_ci,
  `observation` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=6 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `projetos_desembolsos`
--

DROP TABLE IF EXISTS `projetos_desembolsos`;
CREATE TABLE IF NOT EXISTS `projetos_desembolsos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projeto` int(10) unsigned DEFAULT NULL,
  `acao` int(10) unsigned DEFAULT NULL,
  `recurso` int(10) unsigned DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `data_prevista` date DEFAULT NULL,
  `recorrente` smallint(6) DEFAULT NULL,
  `observacao` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto` (`projeto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projetos_naturezas_rubricas`
--

DROP TABLE IF EXISTS `projetos_naturezas_rubricas`;
CREATE TABLE IF NOT EXISTS `projetos_naturezas_rubricas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projetos_rubricas`
--

DROP TABLE IF EXISTS `projetos_rubricas`;
CREATE TABLE IF NOT EXISTS `projetos_rubricas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `projetos_taxas_desembolsos`
--

DROP TABLE IF EXISTS `projetos_taxas_desembolsos`;
CREATE TABLE IF NOT EXISTS `projetos_taxas_desembolsos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taxa` tinyint(4) unsigned DEFAULT NULL,
  `desembolso` int(10) unsigned DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `data` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `template_just_id_name`
--

DROP TABLE IF EXISTS `template_just_id_name`;
CREATE TABLE IF NOT EXISTS `template_just_id_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
