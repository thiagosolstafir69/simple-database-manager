<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ThiagoWip\SimpleDatabaseManager\DbConnection;
use PDO;

class DatabaseIntegrationTest extends TestCase
{
  private DbConnection $db;
  private PDO $pdo;

  protected function setUp(): void
  {
    // Usar SQLite em memória para testes
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    // Criar uma versão testável de DbConnection que aceita PDO diretamente
    $this->db = new class($this->pdo) extends DbConnection {
      private PDO $testPdo;

      public function __construct(PDO $pdo)
      {
        $this->testPdo = $pdo;
      }

      public function getConnection(): PDO
      {
        return $this->testPdo;
      }
    };

    // Criar tabela de teste
    $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

    // Inserir dados de teste
    $this->pdo->exec("
            INSERT INTO users (name, email) VALUES 
            ('João Silva', 'joao@test.com'),
            ('Maria Santos', 'maria@test.com'),
            ('Pedro Costa', 'pedro@test.com'),
            ('Ana Oliveira', 'ana@test.com'),
            ('Carlos Lima', 'carlos@test.com')
        ");
  }

  public function testCompleteWorkflow(): void
  {
    // Teste de inserção
    $insertResult = $this->db->insert('users', [
      'name' => 'Novo Usuário',
      'email' => 'novo@test.com'
    ]);
    $this->assertTrue($insertResult);

    // Verificar se foi inserido
    $count = $this->db->count('users');
    $this->assertEquals(6, $count); // 5 iniciais + 1 novo

    // Buscar o usuário inserido
    $user = $this->db->getSingle('users', 6);
    $this->assertNotNull($user);
    $this->assertEquals('Novo Usuário', $user->name);
    $this->assertEquals('novo@test.com', $user->email);

    // Teste de atualização
    $updateResult = $this->db->update('users', [
      'name' => 'Usuário Atualizado'
    ], 6);
    $this->assertTrue($updateResult);

    // Verificar atualização
    $updatedUser = $this->db->getSingle('users', 6);
    $this->assertEquals('Usuário Atualizado', $updatedUser->name);

    // Teste de buscar todos
    $allUsers = $this->db->getAll('users');
    $this->assertCount(6, $allUsers);
    $this->assertContainsOnlyInstancesOf(\stdClass::class, $allUsers);

    // Verificar sintaxe de objeto (nunca arrays associativos)
    foreach ($allUsers as $user) {
      $this->assertIsObject($user);
      $this->assertObjectHasProperty('id', $user);
      $this->assertObjectHasProperty('name', $user);
      $this->assertObjectHasProperty('email', $user);
    }

    // Teste de exclusão
    $deleteResult = $this->db->delete('users', 6);
    $this->assertTrue($deleteResult);

    // Verificar exclusão
    $deletedUser = $this->db->getSingle('users', 6);
    $this->assertNull($deletedUser);

    $finalCount = $this->db->count('users');
    $this->assertEquals(5, $finalCount);
  }

  public function testPaginationWithRealData(): void
  {
    // Testar paginação se o método existir
    if (method_exists($this->db, 'getPagination')) {
      $result = $this->db->getPagination('users', 1, 2);
      $this->assertInstanceOf(\ThiagoWip\SimpleDatabaseManager\PaginationResult::class, $result);
      $this->assertCount(2, $result->data);
      $this->assertEquals(5, $result->total);
    } else {
      // Marcar como pulado se método não existir
      $this->markTestSkipped('Método getPagination não existe na DbConnection');
    }
  }

  public function testValidationWorksCorrectly(): void
  {
    // Teste com nome de tabela inválido
    $this->expectException(\InvalidArgumentException::class);
    $this->db->insert('invalid-table!', ['name' => 'test']);
  }

  public function testEmptyDataValidation(): void
  {
    // Se a validação de dados vazios existir
    try {
      $this->db->insert('users', []);
      $this->fail('Deveria ter lançado exceção para dados vazios');
    } catch (\Exception $e) {
      $this->assertInstanceOf(\InvalidArgumentException::class, $e);
    }
  }

  public function testCountFunctionality(): void
  {
    $count = $this->db->count('users');
    $this->assertEquals(5, $count);
    $this->assertIsInt($count);
  }

  public function testGetAllReturnsSortedData(): void
  {
    $users = $this->db->getAll('users');

    $this->assertCount(5, $users);

    // Verificar se os dados estão na ordem esperada (por ID)
    $expectedNames = ['João Silva', 'Maria Santos', 'Pedro Costa', 'Ana Oliveira', 'Carlos Lima'];

    for ($i = 0; $i < count($expectedNames); $i++) {
      $this->assertEquals($expectedNames[$i], $users[$i]->name);
    }
  }

  protected function tearDown(): void
  {
    // Limpar recursos
  }
}
