<?php

require __DIR__ . '/vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();

$paginator = $app->createPaginator("users", 10);
$data = $paginator->getResult();

foreach ($data->data as $item) {
  echo $item->id . " - " . $item->name . " - " . $item->email . " - " . $item->password . " - " . $item->created_at . " - " . $item->updated_at . "<br>";
  echo "<a href='?delete=" . $item->id . "'>Deletar</a>";
  echo "<hr>";
}

if (isset($_GET['delete'])) {
  $app->getDatabase()->delete("users", (int)$_GET['delete']);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

echo $paginator->render();

echo "<br><br>";
echo "<div style='margin: 20px 0; padding: 10px; background: #f5f5f5; border-radius: 5px;'>";
echo "<strong>Outras opções:</strong><br>";
echo "<a href='examples/basic_crud.php'>🔧 Exemplos CRUD</a> | ";
echo "<a href='examples/example_no_pagination.php'>📋 Ver todos sem paginação</a> | ";
echo "<a href='examples/example_with_filters.php'>🔍 Ver com filtros de busca</a>";
echo "</div>";
