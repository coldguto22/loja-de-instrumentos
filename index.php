<?php
include 'includes/header.php';
include 'includes/db.php'; // ou o nome do seu arquivo de conexão

// Buscar produtos
$query = "SELECT * FROM produtosx";
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
                        <?php
                        // Buscar imagens do produto
                        $produto_id = $produto['id'];
                        $sql_img = "SELECT caminho FROM imagens WHERE produto_id = $produto_id";
                        $result_img = $conn->query($sql_img);
                        ?>

                        <?php if ($result_img && $result_img->num_rows > 0): ?>
                            <div id="carousel<?= $produto_id ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php $isFirst = true; ?>
                                    <?php while ($img = $result_img->fetch_assoc()): ?>
                                        <div class="carousel-item <?= $isFirst ? 'active' : '' ?>">
                                            <img src="<?= htmlspecialchars($img['caminho']) ?>" class="d-block w-100" alt="Imagem do produto">
                                        </div>
                                        <?php $isFirst = false; ?>
                                    <?php endwhile; ?>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $produto_id ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $produto_id ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Próximo</span>
                                </button>
                            </div>
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
