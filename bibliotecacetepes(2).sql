-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 23/08/2026 às 07:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bibliotecacetepes`
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
  `STATUS` enum('ATIVO','DESATIVADO') DEFAULT 'ATIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`ID`, `NOME`, `EMAIL`, `SENHA`, `STATUS`) VALUES
(7, 'Seven Fps', 'fpsseven03@gmail.com', '$2y$10$VPXvYL.y.5Cs4', 'ATIVO'),
(8, 'faixa 10 Ssssa', 'gamaflavio1000@gmail.com', '$2y$10$KeKNu0DjrgiRR', 'ATIVO'),
(9, 'TRINDADEOS', 'teste@gmail.com', 'italo1234', 'ATIVO');

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
(1, 7, 3, 'instance178315', 'qnt8fmss6rk6oohy');

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
(1, 'Seven Fps', 'Java: Como Programar', '53', 'Cancelado', '2026-07-02 12:01:33', 7),
(2, 'Seven Fps', 'programador sql', '73981035809', 'Cancelado', '2026-07-02 12:02:24', 7),
(3, 'Seven Fps', 'programador sql', '73981035809', 'Cancelado', '2026-07-02 12:04:29', 7),
(4, 'Seven Fps', 'Java: Como Programar', '3123', 'Cancelado', '2026-07-02 12:13:44', 7),
(5, 'Seven Fps', 'Java: Como Programar', '34', 'Cancelado', '2026-07-02 12:14:01', 7),
(6, 'Seven Fps', 'Java: Como Programar', '43', 'Cancelado', '2026-07-02 12:14:04', 7),
(7, 'Trindade', 'Java: Como Programar', '73981035809', 'Cancelado', '2026-07-02 12:51:27', 7),
(8, 'Seven Fps', 'Java: Como Programar', '888', 'Cancelado', '2026-07-02 16:35:14', 7),
(9, 'Seven Fps', 'programador sql', '737473', 'Cancelado', '2026-08-21 01:02:24', 7),
(10, 'Seven Fps', 'Java: Como Programar', 'juuu', 'Cancelado', '2026-08-21 01:07:46', 7),
(11, 'Seven Fps', 'Java: Como Programar', 'iiiiii', 'Cancelado', '2026-08-21 01:07:50', 7),
(12, 'TRINDADEOS', 'programador sql', '73981035809', 'Cancelado', '2026-08-22 20:11:48', 9),
(13, 'TRINDADEOS', 'TrindadeOS', '73981035809', 'Cancelado', '2026-08-22 20:24:57', 9),
(14, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:17:48', 9),
(15, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:19:16', 9),
(16, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:19:19', 9),
(17, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:19:22', 9),
(18, 'TRINDADEOS', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 02:22:29', 9),
(19, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:23:12', 9),
(20, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:27:12', 9),
(21, 'TRINDADEOS', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 02:27:48', 9),
(22, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:29:17', 9),
(23, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:29:49', 9),
(24, 'TRINDADEOS', 'programador sql', '', 'Cancelado', '2026-08-23 02:30:41', 9),
(25, 'TRINDADEOS', 'TrindadeOS', '', 'Cancelado', '2026-08-23 02:37:27', 9),
(26, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 03:24:17', 1),
(27, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 03:25:52', 1),
(28, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 03:25:57', 1),
(29, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 03:29:46', 1),
(30, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 03:30:30', 1),
(31, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 04:15:37', 1),
(32, 'Trindade', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 04:15:53', 1),
(33, 'italo', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 06:16:59', 2),
(34, 'italo', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 06:32:21', 2),
(35, 'italo', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 06:36:17', 2),
(36, 'italo', 'Java: Como Programar', '', 'Cancelado', '2026-08-23 06:36:24', 2),
(37, 'italo', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 06:54:09', 2),
(38, 'Trindade', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 06:56:03', 1),
(39, 'Trindade', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 06:56:09', 1),
(40, 'Trindade', 'Aprendendo SQL', '', 'Cancelado', '2026-08-23 06:56:27', 1);

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
(6, 'italo', 'A', 'VESP', 'INF', 'Java', '2026-05-28', '2', '384343', 1),
(5, 'italo', 'A', 'VESP', 'INF', 'GestÃ£o Comercial', '2026-05-28', '2', '838383', 1),
(4, 'Trindade', 'A', 'VESP', 'INF', 'PHP', '2026-05-25', '2', '73', 1),
(7, 'debora', 'a', 'vesp', 'inf', 'fundamentos Java', '2026-06-01', '2', '73981683838', 1),
(8, 'Trindade testes', 'A', 'Vesp', 'InF', 'SQLITE', '2026-06-01', '1', '73981035809', 1),
(9, 'adler', 'A', 'vespet', 'tec.informatica', '20mil leguas submarina', '2026-06-02', '2 ano', '73991019784', 1),
(10, 'jessica', 'a', 'vesp', 'inf', 'matematica', '2026-06-02', '1', '73999347646', 1),
(12, 'trindadev2', 'a', 'vesp', 'inf', 'Java para burros', '2026-08-22', '2', '73981035909', 1),
(13, 'sad', 'a', 'vesp', 'inf', 'Java: Como Programar', '2026-08-23', '2', '333', 0),
(14, 'trtr', 'a', 'vesp', 'infffff', 'imggg', '2026-08-23', '2', '222', 0);

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
(14, 'Desenvolvimento real de Software', 'Gabriel Urma', 'Um livro sobre desenvolvimento de software', 'Programação', 'Disponivel', 'java-livro-09.jpg', 100),
(15, 'Aprendendo SQL', 'O\'Reilly', 'SQL', 'Programação', 'Disponivel', '911EOvjFRbL._UF1000,1000_QL80_.jpg', 100);

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
(2, 'italo', '1234', '1111', 'trindadedev2@gmail.com', '73999112030', 'Bibliotecario'),
(8, 'Trindade bibliotecario', '1234', '12312312', 'testeeeee@gmail.com', '9192391', 'Bibliotecario'),
(9, 'debora', '1234', '8583839t8', 'mimserdev@gmail.com', '995939848', 'Bibliotecario'),
(10, 'andrade ', '1234', '05334265538', 'adler@gmail.com', '73991019784', 'Bibliotecario'),
(11, 'PROFESSORA', '1234', '129123', 'TESTEE@GMAIL.COM', '1237123', 'Bibliotecario'),
(12, 'Trindadev2', 'italo1234', '10910910952', 'trindadedevteste020@gmail.com', '73981035809', 'Bibliotecario'),
(13, 'Trindadev2', 'italo1234', '2131232', 'trindadeteste00@gmail.com', '739810935', 'Bibliotecario'),
(14, '123123', '123123', '1123123', '123123213@g', '213123', 'Bibliotecario');

--
-- Índices para tabelas despejadas
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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `avisos`
--
ALTER TABLE `avisos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `emp_pessoal`
--
ALTER TABLE `emp_pessoal`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `livros`
--
ALTER TABLE `livros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
