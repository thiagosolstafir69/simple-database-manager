<?php

namespace ThiagoWip\SimpleDatabaseManager;

use PDO;
use PDOException;
use Exception;
use InvalidArgumentException;

class DbConnection
{
  private ?PDO $conn = null;

  public function __construct(string $servername, string $username, string $password, string $dbname)
  {
    $this->conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
  }

  private function validateIdentifier(string $identifier): void
  {
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
      throw new InvalidArgumentException("Invalid table or field name: $identifier");
    }
  }

  public function getConnection(): PDO
  {
    if ($this->conn === null) {
      throw new Exception("Conexão não foi estabelecida");
    }
    return $this->conn;
  }

  public function closeConnection(): void
  {
    if ($this->conn !== null) {
      $this->conn = null;
    }
  }

  public function insert(string $table, array $data): bool
  {
    $this->validateIdentifier($table);
    foreach (array_keys($data) as $field) {
      $this->validateIdentifier($field);
    }

    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO $table (" . implode(',', array_keys($data)) . ") VALUES ($placeholders)";
    $stmt = $this->getConnection()->prepare($sql);
    return $stmt->execute($data);
  }

  public function update(string $table, array $data, int $id): bool
  {
    $this->validateIdentifier($table);
    foreach (array_keys($data) as $field) {
      $this->validateIdentifier($field);
    }

    $sql = "UPDATE $table SET " . implode(',', array_map(function ($key) {
      return "$key = :$key";
    }, array_keys($data))) . " WHERE id = :id";
    $stmt = $this->getConnection()->prepare($sql);
    return $stmt->execute([...$data, "id" => $id]);
  }

  public function delete(string $table, int $id): bool
  {
    $this->validateIdentifier($table);

    $sql = "DELETE FROM $table WHERE id = :id";
    $stmt = $this->getConnection()->prepare($sql);
    return $stmt->execute(["id" => $id]);
  }

  public function getSingle(string $table, int $id): ?object
  {
    $this->validateIdentifier($table);

    $sql = "SELECT * FROM $table WHERE id = :id";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute(["id" => $id]);
    return $stmt->fetch(PDO::FETCH_OBJ);
  }

  public function getAll(string $table): array
  {
    $this->validateIdentifier($table);

    $sql = "SELECT * FROM $table";
    return $this->getConnection()->query($sql)->fetchAll();
  }

  public function paginate(string $table, int $page = 1, int $limit = 10): array
  {
    $this->validateIdentifier($table);

    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 10;

    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM $table LIMIT :limit OFFSET :offset";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function count(string $table): int
  {
    $this->validateIdentifier($table);

    $sql = "SELECT COUNT(*) FROM $table";
    return $this->getConnection()->query($sql)->fetchColumn();
  }

  public function getLinksPagination(string $table, int $page, int $limit): array
  {
    $total = $this->count($table);
    $totalPages = ceil($total / $limit);
    $links = [];
    for ($i = 1; $i <= $totalPages; $i++) {
      $links[] = $i;
    }
    return $links;
  }

  public function getPagination(string $table, int $page, int $limit): PaginationResult
  {
    $total = $this->count($table);
    $totalPages = ceil($total / $limit);

    $data = $this->paginate($table, $page, $limit);
    $links = $this->getLinksPagination($table, $page, $limit);

    return new PaginationResult($data, $links, $total, $totalPages, $page);
  }
}
