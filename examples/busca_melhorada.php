<?php

require __DIR__ . '/../vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();
$db = $app->getDatabase();

echo "<h2>Busca Melhorada - Extensão da Classe</h2>";

// Vamos estender a funcionalidade do DbConnection
class ExtendedDbConnection extends ThiagoWip\SimpleDatabaseManager\DbConnection
{
  public function __construct(string $servername, string $username, string $password, string $dbname)
  {
    parent::__construct($servername, $username, $password, $dbname);
  }

  /**
   * Busca usando getAll() com filtros aplicados no PHP
   */
  public function searchWithGetAll(string $table, string $searchTerm): array
  {
    // Usar o método getAll() da classe pai
    $allRecords = $this->getAll($table);

    if (empty($searchTerm)) {
      return $allRecords;
    }

    // Filtrar no PHP
    $filtered = array_filter($allRecords, function ($record) use ($searchTerm) {
      foreach ($record as $field => $value) {
        if (is_string($value) && stripos($value, $searchTerm) !== false) {
          return true;
        }
      }
      return false;
    });

    // Ordenar por relevância (quantas vezes o termo aparece)
    usort($filtered, function ($a, $b) use ($searchTerm) {
      $scoreA = $this->calculateRelevanceScore($a, $searchTerm);
      $scoreB = $this->calculateRelevanceScore($b, $searchTerm);
      return $scoreB - $scoreA;
    });

    return array_values($filtered);
  }

  /**
   * Calcula score de relevância baseado no termo de busca
   */
  private function calculateRelevanceScore($record, string $searchTerm): int
  {
    $score = 0;
    foreach ($record as $field => $value) {
      if (is_string($value)) {
        $score += substr_count(strtolower($value), strtolower($searchTerm));
        // Dar mais peso se o termo aparece no início
        if (stripos($value, $searchTerm) === 0) {
          $score += 3;
        }
      }
    }
    return $score;
  }

  /**
   * Busca com filtros múltiplos usando getAll()
   */
  public function searchMultipleFields(string $table, array $filters): array
  {
    $allRecords = $this->getAll($table);

    return array_filter($allRecords, function ($record) use ($filters) {
      foreach ($filters as $field => $value) {
        if (!isset($record->$field)) continue;

        if (stripos($record->$field, $value) === false) {
          return false;
        }
      }
      return true;
    });
  }
}

// Usar nossa classe estendida
$extendedDb = new ExtendedDbConnection(
  $_ENV['DB_HOST'],
  $_ENV['DB_USER'],
  $_ENV['DB_PASS'],
  $_ENV['DB_NAME']
);

echo "<h3>🔍 Busca Simples com getAll()</h3>";
$searchTerm = $_GET['search'] ?? 'João';
$results = $extendedDb->searchWithGetAll('users', $searchTerm);

echo "<p>Buscando por: <strong>" . htmlspecialchars($searchTerm) . "</strong></p>";
echo "<p>Encontrados: <strong>" . count($results) . "</strong> resultados</p>";

if (!empty($results)) {
  echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
  echo "<tr style='background-color: #f2f2f2;'>";
  echo "<th>ID</th><th>Nome</th><th>Email</th><th>Score</th>";
  echo "</tr>";

  foreach ($results as $index => $user) {
    $score = ($index + 1); // Posição na relevância
    echo "<tr>";
    echo "<td>" . htmlspecialchars((string)$user->id) . "</td>";
    echo "<td>" . htmlspecialchars($user->name) . "</td>";
    echo "<td>" . htmlspecialchars($user->email) . "</td>";
    echo "<td>#{$score}</td>";
    echo "</tr>";
  }
  echo "</table>";
}

echo "<h3>🎯 Busca com Múltiplos Filtros</h3>";
$multiResults = $extendedDb->searchMultipleFields('users', [
  'name' => 'João',
  'email' => '@'
]);

echo "<p>Usuários com nome contendo 'João' E email contendo '@': <strong>" . count($multiResults) . "</strong></p>";

echo "<h3>📊 Comparação de Performance</h3>";
$start = microtime(true);
$getAllResults = $extendedDb->getAll('users');
$getAllTime = microtime(true) - $start;

$start = microtime(true);
$searchResults = $extendedDb->searchWithGetAll('users', 'test');
$searchTime = microtime(true) - $start;

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Método</th><th>Registros</th><th>Tempo</th></tr>";
echo "<tr><td>getAll()</td><td>" . count($getAllResults) . "</td><td>" . number_format($getAllTime * 1000, 2) . "ms</td></tr>";
echo "<tr><td>searchWithGetAll()</td><td>" . count($searchResults) . "</td><td>" . number_format($searchTime * 1000, 2) . "ms</td></tr>";
echo "</table>";

echo "<br>";
echo "<form method='GET' style='margin: 20px 0;'>";
echo "<input type='text' name='search' placeholder='Digite sua busca' value='" . htmlspecialchars($_GET['search'] ?? '') . "' style='padding: 8px; width: 300px;'>";
echo "<button type='submit' style='padding: 8px 15px; margin-left: 10px;'>Buscar</button>";
echo "</form>";

echo "<br><a href='../index.php'>← Voltar para paginação</a> | ";
echo "<a href='busca_com_filtros.php'>🔍 Busca Original</a>";
