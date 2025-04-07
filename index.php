<?php
include 'includes/header.php';

// Buscar produtos com uma imagem associada - CORREÇÃO DA CONSULTA SQL
$query = "
    SELECT p.*, 
       (SELECT caminho 
        FROM imagens 
        WHERE produto_id = p.id 
        LIMIT 1) AS imagem
    FROM produtosx p;
";
$result = $conn->query($query);

// Verificar erro na consulta
if (!$result) {
    echo '<div class="alert alert-danger">Erro na consulta: ' . $conn->error . '</div>';
}
?>

<h2 class="text-center mb-4">Produtos em Destaque</h2>
<div class="row">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($produto = $result->fetch_assoc()) : ?>
            <?php
                // Buscar todas as imagens do produto atual
                $id_produto = $produto['id'];
                $imgQuery = "SELECT caminho FROM imagens WHERE produto_id = $id_produto";
                $imgResult = $conn->query($imgQuery);
                $imagens = [];
                if ($imgResult && $imgResult->num_rows > 0) {
                    while ($img = $imgResult->fetch_assoc()) {
                        $imagens[] = $img['caminho'];
                    }
                } else {
                    // Caso não encontre imagens na tabela imagens, use a imagem do produto
                    if (!empty($produto['imagem'])) {
                        $imagens[] = $produto['imagem'];
                    }
                }
            ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <!-- Carrossel de Imagens -->
                    <div id="carouselProduto<?= $produto['id']; ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (count($imagens) > 0): ?>
                                <?php foreach ($imagens as $index => $imagem): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                        <div class="d-flex justify-content-center align-items-center overflow-hidden" style="height: 200px;">
                                            <img src="<?= htmlspecialchars($imagem); ?>" 
                                                 class="img-fluid" 
                                                 style="max-height: 100%; object-fit: contain;" 
                                                 alt="<?= htmlspecialchars($produto['nome']); ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <div class="d-flex justify-content-center align-items-center overflow-hidden" style="height: 200px;">
                                        <div class="text-muted">Sem imagem disponível</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (count($imagens) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduto<?= $produto['id']; ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselProduto<?= $produto['id']; ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Detalhes do Produto -->
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($produto['nome']); ?></h5>
                        <p class="card-text"><?= htmlspecialchars($produto['descricao']); ?></p>
                        <p class="fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.'); ?></p>
                        <form action="/pages/add_to_cart.php" method="POST">
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