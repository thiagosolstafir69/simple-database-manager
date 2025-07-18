<?php
// Sistema de Gerenciamento de Usuários - Página Principal
// Esta página serve como ponto de entrada para os exemplos do sistema

// Processar mensagens (se houver)
$message = $_GET['msg'] ?? null;
$messageType = $_GET['type'] ?? 'info';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciador de Usuários</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .header h1 {
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 15px;
      letter-spacing: -0.02em;
    }

    .header p {
      color: #6b7280;
      font-size: 1.2rem;
      font-weight: 400;
    }

    .message {
      padding: 20px;
      border-radius: 15px;
      margin-bottom: 30px;
      border: none;
      font-weight: 500;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .message.success {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
    }

    .message.error {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
    }

    .welcome-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .welcome-header {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      padding: 30px;
      text-align: center;
    }

    .welcome-header h2 {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .welcome-content {
      padding: 40px;
      text-align: center;
    }

    .welcome-content h3 {
      color: #374151;
      font-size: 1.5rem;
      margin-bottom: 15px;
      font-weight: 600;
    }

    .welcome-content p {
      color: #6b7280;
      font-size: 1.1rem;
      line-height: 1.6;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }

    .feature-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .feature-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      border-color: rgba(102, 126, 234, 0.3);
    }

    .feature-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 20px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
    }

    .feature-card h3 {
      color: #374151;
      font-size: 1.3rem;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .feature-card p {
      color: #6b7280;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .feature-badge {
      display: inline-block;
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      margin-top: 10px;
    }

    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      
      .header {
        padding: 30px 20px;
      }
      
      .header h1 {
        font-size: 2.2rem;
      }
      
      .features-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .feature-card {
        padding: 25px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>Gerenciador de Usuários</h1>
      <p>Sistema moderno de gerenciamento com múltiplas funcionalidades</p>
    </div>

    <?php if ($message): ?>
      <div class="message <?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="welcome-card">
      <div class="welcome-header">
        <h2>🚀 Bem-vindo ao Sistema</h2>
      </div>
      
      <div class="welcome-content">
        <h3>Explore as Funcionalidades</h3>
        <p>Escolha uma das opções abaixo para descobrir todas as capacidades do nosso sistema de gerenciamento de usuários.</p>
      </div>
    </div>

    <div class="features-grid">
      <a href="examples/formulario_ajax.php" class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Formulário AJAX</h3>
        <p>Interface moderna com validação em tempo real e processamento assíncrono</p>
        <span class="feature-badge">Novo</span>
      </a>
      
      <a href="examples/crud_basico.php" class="feature-card">
        <div class="feature-icon">📝</div>
        <h3>CRUD Básico</h3>
        <p>Operações fundamentais de criar, ler, atualizar e deletar registros</p>
      </a>
      
      <a href="examples/listagem_completa.php" class="feature-card">
        <div class="feature-icon">📋</div>
        <h3>Listagem Completa</h3>
        <p>Visualização organizada de todos os usuários com paginação inteligente</p>
      </a>
      
      <a href="examples/busca_com_filtros.php" class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>Busca com Filtros</h3>
        <p>Sistema avançado de pesquisa com múltiplos critérios de filtragem</p>
      </a>
      
      <a href="examples/busca_melhorada.php" class="feature-card">
        <div class="feature-icon">🎯</div>
        <h3>Busca Melhorada</h3>
        <p>Pesquisa otimizada com recursos avançados e resultados precisos</p>
      </a>
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
  </script>
</body>

</html>