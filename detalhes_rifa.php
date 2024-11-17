<?php
session_start();
include 'db.php';

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Verificar se o parâmetro 'id' foi passado na URL
$rifa_id = $_GET['id'] ?? null;
if (!$rifa_id) {
    echo "ID da rifa não informado!";
    exit();
}

// Buscar informações da rifa
$stmt_rifa = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt_rifa->execute([$rifa_id, $_SESSION['user_id']]);
$rifa = $stmt_rifa->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    echo "Rifa não encontrada ou você não tem permissão para visualizar.";
    exit();
}

// Processar a inscrição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $numeros = array_map('intval', explode(',', $_POST['numeros'])); // Converter números para inteiros

    // Validar se os números já foram escolhidos
    $stmt_verificar = $pdo->prepare("SELECT numeros FROM inscricoes_rifa WHERE rifa_id = ?");
    $stmt_verificar->execute([$rifa_id]);
    $numeros_ocupados = [];
    foreach ($stmt_verificar->fetchAll(PDO::FETCH_ASSOC) as $inscricao) {
        $numeros_ocupados = array_merge($numeros_ocupados, explode(',', $inscricao['numeros']));
    }

    if (array_intersect($numeros, $numeros_ocupados)) {
        $mensagem_erro = "Um ou mais números já foram escolhidos. Tente novamente.";
    } else {
        // Inserir dados no banco de dados
        $stmt_insert = $pdo->prepare("INSERT INTO inscricoes_rifa (rifa_id, nome, telefone, numeros) VALUES (?, ?, ?, ?)");
        try {
            $stmt_insert->execute([$rifa_id, $nome, $telefone, implode(",", $numeros)]);
            $mensagem_sucesso = "Inscrição realizada com sucesso!";
        } catch (PDOException $e) {
            $mensagem_erro = "Erro: " . $e->getMessage();
        }
    }
}

// Buscar os números já escolhidos
$stmt_numeros = $pdo->prepare("SELECT numeros FROM inscricoes_rifa WHERE rifa_id = ?");
$stmt_numeros->execute([$rifa_id]);
$inscricoes = $stmt_numeros->fetchAll(PDO::FETCH_ASSOC);

$numeros_escolhidos = [];
foreach ($inscricoes as $inscricao) {
    $numeros_escolhidos = array_merge($numeros_escolhidos, explode(',', $inscricao['numeros']));
}

$max_numeros = $rifa['max_numeros'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Rifa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .matrix {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 5px;
            margin: 20px auto;
            max-width: 400px;
        }
        .number {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            font-weight: bold;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .number.selected {
            background-color: red;
            color: white;
        }
        .number.available {
            background-color: white;
            color: black;
        }
    </style>
</head>
<body class="container">
    <h1 class="my-4">Detalhes da Rifa: <?= htmlspecialchars($rifa['nome']) ?></h1>

    <p><strong>Descrição:</strong> <?= htmlspecialchars($rifa['descricao']) ?></p>
    <p><strong>Data do Sorteio:</strong> <?= htmlspecialchars($rifa['data_sorteio']) ?></p>
    <p><strong>Máximo de Números:</strong> <?= htmlspecialchars($rifa['max_numeros']) ?></p>

    <!-- Mensagens -->
    <?php if (isset($mensagem_sucesso)): ?>
        <div class="alert alert-success"><?= $mensagem_sucesso ?></div>
    <?php elseif (isset($mensagem_erro)): ?>
        <div class="alert alert-danger"><?= $mensagem_erro ?></div>
    <?php endif; ?>

    <!-- Formulário de inscrição -->
    <form action="detalhes_rifa.php?id=<?= $rifa_id ?>" method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone:</label>
            <input type="text" name="telefone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="numeros" class="form-label">Escolha seus números (separados por vírgula):</label>
            <input type="text" name="numeros" id="numeros" class="form-control" placeholder="1, 2, 3, ..." required>
        </div>
        <button type="submit" class="btn btn-primary">Inscrever</button>
    </form>

    <!-- Botões -->
    <form action="gerenciar_inscritos.php" method="GET" class="mb-2">
        <input type="hidden" name="rifa_id" value="<?= $rifa_id ?>">
        <button type="submit" class="btn btn-secondary">Ver Inscritos</button>
    </form>
    <form action="lista_sorteados.php" method="GET">
        <input type="hidden" name="rifa_id" value="<?= $rifa_id ?>">
        <button type="submit" class="btn btn-secondary">Lista de Sorteio</button>
    </form>

    <!-- Matriz de números -->
    <h2 class="mt-4">Disponibilidade de Números:</h2>
    <div class="matrix">
        <?php for ($i = 1; $i <= $max_numeros; $i++): ?>
            <?php $selected_class = in_array($i, $numeros_escolhidos) ? 'selected' : 'available'; ?>
            <div class="number <?= $selected_class ?>"><?= $i ?></div>
        <?php endfor; ?>
    </div>
</body>
</html>
