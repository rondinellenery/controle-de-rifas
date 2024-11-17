<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $password]);
        echo "<script>
                alert('Usuário registrado com sucesso!');
                window.location.href = 'login.html?username=$username';
              </script>";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>
