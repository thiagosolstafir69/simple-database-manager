<?php

require __DIR__ . '/../vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;

$app = App::boot();
$db = $app->getDatabase();

echo "<h2>Exemplo CRUD Básico</h2>";

echo "<h3>1. Inserir Novo Usuário</h3>";
$timestamp = time();
$newUser = [
  'name' => 'João Silva',
  'email' => "joao.silva.{$timestamp}@email.com",
  'password' => password_hash('123456', PASSWORD_DEFAULT)
];

$success = $db->insert('users', $newUser);
if ($success) {
  echo "✅ Usuário inserido com sucesso!<br>";
} else {
  echo "❌ Erro ao inserir usuário.<br>";
}

echo "<h3>2. Buscar Usuário por ID</h3>";
// Buscar o último usuário inserido
$lastUsers = $db->getConnection()->query("SELECT * FROM users ORDER BY id DESC LIMIT 1")->fetchAll();
$lastId = !empty($lastUsers) ? $lastUsers[0]->id : 1;
$user = $db->getSingle('users', $lastId);
if ($user) {
  echo "👤 Usuário encontrado: <strong>{$user->name}</strong> ({$user->email})<br>";
} else {
  echo "❌ Usuário não encontrado.<br>";
}

echo "<h3>3. Atualizar Usuário</h3>";
$updateData = ['name' => 'João Santos'];
$updated = $db->update('users', $updateData, $lastId);
if ($updated) {
  echo "✅ Usuário atualizado com sucesso!<br>";
} else {
  echo "❌ Erro ao atualizar usuário.<br>";
}

echo "<h3>4. Buscar com Condições WHERE</h3>";
$sql = "SELECT * FROM users WHERE name LIKE :search";
$stmt = $db->getConnection()->prepare($sql);
$stmt->execute(['search' => '%João%']);
$users = $stmt->fetchAll();
echo "🔍 Encontrados <strong>" . count($users) . "</strong> usuários com nome contendo 'João'<br>";

echo "<h3>5. Contar Total de Usuários</h3>";
$total = $db->count('users');
echo "📊 Total de usuários no sistema: <strong>{$total}</strong><br>";

echo "<h3>6. Buscar Todos os Usuários</h3>";
$allUsers = $db->getAll('users');
if (!empty($allUsers)) {
  echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
  echo "<tr style='background-color: #f2f2f2;'>";
  echo "<th style='padding: 8px;'>ID</th>";
  echo "<th style='padding: 8px;'>Nome</th>";
  echo "<th style='padding: 8px;'>Email</th>";
  echo "</tr>";

  foreach ($allUsers as $user) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>{$user->id}</td>";
    echo "<td style='padding: 8px;'>{$user->name}</td>";
    echo "<td style='padding: 8px;'>{$user->email}</td>";
    echo "</tr>";
  }
  echo "</table>";
}

echo "<br><a href='../index.php'>← Voltar para paginação</a> | ";
echo "<a href='listagem_completa.php'>📋 Listagem Completa</a> | ";
echo "<a href='busca_com_filtros.php'>🔍 Busca com Filtros</a>";
