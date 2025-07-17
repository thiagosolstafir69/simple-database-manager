<?php

declare(strict_types=1);

namespace ThiagoWip\SimpleDatabaseManager;

use Dotenv\Dotenv;

class App
{
  private static ?self $instance = null;
  private DbConnection $db;

  private function __construct()
  {
    try {
      $this->loadEnvironment();
      $this->initializeDatabase();
    } catch (\Exception $e) {
      error_log("App initialization failed: " . $e->getMessage());
      throw $e;
    }
  }

  public static function getInstance(): self
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function loadEnvironment(): void
  {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
  }

  private function initializeDatabase(): void
  {
    $this->db = new DbConnection(
      $_ENV['DB_HOST'],
      $_ENV['DB_USER'],
      $_ENV['DB_PASS'],
      $_ENV['DB_NAME']
    );
  }

  public function getDatabase(): DbConnection
  {
    return $this->db;
  }

  public function createPaginator(string $table, int $limit = 10): Paginator
  {
    return new Paginator($this->db, $table, $limit);
  }

  // Método estático para facilitar o uso
  public static function boot(): self
  {
    return self::getInstance();
  }
}
