<?php
require 'config.php';

$sql = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(100),
    telefone VARCHAR(20),
    data DATE,
    hora TIME,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
$pdo->exec($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $email, $senha]);

    echo "<p style='color:green'>Administrador criado com sucesso!</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Instalar Sistema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="install">
    <h2>Instalar Sistema da Barbearia</h2>
    <form method="post">
        <input type="text" name="nome" placeholder="Nome do admin" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Criar admin</button>
    </form>
</body>
</html>
