# Simple Database Manager

[![Latest Stable Version](https://poser.pugx.org/thiagowip/simple-database-manager/v/stable)](https://packagist.org/packages/thiagowip/simple-database-manager)
[![Total Downloads](https://poser.pugx.org/thiagowip/simple-database-manager/downloads)](https://packagist.org/packages/thiagowip/simple-database-manager)
[![License](https://poser.pugx.org/thiagowip/simple-database-manager/license)](https://packagist.org/packages/thiagowip/simple-database-manager)

Uma biblioteca PHP simples e elegante para gerenciar conexões de banco de dados com paginação automática, CRUD completo e interface moderna.

## Características

- **Inicialização automática** - Uma linha configura tudo
- **Seguro** - Prepared statements e validação
- **Paginação elegante** - Sistema completo de paginação
- **Interface moderna** - Design responsivo com glassmorphism
- **AJAX integrado** - Formulários assíncronos com validação
- **Sintaxe de objeto** - `$data->total` em vez de `$data['total']`
- **PHP 7.4+** - Tipagem forte e recursos modernos

## Instalação

```bash
composer require thiagowip/simple-database-manager
```

## Configuração

Crie um arquivo `.env` na raiz do projeto:

```env
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=your_database
```

## Uso Básico

### Inicialização

```php
<?php
require 'vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

// Carrega .env, conecta banco e inicializa tudo automaticamente
$app = App::boot();
```

### CRUD Simples

```php
$db = $app->getDatabase();

// Inserir
$db->insert('users', ['name' => 'João', 'email' => 'joao@email.com']);

// Buscar um
$user = $db->getSingle('users', 1);
echo $user->name; // Sintaxe de objeto

// Atualizar
$db->update('users', ['name' => 'João Santos'], 1);

// Deletar
$db->delete('users', 1);

// Buscar todos
$users = $db->getAll('users');
```

### Paginação

```php
// Criar paginador (10 itens por página)
$paginator = $app->createPaginator('users', 10);
$data = $paginator->getResult();

// Informações da paginação
echo "Total: {$data->total} | Página {$data->currentPage} de {$data->totalPages}";

// Exibir dados
foreach ($data->data as $user) {
    echo "{$user->name} - {$user->email}<br>";
}

// Links de navegação
echo $paginator->render();
```

## Exemplos Práticos

A biblioteca inclui exemplos completos na pasta `examples/`:

| Arquivo | Descrição |
|---------|-----------|
| `formulario_ajax.php` | **🆕** Interface moderna com AJAX, validação em tempo real |
| `crud_basico.php` | Operações básicas de CRUD |
| `listagem_completa.php` | Lista todos os dados sem paginação |
| `busca_com_filtros.php` | Sistema de busca com filtros |
| `busca_melhorada.php` | Busca avançada com score de relevância |

### Como executar os exemplos

```bash
# Iniciar servidor local
php -S localhost:8000

# Acessar exemplos
http://localhost:8000/examples/crud_basico.php
```

## Busca Personalizada

```php
$db = $app->getDatabase();

// Busca com prepared statements
$sql = "SELECT * FROM users WHERE name LIKE :search";
$stmt = $db->getConnection()->prepare($sql);
$stmt->execute(['search' => '%João%']);
$users = $stmt->fetchAll();
```

## Segurança

- **Prepared Statements** - Proteção contra SQL Injection
- **Validação de entrada** - Nomes de tabelas validados
- **Tipagem forte** - Previne erros em runtime

## Requisitos

- PHP >= 7.4
- PDO extension
- MySQL/MariaDB

## Testes

A biblioteca inclui uma suite completa de testes:

```bash
# Executar todos os testes
composer test

# Apenas testes unitários
vendor/bin/phpunit tests/Unit/

# Apenas testes de integração
vendor/bin/phpunit tests/Integration/

# Com cobertura de código
composer test-coverage
```

### O que é testado:
- ✅ **Operações CRUD** - Insert, update, delete, select
- ✅ **Sintaxe de objeto** - Sempre `$data->propriedade`, nunca arrays
- ✅ **Segurança** - Prepared statements e validação
- ✅ **Paginação** - Cálculos e renderização
- ✅ **Padrão Singleton** - App::boot() funcional
- ✅ **Workflow completo** - Testes de integração

Veja [tests/README.md](tests/README.md) para documentação completa dos testes.

## Licença

MIT License. Veja [LICENSE](LICENSE) para detalhes.

## Contribuição

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

---

⭐ **Gostou do projeto? Dê uma estrela no GitHub!**