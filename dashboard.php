<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Filtro de data
$dataSelecionada = $_GET['data'] ?? date('Y-m-d');

// Contadores
$contPendentes = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento='$dataSelecionada'")->fetchColumn();
$contConfirmados = 0; // futuramente pode vir da coluna "status"
$contConcluidos = 0;
$contCancelados = 0;

// Agendamentos do dia
$stmt = $pdo->prepare("SELECT * FROM agendamentos ORDER BY data_agendamento DESC, hora_agendamento ASC");
$stmt->execute();
$agendamentos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Barbearia N/title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <header class="topo">
        <h1>✂️ Painel</h1>
        <a href="logout.php" class="btn-sair">Sair</a>
    </header>

    <main class="painel">
        <section class="resumo">
            <h2>Resumo de hoje</h2>
            <div class="cards">
                <div class="card pendente">Pendentes <span><?= $contPendentes ?></span></div>
                <div class="card confirmado">Confirmados <span><?= $contConfirmados ?></span></div>
                <div class="card concluido">Concluídos <span><?= $contConcluidos ?></span></div>
                <div class="card cancelado">Cancelados <span><?= $contCancelados ?></span></div>
            </div>

            <form method="get" class="filtro-data">
                <label for="data">Filtrar por data</label>
                <input type="date" id="data" name="data" value="<?= $dataSelecionada ?>">
                <button type="submit">Aplicar</button>
            </form>
        </section>

        <section class="agendamentos">
            <h2>Agendamentos — <?= $dataSelecionada ?></h2>
            <table>
                <tr>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Status</th>
                    <th>Contato</th>
                    <th>Ações</th>
                </tr>

                <?php if ($agendamentos): ?>
                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($a['hora_agendamento'])) ?></td>
                            <td><?= htmlspecialchars($a['nome_cliente']) ?></td>
                            <td><?= htmlspecialchars($a['servico']) ?></td>
                            <td><span class="status pendente">Pendente</span></td>
                            <td><?= htmlspecialchars($a['telefone']) ?></td>
<td class="acoes">
    <?php
        $mensagem = urlencode("Olá {$a['nome_cliente']}! Seu agendamento para {$a['data_agendamento']} às {$a['hora_agendamento']} foi confirmado! ✂️");
        $linkWhats = "https://wa.me/55" . preg_replace('/\D/', '', $a['telefone']) . "?text=" . $mensagem;
    ?>
    <a href="<?= $linkWhats ?>" target="_blank" class="btn-confirmar">Confirmar</a>
    <a href="recusar.php?id=<?= $a['id'] ?>" class="btn-recusar">Recusar</a>
</td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Sem agendamentos para esta data.</td></tr>
                <?php endif; ?>
            </table>
        </section>
    </main>
</body>
</html>
