<?php

require __DIR__ . '/../vendor/autoload.php';

use ThiagoWip\SimpleDatabaseManager\App;
use ThiagoWip\SimpleDatabaseManager\Paginator;

try {
  $app = App::boot();
} catch (Exception $e) {
  die("Erro ao inicializar aplicação: " . $e->getMessage());
}

// Configuração da paginação
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$paginator = new Paginator($app->getDatabase(), 'users', $page, $limit);

$message = '';
$messageType = '';

// Processar criação de usuário via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validações
  if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
  }

  if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Senha deve ter pelo menos 6 caracteres']);
    exit;
  }

  // Verificar se email já existe
  try {
    $existingUser = $app->getDatabase()->getConnection()->prepare("SELECT id FROM users WHERE email = :email");
    $existingUser->execute(['email' => $email]);
    if ($existingUser->fetch()) {
      echo json_encode(['success' => false, 'message' => 'Email já está em uso']);
      exit;
    }

    // Criar usuário
    $userData = [
      'name' => htmlspecialchars($name),
      'email' => $email,
      'password' => password_hash($password, PASSWORD_DEFAULT),
      'created_at' => date('Y-m-d H:i:s')
    ];

    $success = $app->getDatabase()->insert('users', $userData);
    if ($success) {
      // Buscar o usuário criado
      $newUserId = $app->getDatabase()->getConnection()->lastInsertId();
      $newUser = $app->getDatabase()->getSingle('users', $newUserId);
      
      echo json_encode([
        'success' => true, 
        'message' => 'Usuário criado com sucesso!',
        'user' => $newUser
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
    }
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
  }
  exit;
}

// Processar atualização de usuário via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'update_user') {
  $id = (int) ($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');

  // Validações
  if ($id <= 0 || empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
  }

  // Verificar se email já existe para outro usuário
  try {
    $existingUser = $app->getDatabase()->getConnection()->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $existingUser->execute(['email' => $email, 'id' => $id]);
    if ($existingUser->fetch()) {
      echo json_encode(['success' => false, 'message' => 'Email já está em uso por outro usuário']);
      exit;
    }

    // Atualizar usuário
    $userData = [
      'name' => htmlspecialchars($name),
      'email' => $email,
      'updated_at' => date('Y-m-d H:i:s')
    ];

    $success = $app->getDatabase()->update('users', $userData, $id);
    if ($success) {
      $updatedUser = $app->getDatabase()->getSingle('users', $id);
      echo json_encode([
        'success' => true, 
        'message' => 'Usuário atualizado com sucesso!',
        'user' => $updatedUser
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
    }
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
  }
  exit;
}

// Processar exclusão com validação e segurança
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  if ($id > 0) {
    // Verificar se o usuário existe antes de deletar
    $user = $app->getDatabase()->getSingle('users', $id);
    if ($user) {
      $success = $app->getDatabase()->delete("users", $id);
      if ($success) {
        $message = "Usuário '{$user->name}' deletado com sucesso!";
        $messageType = 'success';
      } else {
        $message = "Erro ao deletar o usuário.";
        $messageType = 'error';
      }
    } else {
      $message = "Usuário não encontrado.";
      $messageType = 'error';
    }
    // Redirect para evitar resubmissão
    header("Location: " . $_SERVER['PHP_SELF'] . ($message ? '?msg=' . urlencode($message) . '&type=' . $messageType : ''));
    exit;
  }
}

// Exibir mensagem se houver
if (isset($_GET['msg'])) {
  $message = htmlspecialchars($_GET['msg']);
  $messageType = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
}

try {
  $data = $paginator->getResult();
} catch (\Exception $e) {
  $message = "Erro ao carregar dados: " . htmlspecialchars($e->getMessage());
  $messageType = 'error';
  $data = (object) ['data' => []];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulário AJAX - Gerenciador de Usuários</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .header h1 {
      color: #2c3e50;
      margin-bottom: 10px;
    }

    .message {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .users-table {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .table-header {
      background: #3498db;
      color: white;
      padding: 15px;
      font-weight: bold;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-content .btn {
      font-size: 0.9em;
      padding: 8px 15px;
    }

    .user-row {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .user-row:last-child {
      border-bottom: none;
    }

    .user-row:hover {
      background: #f8f9fa;
    }

    .user-info {
      flex: 1;
    }

    .user-info strong {
      color: #2c3e50;
    }

    .user-meta {
      color: #7f8c8d;
      font-size: 0.9em;
      margin-top: 5px;
    }

    .actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      font-size: 0.9em;
      transition: all 0.3s;
    }

    .btn-danger {
      background: #e74c3c;
      color: white;
    }

    .btn-danger:hover {
      background: #c0392b;
    }

    .btn-primary {
      background: #3498db;
      color: white;
    }

    .btn-primary:hover {
      background: #2980b9;
    }

    .btn-success {
      background: #27ae60;
      color: white;
    }

    .btn-success:hover {
      background: #229954;
    }

    .btn-secondary {
      background: #95a5a6;
      color: white;
    }

    .btn-secondary:hover {
      background: #7f8c8d;
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 20px 0;
    }

    .pagination a {
      padding: 8px 12px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 5px;
      text-decoration: none;
      color: #333;
      transition: all 0.3s;
    }

    .pagination a:hover {
      background: #3498db;
      color: white;
      border-color: #3498db;
    }

    .pagination .current {
      background: #3498db;
      color: white;
      border-color: #3498db;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #7f8c8d;
    }

    .navigation {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
      text-align: center;
    }

    .navigation a {
      display: inline-block;
      margin: 5px 10px;
      padding: 10px 20px;
      background: #3498db;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s;
    }

    .navigation a:hover {
      background: #2980b9;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 5% auto;
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      position: relative;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
    }

    .close:hover {
      color: #000;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #2c3e50;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    .form-group input:focus {
      outline: none;
      border-color: #3498db;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }

    .alert {
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .loading {
      opacity: 0.7;
      pointer-events: none;
    }

    .loading button {
      position: relative;
    }

    .loading button:after {
      content: '';
      position: absolute;
      width: 16px;
      height: 16px;
      margin: auto;
      border: 2px solid transparent;
      border-top-color: #ffffff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>📋 Formulário AJAX - Gerenciador de Usuários</h1>
      <p>Exemplo de formulário com funcionalidade AJAX para criação e edição de usuários.</p>
    </div>

    <?php if ($message): ?>
      <div class="message <?= $messageType ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="users-table">
      <div class="table-header">
        <div class="header-content">
          <span>Lista de Usuários (Total: <?= isset($data->total) ? $data->total : 0 ?>)</span>
          <button class="btn btn-success" onclick="openCreateModal()">+ Novo Usuário</button>
        </div>
      </div>

      <?php if (!empty($data->data)): ?>
        <?php foreach ($data->data as $item): ?>
          <div class="user-row">
            <div class="user-info">
              <strong>#<?= htmlspecialchars($item->id) ?> - <?= htmlspecialchars($item->name) ?></strong><br>
              <span>Email: <?= htmlspecialchars($item->email) ?></span>
              <div class="user-meta">
                Criado: <?= date('d/m/Y H:i', strtotime($item->created_at)) ?>
                <?php if ($item->updated_at): ?>
                  | Atualizado: <?= date('d/m/Y H:i', strtotime($item->updated_at)) ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="actions">
              <button class="btn btn-primary" onclick="editUser(<?= $item->id ?>, '<?= htmlspecialchars($item->name, ENT_QUOTES) ?>', '<?= htmlspecialchars($item->email, ENT_QUOTES) ?>')">
                Editar
              </button>
              <a href="?delete=<?= $item->id ?>"
                class="btn btn-danger"
                onclick="return confirm('Tem certeza que deseja deletar o usuário <?= htmlspecialchars($item->name) ?>?')">
                Deletar
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <h3>Nenhum usuário encontrado</h3>
          <p>Não há usuários cadastrados no sistema.</p>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($data->data)): ?>
      <div class="pagination">
        <?= $paginator->render() ?>
      </div>
    <?php endif; ?>

    <div class="navigation">
      <strong>Outras Funcionalidades:</strong><br><br>
      <a href="../index.php">← Voltar ao Sistema Principal</a>
      <a href="crud_basico.php">CRUD Básico</a>
      <a href="listagem_completa.php">Listagem Completa</a>
      <a href="busca_com_filtros.php">Busca com Filtros</a>
      <a href="busca_melhorada.php">Busca Melhorada</a>
    </div>
  </div>

  <!-- Modal de Criação -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeCreateModal()">&times;</span>
      <h2>Novo Usuário</h2>
      <div id="createAlert"></div>
      <form id="createForm">
        <div class="form-group">
          <label for="createName">Nome:</label>
          <input type="text" id="createName" required>
        </div>
        <div class="form-group">
          <label for="createEmail">Email:</label>
          <input type="email" id="createEmail" required>
        </div>
        <div class="form-group">
          <label for="createPassword">Senha:</label>
          <input type="password" id="createPassword" required minlength="6">
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-success">Criar Usuário</button>
          <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Editar Usuário</h2>
      <div id="editAlert"></div>
      <form id="editForm">
        <input type="hidden" id="editUserId">
        <div class="form-group">
          <label for="editName">Nome:</label>
          <input type="text" id="editName" required>
        </div>
        <div class="form-group">
          <label for="editEmail">Email:</label>
          <input type="email" id="editEmail" required>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Limpar parâmetros da URL após exibir mensagem
    if (window.location.search.includes('msg=')) {
      const url = new URL(window.location);
      url.searchParams.delete('msg');
      url.searchParams.delete('type');
      window.history.replaceState({}, document.title, url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : ''));
    }

    // Funções do Modal de Criação
    function openCreateModal() {
      document.getElementById('createForm').reset();
      document.getElementById('createAlert').innerHTML = '';
      document.getElementById('createModal').style.display = 'block';
    }

    function closeCreateModal() {
      document.getElementById('createModal').style.display = 'none';
    }

    // Funções do Modal de Edição
    function editUser(id, name, email) {
      document.getElementById('editUserId').value = id;
      document.getElementById('editName').value = name;
      document.getElementById('editEmail').value = email;
      document.getElementById('editAlert').innerHTML = '';
      document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
      const editModal = document.getElementById('editModal');
      const createModal = document.getElementById('createModal');
      if (event.target === editModal) {
        closeEditModal();
      } else if (event.target === createModal) {
        closeCreateModal();
      }
    }

    // Submissão do formulário de criação via AJAX
    document.getElementById('createForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData();
      formData.append('action', 'create_user');
      formData.append('name', document.getElementById('createName').value);
      formData.append('email', document.getElementById('createEmail').value);
      formData.append('password', document.getElementById('createPassword').value);

      // Mostrar loading
      const form = document.getElementById('createForm');
      form.classList.add('loading');

      fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          form.classList.remove('loading');

          if (data.success) {
            // Mostrar mensagem de sucesso
            document.getElementById('createAlert').innerHTML =
              '<div class="alert alert-success">' + data.message + '</div>';

            // Fechar modal após 1 segundo e recarregar página
            setTimeout(() => {
              closeCreateModal();
              showGlobalMessage(data.message, 'success');
              // Recarregar página para mostrar novo usuário
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            }, 1000);
          } else {
            // Mostrar erro
            document.getElementById('createAlert').innerHTML =
              '<div class="alert alert-error">' + data.message + '</div>';
          }
        })
        .catch(error => {
          form.classList.remove('loading');
          document.getElementById('createAlert').innerHTML =
            '<div class="alert alert-error">Erro de conexão. Tente novamente.</div>';
        });
    });

    // Submissão do formulário de edição via AJAX
    document.getElementById('editForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData();
      formData.append('action', 'update_user');
      formData.append('id', document.getElementById('editUserId').value);
      formData.append('name', document.getElementById('editName').value);
      formData.append('email', document.getElementById('editEmail').value);

      // Mostrar loading
      const form = document.getElementById('editForm');
      form.classList.add('loading');

      fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          form.classList.remove('loading');

          if (data.success) {
            // Mostrar mensagem de sucesso
            document.getElementById('editAlert').innerHTML =
              '<div class="alert alert-success">' + data.message + '</div>';

            // Atualizar a linha na tabela
            updateUserRow(data.user);

            // Fechar modal após 1 segundo
            setTimeout(() => {
              closeEditModal();
              // Mostrar mensagem global
              showGlobalMessage(data.message, 'success');
            }, 1000);
          } else {
            // Mostrar erro
            document.getElementById('editAlert').innerHTML =
              '<div class="alert alert-error">' + data.message + '</div>';
          }
        })
        .catch(error => {
          form.classList.remove('loading');
          document.getElementById('editAlert').innerHTML =
            '<div class="alert alert-error">Erro de conexão. Tente novamente.</div>';
        });
    });

    // Atualizar linha do usuário na tabela
    function updateUserRow(user) {
      const userRows = document.querySelectorAll('.user-row');
      userRows.forEach(row => {
        const editBtn = row.querySelector('button[onclick*="editUser(' + user.id + '"]');
        if (editBtn) {
          const userInfo = row.querySelector('.user-info');
          userInfo.innerHTML = `
                        <strong>#${user.id} - ${user.name}</strong><br>
                        <span>Email: ${user.email}</span>
                        <div class="user-meta">
                            Criado: ${userInfo.querySelector('.user-meta').textContent.split('|')[0].trim()}
                            ${userInfo.querySelector('.user-meta').textContent.includes('|') ? '| Atualizado: ' + new Date().toLocaleString('pt-BR') : ''}
                        </div>
                    `;
          // Atualizar onclick do botão editar
          editBtn.setAttribute('onclick', `editUser(${user.id}, '${user.name}', '${user.email}')`);
        }
      });
    }

    // Mostrar mensagem global
    function showGlobalMessage(message, type) {
      const existingMessage = document.querySelector('.message');
      if (existingMessage) {
        existingMessage.remove();
      }

      const messageDiv = document.createElement('div');
      messageDiv.className = 'message ' + type;
      messageDiv.textContent = message;

      const container = document.querySelector('.container');
      const header = container.querySelector('.header');
      header.insertAdjacentElement('afterend', messageDiv);

      // Remover mensagem após 3 segundos
      setTimeout(() => {
        messageDiv.remove();
      }, 3000);
    }
  </script>
</body>

</html>