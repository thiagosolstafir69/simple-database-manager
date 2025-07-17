<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ThiagoWip\SimpleDatabaseManager\Paginator;
use ThiagoWip\SimpleDatabaseManager\DbConnection;
use ThiagoWip\SimpleDatabaseManager\PaginationResult;

class PaginatorTest extends TestCase
{
  private $mockDb;
  private $paginator;

  protected function setUp(): void
  {
    $this->mockDb = $this->createMock(DbConnection::class);
    $this->paginator = new Paginator($this->mockDb, 'users', 10, 1);
  }

  public function testGetResultReturnsCorrectPaginationResult(): void
  {
    $mockData = [
      (object) ['id' => 1, 'name' => 'João'],
      (object) ['id' => 2, 'name' => 'Maria']
    ];

    // Mock do count
    $this->mockDb
      ->expects($this->once())
      ->method('count')
      ->with('users')
      ->willReturn(25);

    // Mock do getPageData
    $this->mockDb
      ->expects($this->once())
      ->method('getPageData')
      ->with('users', 1, 10)
      ->willReturn($mockData);

    $result = $this->paginator->getResult();

    $this->assertInstanceOf(PaginationResult::class, $result);
    $this->assertEquals($mockData, $result->data);
    $this->assertEquals(25, $result->total);
    $this->assertEquals(1, $result->currentPage);
    $this->assertEquals(3, $result->totalPages); // ceil(25/10) = 3
  }

  public function testRenderGeneratesCorrectHTML(): void
  {
    // Configurar mock para simular página 2 de 5
    $this->mockDb
      ->method('count')
      ->willReturn(50);

    $this->mockDb
      ->method('getPageData')
      ->willReturn([]);

    $paginator = new Paginator($this->mockDb, 'users', 10, 2);
    $html = $paginator->render();

    // Verificar se contém elementos essenciais da paginação
    $this->assertStringContainsString('pagination', $html);
    $this->assertStringContainsString('page=1', $html); // Link anterior
    $this->assertStringContainsString('page=3', $html); // Link próximo
    $this->assertStringContainsString('page-current', $html); // Página atual destacada
  }

  public function testRenderFirstPage(): void
  {
    $this->mockDb
      ->method('count')
      ->willReturn(30);

    $this->mockDb
      ->method('getPageData')
      ->willReturn([]);

    $paginator = new Paginator($this->mockDb, 'users', 10, 1);
    $html = $paginator->render();

    // Na primeira página não deve ter link "Anterior"
    $this->assertStringNotContainsString('« Anterior', $html);
    $this->assertStringContainsString('Próximo »', $html);
  }

  public function testRenderLastPage(): void
  {
    $this->mockDb
      ->method('count')
      ->willReturn(25);

    $this->mockDb
      ->method('getPageData')
      ->willReturn([]);

    $paginator = new Paginator($this->mockDb, 'users', 10, 3); // Última página (25/10 = 3)
    $html = $paginator->render();

    // Na última página não deve ter link "Próximo"
    $this->assertStringContainsString('« Anterior', $html);
    $this->assertStringNotContainsString('Próximo »', $html);
  }

  public function testRenderSinglePage(): void
  {
    $this->mockDb
      ->method('count')
      ->willReturn(5); // Menos que perPage

    $this->mockDb
      ->method('getPageData')
      ->willReturn([]);

    $paginator = new Paginator($this->mockDb, 'users', 10, 1);
    $html = $paginator->render();

    // Com uma só página, não deve renderizar nada
    $this->assertEquals('', $html);
  }

  public function testConstructorWithDefaultPage(): void
  {
    $paginator = new Paginator($this->mockDb, 'products', 5);

    // Verificar se a página padrão é 1
    $reflection = new \ReflectionClass($paginator);
    $currentPageProperty = $reflection->getProperty('currentPage');
    $currentPageProperty->setAccessible(true);

    $this->assertEquals(1, $currentPageProperty->getValue($paginator));
  }

  public function testGetResultWithEmptyData(): void
  {
    $this->mockDb
      ->expects($this->once())
      ->method('count')
      ->willReturn(0);

    $this->mockDb
      ->expects($this->once())
      ->method('getPageData')
      ->willReturn([]);

    $result = $this->paginator->getResult();

    $this->assertEquals([], $result->data);
    $this->assertEquals(0, $result->total);
    $this->assertEquals(0, $result->totalPages);
    $this->assertFalse($result->hasNext);
    $this->assertFalse($result->hasPrev);
  }

  public function testGetResultCalculatesCorrectOffset(): void
  {
    // Página 3, 10 por página = offset 20
    $paginator = new Paginator($this->mockDb, 'users', 10, 3);

    $this->mockDb
      ->expects($this->once())
      ->method('count')
      ->willReturn(50);

    $this->mockDb
      ->expects($this->once())
      ->method('getPageData')
      ->with('users', 3, 10) // Verifica se passou página e perPage corretos
      ->willReturn([]);

    $paginator->getResult();
  }
}
