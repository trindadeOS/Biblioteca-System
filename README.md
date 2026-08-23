# 📚 CETEPES Digital - Sistema de Gestão de Biblioteca

Sistema web completo para gerenciamento de biblioteca escolar/acadêmico, desenvolvido em **PHP** e **MySQL**, com controle de estoque de livros, painel para alunos, área para bibliotecários e administração geral.

---

## 🚀 Funcionalidades do Sistema

### 👨‍🎓 Módulo Aluno
* **Catálogo de Livros:** Visualização do acervo disponível.
* **Sistema de Reservas/Empréstimos:** Solicitação de livros de forma prática.
* **Cancelamento Inteligente:** Alunos podem cancelar pedidos pendentes, retornando automaticamente `+1` unidade ao estoque do livro de forma segura (`transactions` MySQL).

### 📖 Módulo Bibliotecário
* **Dashboard Operacional:** Visão geral dos empréstimos e fluxo da biblioteca.
* **Gerenciamento de Livros:** Cadastro, controle de quantidade e estoque.
* **Controle de Prazos:** Tela dedicada (`prazos.php`) para acompanhar devoluções pendentes e prazos de entrega baseados na data do empréstimo.
* **Mural de Avisos:** Comunicação direta com os usuários do sistema.

### 🛡️ Módulo Administrador
* Auditoria de acessos e ações no sistema.
* Criação e gerenciamento de usuários e permissões.

---

## 🛠️ Tecnologias Utilizadas

* **Linguagem:** PHP (com Programação Orientada a Objetos e Prepared Statements para segurança contra SQL Injection).
* **Banco de Dados:** MySQL / MariaDB.
* **Estilização:** CSS Customizado (Temas dinâmicos, Design responsivo).
* **Ambiente de Testes:** XAMPP / Linux.

---

## 📂 Estrutura do Projeto

```text
Biblioteca-System-main/
│
├── admin/               # Painel do Administrador (Auditoria, Usuários)
├── alunos/              # Painel do Aluno (Reservas, Login, Perfil)
├── bibliotecario/       # Painel do Bibliotecário (Prazos, Empréstimos, Livros)
├── conexao.php          # Arquivo central de conexão com o Banco de Dados
├── index.php            # Página inicial / Redirecionamento
├── login.php            # Autenticação geral
└── logout.php           # Encerramento de sessão

👨‍💻 Autor

Desenvolvido por trindadeOS.
