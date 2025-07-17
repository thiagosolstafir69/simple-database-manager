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
