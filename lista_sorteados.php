<?php
session_start();
include 'db.php';

// Verificar se o parâmetro 'rifa_id' foi passado
if (!isset($_GET['rifa_id'])) {
    die("ID da rifa não fornecido!");
}

$rifa_id = $_GET['rifa_id'];

// Buscar informações da rifa
$stmt_rifa = $pdo->prepare("SELECT * FROM rifas WHERE id = ?");
$stmt_rifa->execute([$rifa_id]);
$rifa = $stmt_rifa->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    die("Rifa não encontrada!");
}

// Buscar inscrições da rifa
$stmt_inscricoes = $pdo->prepare("SELECT * FROM inscricoes_rifa WHERE rifa_id = ?");
$stmt_inscricoes->execute([$rifa_id]);
$inscricoes = $stmt_inscricoes->fetchAll(PDO::FETCH_ASSOC);

// Mapear números aos usuários
$mapa_numeros = [];
foreach ($inscricoes as $inscricao) {
    $numeros = explode(',', $inscricao['numeros']);
    foreach ($numeros as $numero) {
        $mapa_numeros[intval($numero)] = [
            'nome' => $inscricao['nome'],
            'telefone' => $inscricao['telefone']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Sorteados</title>
    <style>
        table {
            width: 50%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Lista de Sorteados: <?= htmlspecialchars($rifa['nome']) ?></h1>

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Nome</th>
                <th>Telefone</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= $rifa['max_numeros']; $i++): ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= isset($mapa_numeros[$i]) ? htmlspecialchars($mapa_numeros[$i]['nome']) : 'Disponível' ?></td>
                    <td><?= isset($mapa_numeros[$i]) ? htmlspecialchars($mapa_numeros[$i]['telefone']) : '-' ?></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</body>
</html>
