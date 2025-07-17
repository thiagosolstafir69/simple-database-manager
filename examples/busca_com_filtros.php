<?php

require __DIR__ . '/../vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();
$db = $app->getDatabase();

echo "<h2>Lista de Usuários com Filtros</h2>";

// Formulário de busca
echo "<form method='GET' style='margin-bottom: 20px;'>";
echo "<input type='text' name='search' placeholder='Buscar por nome ou email' value='" . ($_GET['search'] ?? '') . "' style='padding: 8px; width: 300px;'>";
echo "<button type='submit' style='padding: 8px 15px; margin-left: 10px;'>Buscar</button>";
echo "<a href='" . $_SERVER['PHP_SELF'] . "' style='padding: 8px 15px; margin-left: 10px; text-decoration: none; background: #ccc; color: black;'>Limpar</a>";
echo "</form>";

// Lógica de busca
$users = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search = $_GET['search'];

  // Usando prepared statements para busca segura
  $sql = "SELECT * FROM users WHERE name LIKE :search OR email LIKE :search ORDER BY created_at DESC";
  $stmt = $db->getConnection()->prepare($sql);
  $stmt->execute(['search' => '%' . $search . '%']);
  $users = $stmt->fetchAll();

  echo "<p><em>Resultados para: \"" . htmlspecialchars($search) . "\"</em></p>";
} else {
  // Buscar todos ordenados por data de criação
  $sql = "SELECT * FROM users ORDER BY created_at DESC";
  $users = $db->getConnection()->query($sql)->fetchAll();
}

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
    echo "<a href='?delete=" . $user->id . "&search=" . urlencode($_GET['search'] ?? '') . "' onclick='return confirm(\"Confirma exclusão?\")'>Deletar</a>";
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";

  echo "<br><p><strong>Total encontrado:</strong> " . count($users) . "</p>";
} else {
  echo "<p>Nenhum usuário encontrado.</p>";
}

// Lógica de delete
if (isset($_GET['delete'])) {
  $success = $db->delete("users", (int)$_GET['delete']);
  if ($success) {
    $redirectUrl = $_SERVER['PHP_SELF'];
    if (!empty($_GET['search'])) {
      $redirectUrl .= '?search=' . urlencode($_GET['search']);
    }
    echo "<script>alert('Usuário deletado com sucesso!'); window.location.href = '" . $redirectUrl . "';</script>";
  } else {
    echo "<script>alert('Erro ao deletar usuário!');</script>";
  }
  exit;
}

echo "<br><a href='../index.php'>← Voltar para paginação</a>";
