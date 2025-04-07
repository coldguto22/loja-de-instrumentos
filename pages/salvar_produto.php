<?php
// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Inserir o produto primeiro
        $sql = "INSERT INTO produtosx (nome, descricao, preco, estoque) 
                VALUES ('$nome', '$descricao', $preco, $estoque)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao inserir produto: " . $conn->error);
        }
        
        $produto_id = $conn->insert_id;
        
        // Processamento de imagens
        if(isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
            // Diretório para armazenar as imagens
            $upload_dir = "../uploads/";
            
            // Criar diretório se não existir
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception("Falha ao criar diretório de uploads");
                }
            }
            
            // Processar todas as imagens
            $imagens_count = count($_FILES['imagens']['name']);
            
            for ($i = 0; $i < $imagens_count; $i++) {
                if(!empty($_FILES['imagens']['name'][$i])) {
                    // Nome do arquivo com timestamp para evitar duplicatas
                    $file_name = time() . '_' . $_FILES['imagens']['name'][$i];
                    $file_tmp = $_FILES['imagens']['tmp_name'][$i];
                    $file_path = $upload_dir . $file_name;
                    
                    // Mover o arquivo para o diretório de upload
                    if(!move_uploaded_file($file_tmp, $file_path)) {
                        throw new Exception("Erro ao fazer upload da imagem " . ($i+1));
                    }
                    
                    $imagem_path = "/uploads/" . $file_name;
                    
                    // Inserir na tabela imagens
                    $sql_imagem = "INSERT INTO imagens (produto_id, caminho) VALUES ($produto_id, '$imagem_path')";
                    if (!$conn->query($sql_imagem)) {
                        throw new Exception("Erro ao salvar imagem no banco: " . $conn->error);
                    }
                }
            }
        }
        
        // Se chegou até aqui, tudo deu certo
        $conn->commit();
        $mensagem = "Produto cadastrado com sucesso!";
        $tipo = "success";
        
    } catch (Exception $e) {
        // Reverter em caso de erro
        $conn->rollback();
        $mensagem = $e->getMessage();
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
                            </div>
                            
                            <?php 
                            // Buscar e exibir imagens
                            $img_query = "SELECT caminho FROM imagens WHERE produto_id = $produto_id";
                            $img_result = $conn->query($img_query);
                            if ($img_result && $img_result->num_rows > 0):
                            ?>
                            <h5>Imagens do produto:</h5>
                            <div class="row">
                                <?php while ($img = $img_result->fetch_assoc()): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="<?= htmlspecialchars($img['caminho']) ?>" class="card-img-top" alt="Imagem do produto">
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php endif; ?>
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