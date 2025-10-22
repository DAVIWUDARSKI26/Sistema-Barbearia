<?php
require 'config.php';

// Buscar dias ocupados
$ano = date('Y');
$mes = date('m');

$stmt = $pdo->prepare("SELECT DISTINCT data_agendamento FROM agendamentos WHERE MONTH(data_agendamento)=? AND YEAR(data_agendamento)=?");
$stmt->execute([$mes, $ano]);
$datasOcupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Barbearia NW — Corte com estilo</title>
    <link rel="stylesheet" href="style.css">
    <style> html { scroll-behavior: smooth; } </style>
</head>
<body>

<!-- 🔝 NAVBAR -->
<header class="navbar">
    <div class="logo">💈 Barbearia <span>NW</span></div>
    <nav>
        <ul>
            <li><a href="#servicos">Serviços</a></li>
            <li><a href="#agenda">Agenda</a></li>
            <li><a href="#depoimentos">Depoimentos</a></li>
            <li><a href="#contato">Contato</a></li>
        </ul>
    </nav>
    <a href="login.php" class="btn-azul">Painel Barbeiro</a>
</header>

<!-- 🧔 HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="card">
            <small>Barbearia Premium • Centro</small>
            <h1>Seu corte no horário certo — estilo sem fila.</h1>
            <p>Chega de esperar. Escolha o serviço, veja os horários livres no calendário e garanta seu atendimento. Simples, rápido e sem surpresas.</p>
            <ul>
                <li>🕒 Seg a Sáb — 09:00 às 18:00</li>
                <li>🔒 Reserva online segura</li>
                <li>💬 Confirmação por WhatsApp</li>
            </ul>
            <a href="#agenda" class="btn-azul">Quero reservar agora</a>
        </div>

        <div class="beneficios">
            <div class="box"><strong>Experiência</strong><p>Profissionais de alto nível e produtos premium.</p></div>
            <div class="box"><strong>Conveniência</strong><p>Agendamento online em poucos cliques.</p></div>
            <div class="box"><strong>Compromisso</strong><p>Pontualidade e qualidade em cada atendimento.</p></div>
        </div>
    </div>
</section>

<!-- 💇 SERVIÇOS -->
<section id="servicos" class="servicos">
    <h2>Serviços</h2>
    <p>Do clássico ao moderno: escolha seu estilo e deixe o resto com a gente.</p>

    <div class="cards-servicos">
        <div class="card-servico">
            <h3>Corte masculino</h3>
            <p>Acabamento impecável e consultoria de estilo.</p>
        </div>
        <div class="card-servico">
            <h3>Barba</h3>
            <p>Lâmina quente, toalha aromática e óleo premium.</p>
        </div>
        <div class="card-servico">
            <h3>Corte + Barba</h3>
            <p>Combo completo para sair pronto.</p>
        </div>
    </div>
</section>

<!-- 📅 AGENDA -->
<section id="agenda" class="agenda">
    <h2>Agenda</h2>
    <p>Veja abaixo os dias com vagas.<br>
    <strong>Vermelho:</strong> Lotado • <strong>Azul:</strong> Disponível • <strong>Cinza:</strong> Fechado</p>

    <div id="calendar"></div>
</section>

<!-- 🕓 FORMULÁRIO -->
<section class="form-reserva">
    <h2>Reserve seu horário</h2>
    <p>Preencha seus dados e escolha um horário disponível. Confirmaremos por WhatsApp.</p>

    <form action="agendar.php" method="POST" class="form-box">
        <div class="campo">
            <label>Seu nome</label>
            <input type="text" name="nome" required placeholder="Seu nome completo">
        </div>
        <div class="campo">
            <label>WhatsApp</label>
            <input type="text" name="whatsapp" required placeholder="(11) 90000-0000">
        </div>
        <div class="campo">
            <label>Serviço</label>
            <select name="servico" required>
                <option value="">Selecione...</option>
                <option value="Corte masculino">Corte masculino</option>
                <option value="Barba">Barba</option>
                <option value="Corte + Barba">Corte + Barba</option>
            </select>
        </div>
        <div class="campo">
            <label>Data</label>
            <input type="date" name="data" required>
        </div>
        <div class="campo">
            <label>Horário</label>
            <select name="hora" required>
                <option value="">Selecione um dia no calendário</option>
                <?php
                for ($h = 9; $h <= 18; $h++) {
                    echo "<option value='{$h}:00'>{$h}:00</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn-azul">Reservar agora</button>
    </form>
</section>

<!-- 📞 CONTATO -->
<section id="contato" class="contato">
    <h2>Fale conosco</h2>
    <p>📍 Rua Exemplo, 123 – Centro<br>
    📱 (11) 90000-0000<br>
    ⏰ Seg a Sáb — 09:00 às 18:00</p>
</section>

<footer>
    <p>© 2025 Barbearia NW. Todos os direitos reservados.</p>
</footer>

<script>
const datasOcupadas = <?= json_encode($datasOcupadas) ?>;
const calendar = document.getElementById("calendar");
const hoje = new Date();
const ano = hoje.getFullYear();
const mes = hoje.getMonth();
const diasNoMes = new Date(ano, mes + 1, 0).getDate();

let html = "<div class='calendar-grid'>";
const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
diasSemana.forEach(d => html += `<div class='cabeca'>${d}</div>`);

for (let dia = 1; dia <= diasNoMes; dia++) {
    const data = new Date(ano, mes, dia);
    const dataISO = data.toISOString().split('T')[0];
    const ocupado = datasOcupadas.includes(dataISO);
    const domingo = data.getDay() === 0;
    let classe = domingo ? "fechado" : (ocupado ? "lotado" : "disponivel");
    html += `<div class='dia ${classe}'><span>${dia}</span></div>`;
}
html += "</div>";
calendar.innerHTML = html;
</script>

</body>
</html>
