<?php
// Incluir conexão com o banco de dados
include_once '../config/db.php';
include_once '../includes/header.php';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coletar dados do formulário
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Hash da senha para segurança
    $telefone = $conn->real_escape_string($_POST['telefone']);
    
    // Preparar e executar a consulta SQL
    $sql = "INSERT INTO clientes (nome, email, senha, telefone) VALUES ('$nome', '$email', '$senha', '$telefone')";
    
    if ($conn->query($sql) === TRUE) {
        $mensagem = "Cliente cadastrado com sucesso!";
        $tipo = "success";
    } else {
        $mensagem = "Erro ao cadastrar cliente: " . $conn->error;
        $tipo = "danger";
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
                    <?= $mensagem ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-<?= isset($tipo) ? $tipo : 'primary' ?>">
                    <h3 class="card-title text-white mb-0">Status do Cadastro</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($mensagem)): ?>
                        <p><?= $mensagem ?></p>
                        
                        <?php if ($tipo == "success"): ?>
                            <p>O cliente foi registrado com as seguintes informações:</p>
                            <ul>
                                <li><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></li>
                                <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
                                <li><strong>Telefone:</strong> <?= htmlspecialchars($telefone) ?></li>
                            </ul>
                        <?php elseif (strpos($mensagem, "Duplicate entry") !== false): ?>
                            <p>Este email já está registrado no sistema.</p>
                            <p>Por favor, tente novamente com um email diferente ou faça login se já possui uma conta.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Nenhum dado foi enviado. Por favor, preencha o formulário de cadastro.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="cadastro_cliente.php" class="btn btn-secondary me-2">Voltar para Cadastro</a>
                    <?php if (isset($tipo) && $tipo == "success"): ?>
                        <a href="../index.php" class="btn btn-primary">Ir para Home</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>