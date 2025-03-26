<?php
session_start();
include 'config/db.php';

// Adiciona ao carrinho
if (isset($_GET['add'])) {
    $id = $_GET['add'];
    $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
}

// Exibe produtos no carrinho
$carrinho = $_SESSION['carrinho'] ?? [];
$produtos = [];

foreach ($carrinho as $id => $quantidade) {
    $result = $conn->query("SELECT * FROM produtos WHERE id = $id");
    $produto = $result->fetch_assoc();
    $produto['quantidade'] = $quantidade;
    $produtos[] = $produto;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Meu Carrinho</h1>
        <ul class="list-group">
            <?php foreach ($produtos as $produto): ?>
                <li class="list-group-item">
                    <?php echo $produto['nome']; ?> - 
                    Quantidade: <?php echo $produto['quantidade']; ?> -
                    R$ <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="checkout.php" class="btn btn-primary mt-3">Finalizar Compra</a>
    </div>
</body>
</html>
