<?php

namespace ThiagoWip\SimpleDatabaseManager;

class PaginationResult
{
  public array $data;
  public array $links;
  public int $total;
  public int $totalPages;
  public int $currentPage;
  public bool $hasNext;
  public bool $hasPrev;

  public function __construct(array $data, array $links, int $total, int $totalPages, int $currentPage)
  {
    $this->data = $data;
    $this->links = $links;
    $this->total = $total;
    $this->totalPages = $totalPages;
    $this->currentPage = $currentPage;
    $this->hasNext = $currentPage < $totalPages;
    $this->hasPrev = $currentPage > 1;
  }
}
