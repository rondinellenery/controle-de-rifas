<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

$mensagem = "";

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $data_sorteio = $_POST['data_sorteio'];
    $max_numeros = $_POST['max_numeros'];
    $usuario_id = $_SESSION['user_id'];

    // Verificar limite de rifas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rifas WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $count = $stmt->fetchColumn();

    if ($count >= 2) {
        $mensagem = "Você já atingiu o limite de 2 rifas!";
    } else {
        // Inserir nova rifa
        $stmt = $pdo->prepare("INSERT INTO rifas (usuario_id, nome, descricao, data_sorteio, max_numeros) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$usuario_id, $nome, $descricao, $data_sorteio, $max_numeros]);
            $mensagem = "Rifa criada com sucesso!";
        } catch (PDOException $e) {
            $mensagem = "Erro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Nova Rifa</title>
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
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        input:focus, textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        button {
            width: 100%;
            padding: 10px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
            transform: scale(1.05);
        }

        .mensagem {
            margin-top: 15px;
            font-weight: bold;
            font-size: 1rem;
        }

        .mensagem.success {
            color: #28a745;
        }

        .mensagem.error {
            color: #e74c3c;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 1.8rem;
            }

            input, textarea, button {
                font-size: 0.9rem;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastrar Nova Rifa</h1>

        <form action="nova_rifa.php" method="POST">
            <label for="nome">Nome da Rifa:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="descricao">Descrição:</label>
            <textarea name="descricao" id="descricao" required></textarea>

            <label for="data_sorteio">Data do Sorteio:</label>
            <input type="date" name="data_sorteio" id="data_sorteio" required>

            <label for="max_numeros">Máximo de Números:</label>
            <input type="number" name="max_numeros" id="max_numeros" required>

            <button type="submit">Cadastrar Rifa</button>
        </form>

        <?php if (!empty($mensagem)) : ?>
            <p class="mensagem <?= strpos($mensagem, 'Erro') !== false ? 'error' : 'success' ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
