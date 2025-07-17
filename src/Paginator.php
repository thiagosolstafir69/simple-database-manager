<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager;

class Paginator
{
  private PaginationResult $result;

  /**
   * O Paginator agora controla a lógica de paginação.
   * Ele recebe a conexão, a tabela e o limite, e busca os dados necessários.
   */
  public function __construct(
    private DbConnection $db,
    private string $table,
    private int $limit = 10
  ) {
    $page = (int)($_GET['page'] ?? 1);
    if ($page < 1) {
      $page = 1;
    }

    // A própria classe agora chama o método do banco para buscar os dados.
    $this->result = $this->db->getPagination($this->table, $page, $this->limit);
  }

  /**
   * Retorna o objeto com os dados e informações da paginação.
   */
  public function getResult(): PaginationResult
  {
    return $this->result;
  }

  /**
   * Renderiza os links HTML para a navegação.
   */
  public function render(): string
  {
    if (empty($this->result->links)) {
      return '';
    }

    $html = '';
    foreach ($this->result->links as $link) {
      $isActive = ($link == $this->result->currentPage);
      $style = $isActive
        ? "padding: 8px 12px; margin: 2px; background-color: #007cba; color: white; text-decoration: none; border-radius: 4px;"
        : "padding: 8px 12px; margin: 2px; background-color: #f1f1f1; color: #333; text-decoration: none; border-radius: 4px;";

      $url = htmlspecialchars($_SERVER['PHP_SELF']) . "?page=$link";
      $html .= "<a href='$url' style='$style'>$link</a> ";
    }
    return $html;
  }
}
