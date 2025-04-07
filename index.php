<?php
include 'includes/header.php';

// Buscar produtos com imagem associada
$query = "SELECT p.*, i.caminho AS imagem 
          FROM produtosx p
          LEFT JOIN imagens i ON p.id = i.produto_id
          GROUP BY p.id";
$result = $conn->query($query);
?>

<div class="container mt-4">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4">E-commerce de Instrumentos Musicais</h1>
        <p class="lead">Bem-vindo à nossa loja online de instrumentos musicais.</p>
        <hr class="my-4">
        <p>Acesse as opções abaixo para gerenciar clientes e produtos.</p>
        <div class="mt-4">
            <a href="pages/gerenciar_cliente.php" class="btn btn-primary me-2">Gerenciar Clientes</a>
            <a href="pages/gerenciar_produto.php" class="btn btn-success">Gerenciar Produtos</a>
        </div>
    </div>

    <h2 class="mt-5 mb-4">Produtos em Destaque</h2>
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($produto = $result->fetch_assoc()) : ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem']); ?>" class="card-img-top" alt="<?= htmlspecialchars($produto['nome']); ?>">
                        <?php else: ?>
                            <img src="/uploads/default-product.jpg" class="card-img-top" alt="Imagem padrão">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($produto['descricao']); ?></p>
                            <p class="fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Nenhum produto encontrado.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
