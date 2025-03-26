<?php
include 'config/db.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM produtos WHERE id = $id");
$produto = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $produto['nome']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><?php echo $produto['nome']; ?></h1>
        <img src="public/images/<?php echo $produto['imagem']; ?>" class="img-fluid">
        <p><?php echo $produto['descricao']; ?></p>
        <h4 class="text-success">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h4>
        <a href="carrinho.php?add=<?php echo $produto['id']; ?>" class="btn btn-success">Adicionar ao Carrinho</a>
    </div>
</body>
</html>
