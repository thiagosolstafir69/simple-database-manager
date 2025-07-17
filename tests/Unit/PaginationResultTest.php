<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ThiagoWip\SimpleDatabaseManager\PaginationResult;

class PaginationResultTest extends TestCase
{
  public function testPaginationResultProperties(): void
  {
    $data = [
      (object) ['id' => 1, 'name' => 'João'],
      (object) ['id' => 2, 'name' => 'Maria']
    ];
    $links = ['prev' => '?page=1', 'next' => '?page=3'];

    $result = new PaginationResult($data, $links, 25, 3, 2);

    // Testar sintaxe de objeto (nunca arrays associativos)
    $this->assertEquals($data, $result->data);
    $this->assertEquals($links, $result->links);
    $this->assertEquals(25, $result->total);
    $this->assertEquals(2, $result->currentPage);
    $this->assertEquals(3, $result->totalPages);
    $this->assertTrue($result->hasNext); // página 2 de 3 tem próxima
    $this->assertTrue($result->hasPrev); // página 2 de 3 tem anterior
  }

  public function testFirstPagePagination(): void
  {
    $data = [(object) ['id' => 1, 'name' => 'João']];
    $links = ['next' => '?page=2'];
    $result = new PaginationResult($data, $links, 15, 2, 1);

    $this->assertEquals(1, $result->currentPage);
    $this->assertEquals(2, $result->totalPages);
    $this->assertTrue($result->hasNext);
    $this->assertFalse($result->hasPrev);
  }

  public function testLastPagePagination(): void
  {
    $data = [(object) ['id' => 21, 'name' => 'Pedro']];
    $links = ['prev' => '?page=2'];
    $result = new PaginationResult($data, $links, 21, 3, 3);

    $this->assertEquals(3, $result->currentPage);
    $this->assertEquals(3, $result->totalPages);
    $this->assertFalse($result->hasNext);
    $this->assertTrue($result->hasPrev);
  }

  public function testSinglePagePagination(): void
  {
    $data = [(object) ['id' => 1, 'name' => 'João']];
    $links = [];
    $result = new PaginationResult($data, $links, 5, 1, 1);

    $this->assertEquals(1, $result->currentPage);
    $this->assertEquals(1, $result->totalPages);
    $this->assertFalse($result->hasNext);
    $this->assertFalse($result->hasPrev);
  }

  public function testEmptyResultPagination(): void
  {
    $data = [];
    $links = [];
    $result = new PaginationResult($data, $links, 0, 0, 1);

    $this->assertEquals([], $result->data);
    $this->assertEquals(0, $result->total);
    $this->assertEquals(1, $result->currentPage);
    $this->assertEquals(0, $result->totalPages);
    $this->assertFalse($result->hasNext);
    $this->assertFalse($result->hasPrev);
  }

  public function testObjectPropertyAccess(): void
  {
    $data = [(object) ['id' => 1, 'name' => 'Test']];
    $links = ['prev' => '?page=2', 'next' => '?page=4'];
    $result = new PaginationResult($data, $links, 100, 5, 3);

    // Verificar que todas as propriedades são acessíveis como objeto
    $properties = ['data', 'links', 'total', 'currentPage', 'totalPages', 'hasNext', 'hasPrev'];

    foreach ($properties as $property) {
      $this->assertTrue(property_exists($result, $property), "Propriedade $property deve existir");
    }

    // Verificar valores específicos
    $this->assertEquals(100, $result->total);
    $this->assertEquals(3, $result->currentPage);
    $this->assertEquals(5, $result->totalPages);
  }

  public function testEdgeCaseCalculations(): void
  {
    // Teste com última página
    $data = [];
    $links = ['prev' => '?page=3'];
    $result = new PaginationResult($data, $links, 33, 4, 4);

    $this->assertEquals(4, $result->totalPages);
    $this->assertEquals(4, $result->currentPage);
    $this->assertFalse($result->hasNext); // última página
    $this->assertTrue($result->hasPrev);
  }

  public function testLinksArray(): void
  {
    $data = [(object) ['id' => 1, 'name' => 'Test']];
    $links = [
      'first' => '?page=1',
      'prev' => '?page=1',
      'next' => '?page=3',
      'last' => '?page=5'
    ];
    $result = new PaginationResult($data, $links, 50, 5, 2);

    $this->assertEquals($links, $result->links);
    $this->assertArrayHasKey('first', $result->links);
    $this->assertArrayHasKey('prev', $result->links);
    $this->assertArrayHasKey('next', $result->links);
    $this->assertArrayHasKey('last', $result->links);
  }
}
