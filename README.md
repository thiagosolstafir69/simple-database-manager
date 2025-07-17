# Simple Database Manager

[![Latest Stable Version](https://poser.pugx.org/thiagowip/simple-database-manager/v/stable)](https://packagist.org/packages/thiagowip/simple-database-manager)
[![Total Downloads](https://poser.pugx.org/thiagowip/simple-database-manager/downloads)](https://packagist.org/packages/thiagowip/simple-database-manager)
[![License](https://poser.pugx.org/thiagowip/simple-database-manager/license)](https://packagist.org/packages/thiagowip/simple-database-manager)

Uma biblioteca PHP simples e elegante para gerenciar conexões de banco de dados com paginação automática. Desenvolvida seguindo os princípios SOLID e boas práticas do PHP moderno.

## ✨ Características

- 🚀 **Inicialização automática** - Uma linha configura tudo
- 🔒 **Seguro** - Prepared statements e validação de entrada
- 📄 **Paginação elegante** - Sistema completo de paginação
- 🎯 **Sintaxe de objeto** - `$data->total` em vez de `$data['total']`
- 🛡️ **Tipagem forte** - PHP 7.4+ com strict types
- 🔧 **PSR-4** - Autoloading automático via Composer

## 📦 Instalação

```bash
composer require thiagowip/simple-database-manager
```

## ⚙️ Configuração

Crie um arquivo `.env` na raiz do seu projeto:

```env
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=your_database
```

## 🚀 Uso Básico

### Inicialização (uma linha faz tudo!)

```php
<?php

require 'vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

// 🚀 Carrega .env, conecta banco, inicializa tudo automaticamente!
$app = App::boot();
```

### Operações CRUD

```php
$db = $app->getDatabase();

// Inserir dados
$success = $db->insert('users', [
    'name' => 'João Silva',
    'email' => 'joao@email.com'
]);

// Buscar um registro
$user = $db->getSingle('users', 1);
echo $user->name; // Sintaxe de objeto!

// Atualizar dados
$db->update('users', ['name' => 'João Santos'], 1);

// Deletar
$db->delete('users', 1);

// Buscar todos
$users = $db->getAll('users');
```

### Paginação Automática

```php
// Criar paginador (10 itens por página)
$paginator = $app->createPaginator('users', 10);
$data = $paginator->getResult();

// Acessar dados com sintaxe de objeto
echo "Total: " . $data->total;
echo "Páginas: " . $data->totalPages;
echo "Página atual: " . $data->currentPage;
echo "Tem próxima: " . ($data->hasNext ? 'Sim' : 'Não');

// Exibir dados
foreach ($data->data as $user) {
    echo $user->name . " - " . $user->email;
}

// Renderizar links de paginação
echo $paginator->render();
```

## 📝 Exemplo Completo

```php
<?php

require 'vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();

// Paginação automática
$paginator = $app->createPaginator('users', 5);
$data = $paginator->getResult();

echo "<h2>Usuários (Página {$data->currentPage})</h2>";

// Exibir em tabela
echo "<table>";
foreach ($data->data as $user) {
    echo "<tr>";
    echo "<td>{$user->id}</td>";
    echo "<td>{$user->name}</td>";
    echo "<td>{$user->email}</td>";
    echo "</tr>";
}
echo "</table>";

// Links de navegação
echo $paginator->render();

// Informações da paginação
echo "<p>Total: {$data->total} | Página {$data->currentPage} de {$data->totalPages}</p>";
```

## 🛡️ Segurança

- **Prepared Statements** - Proteção contra SQL Injection
- **Validação de entrada** - Nomes de tabelas e campos são validados
- **Tipagem forte** - Previne erros em tempo de execução
- **Sanitização** - Dados são tratados antes da exibição

## 🔧 Requisitos

- PHP >= 7.4
- PDO extension
- MySQL/MariaDB

## 📄 Licença

MIT License. Veja [LICENSE](LICENSE) para mais detalhes.

## 🤝 Contribuição

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 🐛 Reportar Bugs

Encontrou um bug? [Abra uma issue](https://github.com/thiagowip/simple-database-manager/issues)

## ⭐ Dê uma Estrela!

Se este projeto te ajudou, dê uma ⭐ no GitHub! 