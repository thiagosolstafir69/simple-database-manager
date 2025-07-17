<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ThiagoWip\SimpleDatabaseManager\App;
use ThiagoWip\SimpleDatabaseManager\DbConnection;
use ThiagoWip\SimpleDatabaseManager\Paginator;

class AppTest extends TestCase
{
  protected function setUp(): void
  {
    // Resetar o singleton antes de cada teste
    $reflection = new \ReflectionClass(App::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
  }

  public function testBootReturnsSingletonInstance(): void
  {
    // Criar variáveis de ambiente mock
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_USER=root');
    putenv('DB_PASS=');
    putenv('DB_NAME=test_db');

    $app1 = App::boot();
    $app2 = App::boot();

    $this->assertSame($app1, $app2, 'App::boot() deve retornar a mesma instância (Singleton)');
  }

  public function testGetDatabaseReturnsDbConnectionInstance(): void
  {
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_USER=root');
    putenv('DB_PASS=');
    putenv('DB_NAME=test_db');

    $app = App::boot();
    $database = $app->getDatabase();

    $this->assertInstanceOf(DbConnection::class, $database);
  }

  public function testCreatePaginatorReturnsCorrectInstance(): void
  {
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_USER=root');
    putenv('DB_PASS=');
    putenv('DB_NAME=test_db');

    $app = App::boot();
    $paginator = $app->createPaginator('users', 10);

    $this->assertInstanceOf(Paginator::class, $paginator);
  }

  public function testCreatePaginatorWithDefaultPerPage(): void
  {
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_USER=root');
    putenv('DB_PASS=');
    putenv('DB_NAME=test_db');

    $app = App::boot();
    $paginator = $app->createPaginator('users');

    $this->assertInstanceOf(Paginator::class, $paginator);
  }

  public function testBootWithMissingEnvironmentVariables(): void
  {
    // Limpar variáveis de ambiente
    putenv('DB_HOST');
    putenv('DB_USER');
    putenv('DB_PASS');
    putenv('DB_NAME');

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Variáveis de ambiente não configuradas');

    App::boot();
  }

  public function testSingletonInstancePersistence(): void
  {
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_USER=root');
    putenv('DB_PASS=');
    putenv('DB_NAME=test_db');

    $app1 = App::boot();
    $database1 = $app1->getDatabase();

    $app2 = App::boot();
    $database2 = $app2->getDatabase();

    $this->assertSame($app1, $app2);
    $this->assertSame($database1, $database2);
  }

  protected function tearDown(): void
  {
    // Limpar variáveis de ambiente após cada teste
    putenv('DB_HOST');
    putenv('DB_USER');
    putenv('DB_PASS');
    putenv('DB_NAME');

    // Resetar singleton
    $reflection = new \ReflectionClass(App::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
  }
}
