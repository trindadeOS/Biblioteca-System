-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Tempo de geração: 23/08/2026 às 22:40
-- Versão do servidor: 11.4.12-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_42024997_biblioteca`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(45) NOT NULL,
  `EMAIL` varchar(80) NOT NULL,
  `SENHA` varchar(20) NOT NULL,
  `STATUS` enum('ATIVO','DESATIVADO') DEFAULT 'ATIVO',
  `TURMA` varchar(50) DEFAULT NULL,
  `TURNO` varchar(50) DEFAULT NULL,
  `CURSO` varchar(50) DEFAULT NULL,
  `perfil_completo` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`ID`, `NOME`, `EMAIL`, `SENHA`, `STATUS`, `TURMA`, `TURNO`, `CURSO`, `perfil_completo`) VALUES
(7, 'Italo', 'fpsseven03@gmail.com', '$2y$10$VPXvYL.y.5Cs4', 'ATIVO', '2', 'Matutino', '2', 1),
(9, 'Italo Trindade Gama', 'teste@gmail.com', 'italo1234', 'ATIVO', '2 A', 'Vespertino', 'Informatica', 1),
(10, 'Italo teste de coisas', 'gamaflavio1000@gmail.com', '', 'ATIVO', '2', 'Vespertino', 'a', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `auditoria`
--

CREATE TABLE `auditoria` (
  `ID` int(11) NOT NULL,
  `Tabela_Afetada` varchar(20) DEFAULT NULL,
  `User_Responsavel` int(11) DEFAULT NULL,
  `Tipo_Operacao` enum('INSERT','UPDATE') DEFAULT NULL,
  `data_acao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `auditoria`
--

