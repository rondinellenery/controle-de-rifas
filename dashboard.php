<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Recuperar as rifas do usuário logado
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se foi solicitado excluir uma rifa
if (isset($_GET['delete_rifa_id'])) {
    $delete_rifa_id = $_GET['delete_rifa_id'];

    // Verificar se a rifa existe
    $stmt_rifa = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
    $stmt_rifa->execute([$delete_rifa_id, $user_id]);
    $rifa = $stmt_rifa->fetch(PDO::FETCH_ASSOC);

    if ($rifa) {
        // Excluir a rifa sem verificar o status
        $stmt_delete = $pdo->prepare("DELETE FROM rifas WHERE id = ?");
        try {
            $stmt_delete->execute([$delete_rifa_id]);
            $mensagem_sucesso = "Rifa excluída com sucesso!";
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao excluir a rifa: " . $e->getMessage();
        }
    } else {
        $mensagem_erro = "Rifa não encontrada.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
            width: 100%;
            max-width: 600px;
        }

        h1 {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 1rem;
            display: inline-block;
            margin: 10px 0;
        }

        a:hover {
            text-decoration: underline;
        }

        ul {
            list-style: none;
            margin-top: 20px;
            padding: 0;
        }

        li {
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            padding: 5px 10px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        input[type="submit"]:hover {
            background-color: #c0392b;
        }

        span {
            font-size: 0.9rem;
            color: #666;
            margin-left: 5px;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 1.8rem;
            }

            input[type="submit"], a {
                font-size: 0.8rem;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo ao Painel de Controle</h1>

        <p><a href="nova_rifa.php">Cadastrar Nova Rifa</a></p>

        <h2>Suas Rifas:</h2>

        <!-- Exibir mensagem de erro ou sucesso -->
        <?php if (isset($mensagem_erro)): ?>
            <p class="error"><?= $mensagem_erro ?></p>
        <?php elseif (isset($mensagem_sucesso)): ?>
            <p class="success"><?= $mensagem_sucesso ?></p>
        <?php endif; ?>

        <ul>
            <?php if (empty($rifas)): ?>
                <li>Você ainda não criou nenhuma rifa.</li>
            <?php else: ?>
                <?php foreach ($rifas as $rifa): ?>
                    <li>
                        <a href="detalhes_rifa.php?id=<?= $rifa['id'] ?>"><?= htmlspecialchars($rifa['nome']) ?></a>
                        
                        <!-- Botão de exclusão de rifa -->
                        <form action="dashboard.php" method="GET" style="display:inline;">
                            <input type="hidden" name="delete_rifa_id" value="<?= $rifa['id'] ?>">
                            <input type="submit" value="Excluir Rifa" onclick="return confirm('Tem certeza que deseja excluir esta rifa?')">
                        </form>

                        <!-- Mensagem de status da rifa -->
                        <?php if ($rifa['status'] === 'concluida'): ?>
                            <span>(Rifa concluída)</span>
                        <?php else: ?>
                            <span>(Rifa não concluída)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
