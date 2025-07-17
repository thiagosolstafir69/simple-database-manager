<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ThiagoWip\SimpleDatabaseManager\DbConnection;
use PDO;
use PDOStatement;
use PDOException;

/**
 * Classe específica para testes que permite injeção de PDO mock
 */
class TestableDbConnection extends DbConnection
{
  private $testPdo;

  public function __construct($pdo)
  {
    $this->testPdo = $pdo;
  }

  public function getConnection(): PDO
  {
    return $this->testPdo;
  }
}

class DbConnectionTest extends TestCase
{
  private MockObject $mockPdo;
  private MockObject $mockStatement;
  private TestableDbConnection $dbConnection;

  protected function setUp(): void
  {
    $this->mockPdo = $this->createMock(PDO::class);
    $this->mockStatement = $this->createMock(PDOStatement::class);
    $this->dbConnection = new TestableDbConnection($this->mockPdo);
  }

  public function testInsertSuccess(): void
  {
    $data = ['name' => 'João', 'email' => 'joao@test.com'];
    $expectedSql = "INSERT INTO users (name, email) VALUES (:name, :email)";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute')
      ->with($data)
      ->willReturn(true);

    $result = $this->dbConnection->insert('users', $data);

    $this->assertTrue($result);
  }

  public function testInsertWithInvalidTableName(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid table or field name');

    $this->dbConnection->insert('invalid-table!', ['name' => 'test']);
  }

  public function testInsertWithEmptyData(): void
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->dbConnection->insert('users', []);
  }

  public function testGetSingleSuccess(): void
  {
    $expectedData = (object) ['id' => 1, 'name' => 'João', 'email' => 'joao@test.com'];
    $expectedSql = "SELECT * FROM users WHERE id = :id";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute')
      ->with(['id' => 1]);

    $this->mockStatement
      ->expects($this->once())
      ->method('fetch')
      ->with(PDO::FETCH_OBJ)
      ->willReturn($expectedData);

    $result = $this->dbConnection->getSingle('users', 1);

    $this->assertEquals($expectedData, $result);
  }

  public function testGetSingleNotFound(): void
  {
    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute');

    $this->mockStatement
      ->expects($this->once())
      ->method('fetch')
      ->willReturn(false);

    $result = $this->dbConnection->getSingle('users', 999);

    $this->assertNull($result);
  }

  public function testUpdateSuccess(): void
  {
    $data = ['name' => 'João Atualizado'];
    $expectedSql = "UPDATE users SET name = :name WHERE id = :id";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute')
      ->with(['name' => 'João Atualizado', 'id' => 1])
      ->willReturn(true);

    $this->mockStatement
      ->expects($this->once())
      ->method('rowCount')
      ->willReturn(1);

    $result = $this->dbConnection->update('users', $data, 1);

    $this->assertTrue($result);
  }

  public function testDeleteSuccess(): void
  {
    $expectedSql = "DELETE FROM users WHERE id = :id";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute')
      ->with(['id' => 1])
      ->willReturn(true);

    $this->mockStatement
      ->expects($this->once())
      ->method('rowCount')
      ->willReturn(1);

    $result = $this->dbConnection->delete('users', 1);

    $this->assertTrue($result);
  }

  public function testGetAllSuccess(): void
  {
    $expectedData = [
      (object) ['id' => 1, 'name' => 'João'],
      (object) ['id' => 2, 'name' => 'Maria']
    ];
    $expectedSql = "SELECT * FROM users";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute');

    $this->mockStatement
      ->expects($this->once())
      ->method('fetchAll')
      ->with(PDO::FETCH_OBJ)
      ->willReturn($expectedData);

    $result = $this->dbConnection->getAll('users');

    $this->assertEquals($expectedData, $result);
  }

  public function testCountSuccess(): void
  {
    $expectedSql = "SELECT COUNT(*) FROM users";

    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->with($expectedSql)
      ->willReturn($this->mockStatement);

    $this->mockStatement
      ->expects($this->once())
      ->method('execute');

    $this->mockStatement
      ->expects($this->once())
      ->method('fetchColumn')
      ->willReturn('5');

    $result = $this->dbConnection->count('users');

    $this->assertEquals(5, $result);
  }

  public function testGetConnection(): void
  {
    $connection = $this->dbConnection->getConnection();

    $this->assertSame($this->mockPdo, $connection);
  }

  public function testDatabaseException(): void
  {
    $this->mockPdo
      ->expects($this->once())
      ->method('prepare')
      ->willThrowException(new PDOException('Connection failed'));

    $this->expectException(PDOException::class);
    $this->expectExceptionMessage('Connection failed');

    $this->dbConnection->insert('users', ['name' => 'test']);
  }
}
