<?php 
include_once '../config/db.php'; 
include_once '../includes/header.php'; 
?>
<div class="container mt-4">
    <h2>Cadastro de Cliente</h2>
    <form action="salvar_cliente.php" method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone">
        </div>
        <button type="submit" class="btn btn-success">Cadastrar Cliente</button>
    </form>
</div>
<?php include_once '../includes/footer.php'; ?>