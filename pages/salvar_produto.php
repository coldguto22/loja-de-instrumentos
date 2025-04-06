<?php
// Incluir conexão com o banco de dados
include_once '../config/db.php';
include_once '../includes/header.php';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coletar dados do formulário
    $nome = $conn->real_escape_string($_POST['nome']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $estoque = intval($_POST['estoque']);
    
    // Processamento de imagens
    $imagem_path = ""; // Valor padrão, caso não envie imagem
    
    // Verificar se há imagens enviadas
    if(isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
        // Diretório para armazenar as imagens
        $upload_dir = "../uploads/";
        
        // Criar diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Nome do arquivo com timestamp para evitar duplicatas
        $file_name = time() . '_' . $_FILES['imagens']['name'][0];
        $file_tmp = $_FILES['imagens']['tmp_name'][0];
        
        // Mover o arquivo para o diretório de upload
        if(move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $imagem_path = "/uploads/" . $file_name;
        } else {
            $erro_upload = "Erro ao fazer upload da imagem.";
        }
    }
    
    // Preparar e executar a consulta SQL se não houver erro de upload
    if (!isset($erro_upload)) {
        $sql = "INSERT INTO produtosx (nome, descricao, preco, estoque, imagem) 
                VALUES ('$nome', '$descricao', $preco, $estoque, '$imagem_path')";
        
        if ($conn->query($sql) === TRUE) {
            $mensagem = "Produto cadastrado com sucesso!";
            $tipo = "success";
            $produto_id = $conn->insert_id;
        } else {
            $mensagem = "Erro ao cadastrar produto: " . $conn->error;
            $tipo = "danger";
        }
    } else {
        $mensagem = $erro_upload;
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
                    <h3 class="card-title text-white mb-0">Status do Cadastro de Produto</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($mensagem)): ?>
                        <p><?= $mensagem ?></p>
                        
                        <?php if (isset($tipo) && $tipo == "success"): ?>
                            <p>O produto foi registrado com as seguintes informações:</p>
                            <div class="row">
                                <div class="col-md-8">
                                    <ul>
                                        <li><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></li>
                                        <li><strong>Descrição:</strong> <?= htmlspecialchars($descricao) ?></li>
                                        <li><strong>Preço:</strong> R$ <?= number_format($preco, 2, ',', '.') ?></li>
                                        <li><strong>Estoque:</strong> <?= $estoque ?> unidades</li>
                                        <li><strong>ID:</strong> <?= $produto_id ?></li>
                                    </ul>
                                </div>
                                <?php if (!empty($imagem_path)): ?>
                                <div class="col-md-4">
                                    <div class="card">
                                        <img src="<?= htmlspecialchars($imagem_path) ?>" class="card-img-top" alt="Imagem do produto">
                                        <div class="card-body">
                                            <p class="card-text">Imagem enviada com sucesso.</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Nenhum dado foi enviado. Por favor, preencha o formulário de cadastro de produto.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="cadastro_produto.php" class="btn btn-secondary me-2">Voltar para Cadastro de Produtos</a>
                    <?php if (isset($tipo) && $tipo == "success"): ?>
                        <a href="../index.php" class="btn btn-primary">Ir para Home</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>