INSERT INTO `auditoria` (`ID`, `Tabela_Afetada`, `User_Responsavel`, `Tipo_Operacao`, `data_acao`) VALUES
(1, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(2, 'Clientes', 2, 'INSERT', '2026-08-23 01:47:36'),
(3, 'Clientes', 2, 'INSERT', '2026-08-23 01:47:36'),
(4, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(5, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(6, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(7, 'Clientes', 2, 'INSERT', '2026-08-23 01:47:36'),
(8, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(9, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(10, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(11, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(12, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(13, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(14, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36'),
(15, 'Usuarios', 1, 'INSERT', '2026-08-23 01:47:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avisos`
--

CREATE TABLE `avisos` (
  `ID` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensagem` text NOT NULL,
  `prioridade` varchar(20) DEFAULT 'normal',
  `data_criacao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `dias_prazo` int(11) DEFAULT 7,
  `limite_livros` int(11) DEFAULT 3,
  `ultramsg_instance` varchar(100) DEFAULT NULL,
  `ultramsg_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `dias_prazo`, `limite_livros`, `ultramsg_instance`, `ultramsg_token`) VALUES
(1, 7, 2, 'instance178315', 'qnt8fmss6rk6oohy');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(45) NOT NULL,
  `LIVRO` varchar(45) NOT NULL,
  `TELEFONE` varchar(11) NOT NULL,
  `STATUS` enum('CONCLUIDO','Cancelado','PENDENTE') DEFAULT 'PENDENTE',
  `DATA` datetime DEFAULT current_timestamp(),
  `aluno_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `emprestimos`
--

INSERT INTO `emprestimos` (`ID`, `NOME`, `LIVRO`, `TELEFONE`, `STATUS`, `DATA`, `aluno_id`) VALUES
(59, 'Italo teste de coisas', 'Curso Intensivo de MySQL', '', 'CONCLUIDO', '2026-08-23 22:32:19', 10),
(58, 'italo', 'Aprendendo SQL', '', 'CONCLUIDO', '2026-08-23 22:30:20', 2),
(57, 'italo', 'Curso Intensivo de MySQL', '', 'CONCLUIDO', '2026-08-23 22:29:40', 2),
(56, 'italo', 'Aprendendo SQL', '', 'CONCLUIDO', '2026-08-23 22:26:49', 2),
(55, 'italo', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 22:21:49', 2),
(54, 'italo', 'Curso Intensivo de MySQL', '', 'Cancelado', '2026-08-23 22:20:09', 2),
(53, 'italo', 'Desenvolvimento real de Software', '', 'CONCLUIDO', '2026-08-23 22:19:02', 2),
(52, 'Italo Trindade Gama', 'Curso Intensivo de MySQL', '', 'CONCLUIDO', '2026-08-23 22:13:25', 9),
(51, 'Seven Fps', 'Curso Intensivo de MySQL', '', 'PENDENTE', '2026-08-23 20:36:22', 7),
(50, 'Seven Fps', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 20:14:20', 7),
(49, 'italo', 'Curso Intensivo de MySQL', '', 'Cancelado', '2026-08-23 20:08:29', 2),
(48, 'italo', 'Desenvolvimento real de Software', '', 'CONCLUIDO', '2026-08-23 18:47:56', 2),
(47, 'Seven Fps', 'Desenvolvimento real de Software', '', 'CONCLUIDO', '2026-08-23 18:47:44', 7),
(46, 'Seven Fps', 'Desenvolvimento real de Software', '', 'CONCLUIDO', '2026-08-23 18:48:21', 7);

-- --------------------------------------------------------

--
-- Estrutura para tabela `emp_pessoal`
--

CREATE TABLE `emp_pessoal` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(30) DEFAULT NULL,
  `TURMA` varchar(3) DEFAULT NULL,
  `TURNO` varchar(6) DEFAULT NULL,
  `CURSO` varchar(15) DEFAULT NULL,
  `LIVRO` varchar(40) DEFAULT NULL,
  `DATA` date DEFAULT curdate(),
  `SERIE` varchar(10) DEFAULT NULL,
  `TELEFONE` varchar(20) DEFAULT NULL,
  `AVISO_ENVIADO` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `emp_pessoal`
--

INSERT INTO `emp_pessoal` (`ID`, `NOME`, `TURMA`, `TURNO`, `CURSO`, `LIVRO`, `DATA`, `SERIE`, `TELEFONE`, `AVISO_ENVIADO`) VALUES
(15, 'TRINDADEOS', '1', 'a', 'b', 'c', '2026-08-23', '2', '2', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `sinopse` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT 'Geral',
  `status` varchar(50) DEFAULT 'Disponivel',
  `imagem` varchar(255) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`id`, `titulo`, `autor`, `sinopse`, `categoria`, `status`, `imagem`, `quantidade`) VALUES
(14, 'Desenvolvimento real de Software', 'Gabriel Urma', 'Um livro sobre desenvolvimento de software', 'Programação', 'Disponivel', 'java-livro-09.jpg', 96),
(15, 'Aprendendo SQL', 'O\'Reilly', 'SQL', 'Programação', 'Disponivel', '911EOvjFRbL._UF1000,1000_QL80_.jpg', 95),
(16, 'Curso Intensivo de MySQL', 'Rick Silva', 'O livro Curso Intensivo de MySQL Ã© uma breve e prÃ¡tica introduÃ§Ã£o ao desenvolvimento baseado em bancos de dados relacionais. EstÃ¡ repleto de exemplos prÃ¡ticos e conselhos de especialistas que o farÃ£o aprender da forma mais rÃ¡pida possÃ­vel. VocÃª aprenderÃ¡ os conceitos bÃ¡sicos do SQL, como criar um banco de dados, criar queries SQL para extrair dados e trabalhar com eventos, procedures e funÃ§Ãµes. AprenderÃ¡ tambÃ©m a adicionar restriÃ§Ãµes a tabelas para implementar regras de permissÃµes de dados e a usar Ã­ndices para acelerar o acesso aos dados. VocÃª ainda explorarÃ¡ como chamar o MySQL a partir das linguagens PHP, Python e Java. Nos trÃªs projetos finais, aprenderÃ¡ a criar um banco de dados de condiÃ§Ãµes meteorolÃ³gicas do zero, a usar triggers a fim de evitar erros em um banco de dados eleitoral e a usar views para proteger dados confidenciais em um banco de dados salariais. VocÃª tambÃ©m aprenderÃ¡ a: â€¢ Consultar via query tabelas de banco de dados para obter informaÃ§Ãµes especÃ­ficas, ordenar os resultados, comentar o cÃ³digo SQL e a lidar com valores null â€¢ Definir as colunas da tabela para armazenar strings, nÃºmeros inteiros e datas e determinar quais tipos de dados usar â€¢ Fazer mÃºltiplos joins com tabelas de banco de dados, bem como usar tabelas temporÃ¡rias, expressÃµes de tabela comuns, tabelas derivadas e subqueries â€¢ Adicionar, alterar e remover dados de tabelas, criar views com base em queries especÃ­ficas, escrever rotinas armazenadas reutilizÃ¡veis e automatizar e agendar eventos Como o Curso Intensivo de MySQL Ã© o recurso de inÃ­cio rÃ¡pido e perfeito para desenvolvedores de banco de dados, vocÃª conhecerÃ¡ as ferramentas de que precisa para criar e gerenciar sistemas de armazenamento de dados baseados em MySQL rÃ¡pidos, poderosos e seguros.', 'Programação', 'Disponivel', 'mysql.jpg', 96);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(50) NOT NULL,
  `SENHA` varchar(30) NOT NULL,
  `CPF` varchar(11) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `TELEFONE` varchar(11) NOT NULL,
  `TIPO` enum('Bibliotecario','Admin') DEFAULT 'Bibliotecario'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`ID`, `NOME`, `SENHA`, `CPF`, `EMAIL`, `TELEFONE`, `TIPO`) VALUES
(1, 'Trindade', '1234', '111111', 'trindadedev@gmail.com', '71717171', 'Admin'),
(2, 'italo', '1234', '1111', 'trindadedev2@gmail.com', '73999112030', 'Bibliotecario');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Índices de tabela `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `emp_pessoal`
--
ALTER TABLE `emp_pessoal`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `CPF` (`CPF`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`),
  ADD UNIQUE KEY `TELEFONE` (`TELEFONE`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `avisos`
--
ALTER TABLE `avisos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de tabela `emp_pessoal`
--
ALTER TABLE `emp_pessoal`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `livros`
--
ALTER TABLE `livros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
