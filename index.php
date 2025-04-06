<?php
include 'includes/header.php';

// Buscar produtos do banco de dados
$query = "SELECT * FROM produtosx";
$result = $conn->query($query);
?>

<h2 class="text-center mb-4">Produtos em Destaque</h2>
<div class="row">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($produto = $result->fetch_assoc()) : ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <img src="<?= htmlspecialchars($produto['imagem']); ?>" class="card-img-top" alt="<?= htmlspecialchars($produto['nome']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($produto['nome']); ?></h5>
                        <p class="card-text"><?= htmlspecialchars($produto['descricao']); ?></p>
                        <p class="fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.'); ?></p>
                        <form action="/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $produto['id']; ?>">
                            <button type="submit" class="btn btn-primary w-100">Comprar</button>
                        </form>
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

<?php include 'includes/footer.php'; ?>