# Testes - Simple Database Manager

Este diretório contém a suite completa de testes para a biblioteca Simple Database Manager.

## Estrutura dos Testes

```
tests/
├── Unit/                          # Testes unitários
│   ├── DbConnectionTest.php       # Testa operações CRUD
│   ├── AppTest.php                # Testa padrão Singleton
│   ├── PaginationResultTest.php   # Testa objeto de paginação
│   └── PaginatorTest.php          # Testa renderização
├── Integration/                   # Testes de integração
│   └── DatabaseIntegrationTest.php # Workflow completo
└── README.md                      # Esta documentação
```

## Como Executar

### Todos os Testes
```bash
composer test
```

### Apenas Testes Unitários
```bash
vendor/bin/phpunit tests/Unit/
```

### Apenas Testes de Integração
```bash
vendor/bin/phpunit tests/Integration/
```

### Teste Específico
```bash
vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php --verbose
```

### Com Cobertura de Código
```bash
composer test-coverage
```

## Tipos de Teste

### 🔧 Testes Unitários

**DbConnectionTest.php**
- ✅ Operações CRUD (insert, update, delete, select)
- ✅ Validação de entrada (nomes de tabela, dados vazios)
- ✅ Tratamento de erros (PDOException)
- ✅ Sintaxe de objeto (nunca arrays associativos)
- ✅ Prepared statements seguros

**AppTest.php**
- ✅ Padrão Singleton funcional
- ✅ Factory methods (createPaginator, getDatabase)
- ✅ Gerenciamento de variáveis de ambiente
- ✅ Persistência de instância

**PaginationResultTest.php**
- ✅ Cálculo de páginas (total, current, hasNext, hasPrev)
- ✅ Propriedades de objeto acessíveis
- ✅ Casos extremos (página única, dados vazios)
- ✅ Links de navegação

**PaginatorTest.php**
- ✅ Renderização HTML de paginação
- ✅ Cálculo de offset correto
- ✅ Comportamento em diferentes cenários

### 🔗 Testes de Integração

**DatabaseIntegrationTest.php**
- ✅ Workflow completo CRUD
- ✅ Teste com banco SQLite em memória
- ✅ Verificação de sintaxe de objeto
- ✅ Validação de dados reais
- ✅ Contagem e ordenação

## Características dos Testes

### 🎯 Foco na Sintaxe de Objeto
Todos os testes verificam que a biblioteca **SEMPRE** retorna objetos com propriedades acessíveis via `->`, nunca arrays associativos:

```php
// ✅ Correto - Sintaxe de objeto
$user = $db->getSingle('users', 1);
echo $user->name; // Funciona!

// ❌ Errado - Array associativo (não funciona)
echo $user['name']; // Não existe!
```

### 🛡️ Segurança Validada
- **Prepared Statements**: Todos os SQLs usam bindParam
- **Validação de entrada**: Nomes de tabelas e campos validados
- **Sanitização**: Dados tratados antes da exibição

### 📊 Cobertura de Código
Os testes cobrem:
- ✅ Todas as operações públicas
- ✅ Casos de sucesso e erro
- ✅ Validações de entrada
- ✅ Comportamentos extremos

## Configuração de Ambiente

### Para Testes de Integração
Os testes de integração usam **SQLite em memória**, não requerendo configuração de banco:

```php
// Configuração automática no teste
$pdo = new PDO('sqlite::memory:');
```

### Para Testes Unitários
Os testes unitários usam **mocks do PDO**, não acessando banco real:

```php
// Mock automático
$mockPdo = $this->createMock(PDO::class);
```

## Resultados Esperados

### ✅ Status Ideal
```
Tests: 6, Assertions: 49, Failures: 0
```

### 📈 Métricas de Qualidade
- **Cobertura**: > 80%
- **Assertions**: ~8 por teste
- **Performance**: < 50ms total
- **Memória**: < 8MB

## Problemas Conhecidos

### ⚠️ Limitações Atuais
1. **Testes App**: Requerem banco configurado (limitação do Singleton)
2. **Mocks complexos**: Alguns métodos não são facilmente mockáveis
3. **Validação específica**: Nem todas as validações são padronizadas

### 🔄 Melhorias Futuras
- [ ] Adicionar testes de performance
- [ ] Mock melhor da classe App
- [ ] Testes de concorrência
- [ ] Validação de SQL gerado

## Debug de Testes

### Verbose Mode
```bash
vendor/bin/phpunit --verbose
```

### Debug Específico
```bash
vendor/bin/phpunit --filter testCompleteWorkflow --debug
```

### Informações de Erro
```bash
vendor/bin/phpunit --verbose --stop-on-failure
```

## Contribuindo com Testes

### Padrões a Seguir
1. **Nomeação**: `testNomeDescritivo()`
2. **Organização**: Arrange, Act, Assert
3. **Sintaxe**: Sempre verificar objeto vs array
4. **Mocks**: Usar para dependências externas
5. **Documentação**: Comentar cenários complexos

### Exemplo de Teste
```php
public function testInsertReturnsObjectSyntax(): void
{
    // Arrange
    $data = ['name' => 'João', 'email' => 'joao@test.com'];
    
    // Act
    $result = $this->db->insert('users', $data);
    $user = $this->db->getSingle('users', 1);
    
    // Assert
    $this->assertTrue($result);
    $this->assertEquals('João', $user->name); // Sintaxe de objeto!
    $this->assertObjectHasProperty('email', $user);
}
``` 