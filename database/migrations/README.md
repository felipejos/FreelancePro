# Migrations

Este diretório contém os arquivos SQL de migração do banco de dados.

## Como usar

### 1. Criar nova migration
```bash
php migrate.php create NomeDaMigration
```

### 2. Editar o arquivo SQL gerado
Abra o arquivo criado em `database/migrations/` e adicione seus comandos SQL.

### 3. Executar manualmente no banco
Execute o SQL no seu cliente MySQL (phpMyAdmin, MySQL Workbench, linha de comando, etc.)

### 4. Marcar como executada
```bash
php migrate.php mark 2024_01_15_120000_nome_da_migration.sql
```

### 5. Ver status
```bash
php migrate.php status
```

## Exemplo de migration

```sql
-- ============================================
-- Migration: AddPhoneToUsers
-- Data: 2024-01-15 12:00:00
-- ============================================

-- UP: Alterações a serem aplicadas
ALTER TABLE `user_profiles` ADD COLUMN `phone_verified` BOOLEAN DEFAULT FALSE AFTER `phone`;
```

## Importante

- **NUNCA** modifique o arquivo `schema.sql` após a primeira execução
- Sempre use migrations para alterações no banco
- Execute as migrations manualmente e depois marque como executadas
