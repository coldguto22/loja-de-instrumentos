<?php
include_once '/config/db.php';
include_once '/includes/header.php';

// Inicializar variáveis para o formulário
$id = "";
$nome = "";
$email = "";
$telefone = "";
$titulo_form = "Cadastrar Novo Cliente";
$acao = "cadastrar";

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $sql_excluir = "DELETE FROM clientes WHERE id = $id_excluir";
    
    if ($conn->query($sql_excluir) === TRUE) {
        $mensagem = "Cliente excluído com sucesso!";
        $tipo = "success";
    } else {
        $mensagem = "Erro ao excluir cliente: " . $conn->error;
        $tipo = "danger";
    }
}

// Verificar se é uma edição
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $sql_editar = "SELECT * FROM clientes WHERE id = $id_editar";
    $result_editar = $conn->query($sql_editar);
    
    if ($result_editar && $result_editar->num_rows > 0) {
        $cliente = $result_editar->fetch_assoc();
        $id = $cliente['id'];
        $nome = $cliente['nome'];
        $email = $cliente['email'];
        $telefone = $cliente['telefone'];
        $titulo_form = "Editar Cliente";
        $acao = "atualizar";
    }
}

// Processar o formulário de cadastro/atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    
    if ($_POST['acao'] == "cadastrar") {
        // Inserir novo cliente
        $sql = "INSERT INTO clientes (nome, email, telefone) VALUES ('$nome', '$email', '$telefone')";
        
        if ($conn->query($sql) === TRUE) {
            $mensagem = "Cliente cadastrado com sucesso!";
            $tipo = "success";
            $nome = $email = $telefone = ""; // Limpar formulário
        } else {
            $mensagem = "Erro ao cadastrar cliente: " . $conn->error;
            $tipo = "danger";
        }
    } else if ($_POST['acao'] == "atualizar") {
        // Atualizar cliente existente
        $id_atualizar = intval($_POST['id']);
        $sql = "UPDATE clientes SET nome='$nome', email='$email', telefone='$telefone' WHERE id=$id_atualizar";
        
        if ($conn->query($sql) === TRUE) {
            $mensagem = "Cliente atualizado com sucesso!";
            $tipo = "success";
            $id = $nome = $email = $telefone = ""; // Limpar formulário
            $titulo_form = "Cadastrar Novo Cliente";
            $acao = "cadastrar";
        } else {
            $mensagem = "Erro ao atualizar cliente: " . $conn->error;
            $tipo = "danger";
        }
    }
}

// Buscar todos os clientes para listagem
$sql_listar = "SELECT * FROM clientes ORDER BY nome";
$result_listar = $conn->query($sql_listar);
?>

<div class="container mt-4">
    <h2>Gerenciar Clientes</h2>
    
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mt-4">
        <!-- Formulário de Cadastro/Edição -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><?= $titulo_form ?></h3>
                </div>
                <div class="card-body">
                    <form action="gerenciar_clientes.php" method="POST">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="acao" value="<?= $acao ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($telefone) ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?= ($acao == "cadastrar") ? "Cadastrar" : "Atualizar" ?>
                        </button>
                        
                        <?php if ($acao == "atualizar"): ?>
                            <a href="gerenciar_clientes.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Listagem de Clientes -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Lista de Clientes</h3>
                </div>
                <div class="card-body">
                    <?php if ($result_listar && $result_listar->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($cliente = $result_listar->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $cliente['id'] ?></td>
                                            <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                            <td><?= htmlspecialchars($cliente['email']) ?></td>
                                            <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                                            <td>
                                                <a href="gerenciar_clientes.php?editar=<?= $cliente['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="gerenciar_clientes.php?excluir=<?= $cliente['id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum cliente cadastrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="../index.php" class="btn btn-secondary">Voltar para Home</a>
    </div>
</div>

<?php include_once '/includes/footer.php'; ?>