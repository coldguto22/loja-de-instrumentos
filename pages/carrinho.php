<?php session_start(); include 'header.php'; ?>
<div class="container mt-4">
    <h2>Carrinho de Compras</h2>
    <?php if (!empty($_SESSION['carrinho'])): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; foreach ($_SESSION['carrinho'] as $item): ?>
                    <tr>
                        <td><?= $item['nome'] ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                    <?php $total += $item['preco'] * $item['quantidade']; endforeach; ?>
            </tbody>
        </table>
        <div class="text-end">
            <h4>Total: R$ <?= number_format($total, 2, ',', '.') ?></h4>
            <a href="finalizar_pedido.php" class="btn btn-primary">Finalizar Pedido</a>
        </div>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
