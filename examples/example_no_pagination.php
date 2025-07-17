<?php

require __DIR__ . '/../vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();
$db = $app->getDatabase();

echo "<h2>Lista de Usuários (Sem Paginação)</h2>";

// Buscar todos os usuários
$users = $db->getAll("users");

if (!empty($users)) {
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr style='background-color: #f2f2f2;'>";
  echo "<th style='padding: 10px;'>ID</th>";
  echo "<th style='padding: 10px;'>Nome</th>";
  echo "<th style='padding: 10px;'>Email</th>";
  echo "<th style='padding: 10px;'>Criado em</th>";
  echo "<th style='padding: 10px;'>Ações</th>";
  echo "</tr>";

  foreach ($users as $user) {
    echo "<tr>";
    echo "<td style='padding: 8px; text-align: center;'>" . htmlspecialchars((string)$user->id) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($user->name) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($user->email) . "</td>";
    echo "<td style='padding: 8px;'>" . date('d/m/Y H:i', strtotime($user->created_at)) . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>";
    echo "<a href='?delete=" . $user->id . "' onclick='return confirm(\"Confirma exclusão?\")'>Deletar</a>";
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";

  echo "<br><p><strong>Total de usuários:</strong> " . count($users) . "</p>";
} else {
  echo "<p>Nenhum usuário encontrado.</p>";
}

// Lógica de delete
if (isset($_GET['delete'])) {
  $success = $db->delete("users", (int)$_GET['delete']);
  if ($success) {
    echo "<script>alert('Usuário deletado com sucesso!'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
  } else {
    echo "<script>alert('Erro ao deletar usuário!');</script>";
  }
  exit;
}

echo "<br><a href='../index.php'>← Voltar para paginação</a>";
