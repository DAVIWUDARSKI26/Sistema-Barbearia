<?php
// Mostrar erros durante desenvolvimento (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexão (altere se suas credenciais forem diferentes)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barbearia";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Pega colunas da tabela
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM agendamentos");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
} else {
    die("Erro ao obter colunas: " . $conn->error);
}

// ===== Mapear colunas possíveis para os nomes usados no formulário =====
// Form envia: nome, whatsapp, servico, data, hora
// Possíveis colunas no BD: nome_cliente / nome, telefone / whatsapp, servico, data_agendamento / data, hora_agendamento / hora

// Nome do cliente
if (in_array('nome_cliente', $cols)) {
    $colNome = 'nome_cliente';
} elseif (in_array('nome', $cols)) {
    $colNome = 'nome';
} else {
    $colNome = null;
}

// Telefone/whatsapp
if (in_array('telefone', $cols)) {
    $colTelefone = 'telefone';
} elseif (in_array('whatsapp', $cols)) {
    $colTelefone = 'whatsapp';
} else {
    $colTelefone = null;
}

// Serviço
if (in_array('servico', $cols)) {
    $colServico = 'servico';
} elseif (in_array('serviço', $cols)) { // só por precaução
    $colServico = 'serviço';
} else {
    $colServico = null;
}

// Data
if (in_array('data_agendamento', $cols)) {
    $colData = 'data_agendamento';
} elseif (in_array('data', $cols)) {
    $colData = 'data';
} else {
    $colData = null;
}

// Hora
if (in_array('hora_agendamento', $cols)) {
    $colHora = 'hora_agendamento';
} elseif (in_array('hora', $cols)) {
    $colHora = 'hora';
} else {
    $colHora = null;
}

// Verifica se todos os mapeamentos necessários foram encontrados
if (!$colNome || !$colTelefone || !$colServico || !$colData || !$colHora) {
    // Mensagem clara exibindo colunas detectadas
    die("Colunas obrigatórias não encontradas na tabela 'agendamentos'. Colunas detectadas: " . implode(', ', $cols));
}

// Inicializa flags
$sucesso = false;
$erro = "";

// Processa POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Use os nomes que o formulário envia:
    $nome = trim($_POST["nome"] ?? '');
    $whatsapp = trim($_POST["whatsapp"] ?? '');
    $servico = trim($_POST["servico"] ?? '');
    $data = trim($_POST["data"] ?? '');
    $hora = trim($_POST["hora"] ?? '');

    // Validações básicas
    if (empty($nome) || empty($whatsapp) || empty($servico) || empty($data) || empty($hora)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // 1) Verificar duplicado (usando as colunas reais)
        $sqlCheck = "SELECT COUNT(*) as cnt FROM agendamentos WHERE {$colData} = ? AND {$colHora} = ?";
        $check = $conn->prepare($sqlCheck);
        if (!$check) {
            $erro = "Erro no prepare (check): " . $conn->error;
        } else {
            $check->bind_param("ss", $data, $hora);
            $check->execute();
            $r = $check->get_result();
            $row = $r->fetch_assoc();
            $exists = (int)$row['cnt'] > 0;
            $check->close();

            if ($exists) {
                $erro = "❌ Este horário já foi reservado. Escolha outro.";
            } else {
                // 2) Inserir usando as colunas reais (ordem tem que bater com os parâmetros)
                // Monta SQL dinamicamente com nomes de coluna já validados
                $sqlIns = "INSERT INTO agendamentos ({$colNome}, {$colTelefone}, {$colServico}, {$colData}, {$colHora}) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sqlIns);
                if (!$stmt) {
                    $erro = "Erro no prepare (insert): " . $conn->error;
                } else {
                    // bind: todos strings - ajusta se sua coluna hora for TIME (string funciona)
                    $stmt->bind_param("sssss", $nome, $whatsapp, $servico, $data, $hora);
                    if ($stmt->execute()) {
                        $sucesso = true;
                    } else {
                        $erro = "Erro ao salvar agendamento: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Fecha conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento - Barbearia</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Mantive o estilo local - não altera visual do index.php */
        body {
            background-color: #0a0a0a;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            background: linear-gradient(180deg,#0f172a,#0b0c10);
            padding: 34px;
            max-width: 700px;
            width: 100%;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
            border: 1px solid rgba(30,144,255,0.06);
            text-align: center;
        }
        h1 { color: #1e90ff; margin-bottom: 6px; }
        p { color: #cfcfcf; margin: 6px 0; }
        .btn {
            display: inline-block;
            margin-top: 18px;
            background: #1e90ff;
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }
        .erro { color: #ff6666; font-weight: 700; }
        .success { color: #7fffd4; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($erro): ?>
            <h1 class="erro">Ops</h1>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
            <a class="btn" href="index.php">← Voltar</a>
        <?php elseif ($sucesso): ?>
            <h1 class="success">✅ Agendamento confirmado!</h1>
            <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
            <p><strong>Serviço:</strong> <?= htmlspecialchars($servico) ?></p>
            <p><strong>Data:</strong> <?= htmlspecialchars($data) ?> às <?= htmlspecialchars($hora) ?></p>
            <p>Enviaremos uma confirmação via WhatsApp em alguns minutos.</p>
            <a class="btn" href="index.php">← Voltar à página</a>
        <?php else: ?>
            <h1>Nenhum dado recebido.</h1>
            <p>Envie o formulário de agendamento da página inicial.</p>
            <a class="btn" href="index.php">Voltar</a>
        <?php endif; ?>
    </div>
</body>
</html>
