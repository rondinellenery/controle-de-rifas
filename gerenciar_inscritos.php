<?php
session_start();
include 'db.php';

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Verificar se o parâmetro 'rifa_id' foi passado
$rifa_id = $_GET['rifa_id'] ?? null;
if (!$rifa_id) {
    echo "Rifa não especificada.";
    exit();
}

// Função para buscar inscritos
function getInscritos($pdo, $rifa_id) {
    $stmt = $pdo->prepare("SELECT * FROM inscricoes_rifa WHERE rifa_id = ?");
    $stmt->execute([$rifa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buscar inscritos
$inscritos = getInscritos($pdo, $rifa_id);

// Editar inscrição
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt_edit = $pdo->prepare("SELECT * FROM inscricoes_rifa WHERE id = ?");
    $stmt_edit->execute([$edit_id]);
    $inscrito = $stmt_edit->fetch(PDO::FETCH_ASSOC);

    if (!$inscrito) {
        echo "Inscrito não encontrado.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novo_nome = $_POST['nome'];
        $novo_telefone = $_POST['telefone'];
        $novos_numeros = array_map('intval', explode(',', $_POST['numeros']));

        // Verificar se os números já estão ocupados
        $stmt_verificar = $pdo->prepare("SELECT numeros FROM inscricoes_rifa WHERE rifa_id = ? AND id != ?");
        $stmt_verificar->execute([$rifa_id, $edit_id]);
        $numeros_ocupados = [];
        foreach ($stmt_verificar->fetchAll(PDO::FETCH_ASSOC) as $inscricao) {
            $numeros_ocupados = array_merge($numeros_ocupados, explode(',', $inscricao['numeros']));
        }

        if (array_intersect($novos_numeros, $numeros_ocupados)) {
            $mensagem_erro = "Um ou mais números já foram escolhidos. Tente novamente.";
        } else {
            $stmt_update = $pdo->prepare("UPDATE inscricoes_rifa SET nome = ?, telefone = ?, numeros = ? WHERE id = ?");
            try {
                $stmt_update->execute([$novo_nome, $novo_telefone, implode(',', $novos_numeros), $edit_id]);
                header("Location: gerenciar_inscritos.php?rifa_id=$rifa_id");
                exit();
            } catch (PDOException $e) {
                $mensagem_erro = "Erro ao atualizar: " . $e->getMessage();
            }
        }
    }
}

// Excluir inscrição
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt_delete = $pdo->prepare("DELETE FROM inscricoes_rifa WHERE id = ?");
    try {
        $stmt_delete->execute([$delete_id]);
        header("Location: gerenciar_inscritos.php?rifa_id=$rifa_id");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao excluir: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Inscritos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1>Gerenciar Inscritos na Rifa</h1>
    <h2>Inscritos na Rifa: <?= htmlspecialchars($rifa_id) ?></h2>

    <!-- Mensagem de erro -->
    <?php if (isset($mensagem_erro)): ?>
        <div class="alert alert-danger"><?= $mensagem_erro ?></div>
    <?php endif; ?>

    <!-- Listar inscritos -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Números Escolhidos</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscritos as $inscrito): ?>
            <tr>
                <td><?= $inscrito['id'] ?></td>
                <td><?= htmlspecialchars($inscrito['nome']) ?></td>
                <td><?= htmlspecialchars($inscrito['telefone']) ?></td>
                <td><?= htmlspecialchars($inscrito['numeros']) ?></td>
                <td>
                    <a href="gerenciar_inscritos.php?rifa_id=<?= $rifa_id ?>&edit_id=<?= $inscrito['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="gerenciar_inscritos.php?rifa_id=<?= $rifa_id ?>&delete_id=<?= $inscrito['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Editar formulário -->
    <?php if (isset($inscrito)): ?>
        <h3>Editar Inscrição</h3>
        <form action="gerenciar_inscritos.php?rifa_id=<?= $rifa_id ?>&edit_id=<?= $inscrito['id'] ?>" method="POST" class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($inscrito['nome']) ?>" required class="form-control mb-2">
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($inscrito['telefone']) ?>" required class="form-control mb-2">
            <label for="numeros">Números Escolhidos:</label>
            <input type="text" name="numeros" value="<?= htmlspecialchars($inscrito['numeros']) ?>" required class="form-control mb-2">
            <button type="submit" class="btn btn-success">Atualizar Inscrição</button>
        </form>
    <?php endif; ?>
</body>
</html>
