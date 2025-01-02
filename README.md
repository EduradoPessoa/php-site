# 🚀 Sistema de Gestão Empresarial

Sistema web moderno para gestão empresarial com múltiplos níveis de acesso, desenvolvido em PHP com foco em segurança e boas práticas.

## 📋 Sobre o Projeto

Este sistema implementa uma plataforma completa de gestão empresarial com autenticação, controle de acesso baseado em papéis (RBAC) e gerenciamento de diferentes tipos de usuários.

### 🎯 Funcionalidades

#### Autenticação e Segurança
- 🔐 Sistema de login seguro com proteção contra ataques
- 🔑 Recuperação de senha via email
- 🛡️ Proteção CSRF em formulários
- ⚡ Rate limiting para prevenção de força bruta
- 📝 Sistema de logs de atividades

#### Gestão de Usuários
- 👥 Registro e aprovação de usuários
- 👮‍♂️ Diferentes níveis de acesso:
  - Administrador: Acesso total ao sistema
  - Funcionário: Acesso às funcionalidades internas
  - Cliente: Acesso à área do cliente
  - Fornecedor: Acesso ao portal de fornecedores
- 🖼️ Perfil de usuário com avatar
- 🔔 Sistema de notificações

#### Painel Administrativo
- 📊 Dashboard com métricas importantes
- 👥 Gestão completa de usuários
- 📝 Logs de atividades do sistema
- ⚙️ Configurações do sistema

### 💾 Banco de Dados
- SQLite para facilitar instalação
- Migrations para versionamento
- Seeds para dados iniciais
- Backup automático diário

## 🔧 Requisitos

- PHP 7.4 ou superior
- Extensões PHP necessárias:
  - PDO SQLite
  - GD (para manipulação de imagens)
  - OpenSSL (para criptografia)
  - Fileinfo (para upload de arquivos)
- Servidor web (Apache/Nginx) ou PHP built-in server
- Permissões de escrita nos diretórios:
  - /data
  - /uploads
  - /logs

## 🚀 Instalação

1. Clone o repositório:
    ```bash
    git clone https://github.com/EduradoPessoa/php-site.git
    cd php-site
    ```

2. Configure as permissões:
    ```bash
    chmod 777 data uploads logs
    ```

3. Execute as migrations:
    ```bash
    php database/migrate.php
    ```

4. Inicie o servidor:
    ```bash
    php -S localhost:8888 -t public
    ```

5. Acesse no navegador:
    ```
    http://localhost:8888
    ```

## 👤 Credenciais Iniciais

**Administrador**
- Email: eduardo@phoenyx.com.br
- Senha: 123456

## 📁 Estrutura do Projeto

```
php-site/
├── admin/              # Área administrativa
├── config/             # Configurações
│   ├── config.php     # Configurações gerais
│   └── database.php   # Configuração do banco
├── data/              # Dados do sistema
│   └── database.sqlite # Banco SQLite
├── database/          # Gerenciamento do banco
│   ├── migrations/    # Migrações
│   └── seeds/        # Seeds
├── includes/          # Arquivos incluídos
│   ├── auth.php      # Autenticação
│   └── functions.php # Funções úteis
├── logs/             # Logs do sistema
├── public/           # Arquivos públicos
│   ├── css/         # Estilos
│   ├── js/          # Scripts
│   └── uploads/     # Uploads públicos
└── src/              # Código fonte
    ├── Controllers/  # Controladores
    ├── Models/       # Modelos
    └── Utils/        # Utilitários
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie sua branch: `git checkout -b feature/nova-funcionalidade`
3. Commit suas mudanças: `git commit -m 'Adiciona nova funcionalidade'`
4. Push para a branch: `git push origin feature/nova-funcionalidade`
5. Abra um Pull Request

## 📝 Changelog

### v1.1.0 (Em desenvolvimento)
- Sistema de notificações
- Upload de avatar
- Recuperação de senha
- Proteção CSRF
- Rate limiting
- Sistema de logs

### v1.0.0
- Sistema base
- Autenticação
- Controle de acesso
- Gestão de usuários

## 📜 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

Eduardo Pessoa
- GitHub: [@EduradoPessoa](https://github.com/EduradoPessoa)
- Email: eduardo@phoenyx.com.br
