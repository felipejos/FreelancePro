# FreelancePro

Plataforma Corporativa de Treinamentos + Gestão de Freelancers com IA

## Requisitos

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache com mod_rewrite habilitado
- Extensões PHP: PDO, pdo_mysql, curl, json, mbstring

## Instalação

### 1. Configurar o Banco de Dados

1. Crie um banco de dados MySQL:
```sql
CREATE DATABASE freelancepro_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Execute o schema inicial:
```bash
mysql -u root -p freelancepro_dev < database/schema.sql
```

### 2. Configurar a Aplicação

1. Edite `config/database.php` e configure suas credenciais:
```php
$environment = 'development'; // ou 'production'

// Configurações de desenvolvimento
'development' => [
    'host'     => 'localhost',
    'database' => 'freelancepro_dev',
    'username' => 'root',
    'password' => '',
],
```

2. Edite `config/app.php` se necessário:
```php
'url' => 'http://localhost/site-freelancePro',
```

### 3. Configurar Apachee

Certifique-se de que o mod_rewrite está habilitado e o AllowOverride está configurado.

### 4. Acessar o Sistema

- URL: `http://localhost/site-freelancePro`
- **Admin padrão:**
  - Email: `admin@freelancepro.com`
  - Senha: `![alt text](image.png)`

## Estrutura do Projeto

```
site-freelancePro/
├── app/
│   ├── Controllers/     # Controladores
│   ├── Core/            # Classes base do MVC
│   ├── Helpers/         # Funções auxiliares
│   ├── Middlewares/     # Middlewares de autenticação
│   ├── Models/          # Models do banco de dados
│   ├── Services/        # Serviços externos (OpenAI, ASSAS, Email)
│   └── Views/           # Templates das páginas
├── config/
│   ├── app.php          # Configurações gerais
│   └── database.php     # Configuração do banco (DEV/PROD)
├── database/
│   ├── schema.sql       # Schema inicial (executar apenas 1x)
│   └── migrations/      # Migrations para alterações
├── public/
│   ├── index.php        # Ponto de entrada
│   └── .htaccess        # Rewrite rules
├── routes/
│   └── web.php          # Definição de rotas
├── migrate.php          # CLI para migrations
└── README.md
```

## Sistema de Migrations

Após executar o `schema.sql` inicial, **NÃO** modifique-o diretamente. Use migrations:

### Criar nova migration:
```bash
php migrate.php create AddColumnToTable
```

### Marcar como executada (após rodar o SQL manualmente):
```bash
php migrate.php mark 2024_01_15_120000_add_column_to_table.sql
```

### Ver status:
```bash
php migrate.php status
```

## Configurações do Admin

Acesse o painel admin (`/admin/dashboard`) para configurar:

- **API Keys**: OpenAI, ASSAS
- **Email**: Servidor SMTP
- **Valores**: Taxas de registro, mensalidade, playbook, freelancer

## Módulos

1. **Autenticação**: Login, Cadastro, Reset de senha
2. **Dashboard Empresa**: Estatísticas e ações rápidas
3. **Playbooks/Treinamentos**: Geração com IA
4. **Cursos**: Módulos e aulas com IA
5. **Funcionários**: Gestão e atribuição de treinamentos
6. **Freelancers**: Projetos, propostas e contratos
7. **Pagamentos**: Integração ASSAS
8. **Admin**: Configurações do sistema

## Tipos de Usuário

- `admin`: Administrador da plataforma
- `company`: Empresa (cliente principal)
- `professional`: Freelancer
- `employee`: Funcionário de empresa

## Integração ASSAS

Configure a chave API no painel admin. Suporta:
- Assinaturas com cartão de crédito
- Webhooks para atualização de status
- Ambientes: Sandbox e Produção

## Integração OpenAI

Configure a chave API no painel admin. Usado para:
- Geração de playbooks/treinamentos
- Criação de cursos completos
- Geração de questionários

## Suporte

Para dúvidas ou problemas, verifique os logs em ambiente de desenvolvimento (`debug = true` em `config/app.php`).
