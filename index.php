<?php
include 'includes/header.php';
include 'config/db.php'; // Conexão com o banco de dados

// Buscar produtos do banco de dados
$query = "SELECT * FROM produtos";
$result = $conn->query($query);
?>

<h2>Produtos em Destaque</h2>
<div class="product-grid">
    <?php while ($produto = $result->fetch_assoc()) : ?>
        <div class="product-card">
            <img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="<?= htmlspecialchars($produto['nome']); ?>">
            <h3><?= htmlspecialchars($produto['nome']); ?></h3>
            <p><?= htmlspecialchars($produto['descricao']); ?></p>
            <p><strong>R$ <?= number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $produto['id']; ?>">
                <button type="submit">Comprar</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
