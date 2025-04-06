<?php 
include_once '../config/db.php'; 
include_once '../includes/header.php'; 
?>
<div class="container mt-4">
    <h2>Cadastro de Produto</h2>
    <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Produto</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="preco" class="form-label">Preço</label>
            <input type="number" step="0.01" class="form-control" id="preco" name="preco" required>
        </div>
        <div class="mb-3">
            <label for="estoque" class="form-label">Estoque</label>
            <input type="number" class="form-control" id="estoque" name="estoque" required>
        </div>
        <div class="mb-3">
            <label for="imagens[]" class="form-label">Imagens do Produto</label>
            <input type="file" class="form-control" name="imagens[]" multiple required>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
    </form>
</div>
<?php include_once '../includes/footer.php'; ?>