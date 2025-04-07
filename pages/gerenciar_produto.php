<?php
include_once '../config/db.php';
include_once '../includes/header.php';

// Inicializar variáveis para o formulário
$id = "";
$nome = "";
$descricao = "";
$preco = "";
$estoque = "";
$imagem_atual = "";
$titulo_form = "Cadastrar Novo Produto";
$acao = "cadastrar";

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
    // Primeiro, verificar se há imagem para excluir
    $sql_imagem = "SELECT imagem FROM produtosx WHERE id = $id_excluir";
    $result_imagem = $conn->query($sql_imagem);
    
    if ($result_imagem && $result_imagem->num_rows > 0) {
        $produto = $result_imagem->fetch_assoc();
        if (!empty($produto['imagem']) && file_exists(".." . $produto['imagem'])) {
            unlink(".." . $produto['imagem']);
        }
    }
    
    // Agora exclui o produto
    $sql_excluir = "DELETE FROM produtosx WHERE id = $id_excluir";
    
    if ($conn->query($sql_excluir) === TRUE) {
        $mensagem = "Produto excluído com sucesso!";
        $tipo = "success";
    } else {
        $mensagem = "Erro ao excluir produto: " . $conn->error;
        $tipo = "danger";
    }
}

// Verificar se é uma edição
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $sql_editar = "SELECT * FROM produtosx WHERE id = $id_editar";
    $result_editar = $conn->query($sql_editar);
    
    if ($result_editar && $result_editar->num_rows > 0) {
        $produto = $result_editar->fetch_assoc();
        $id = $produto['id'];
        $nome = $produto['nome'];
        $descricao = $produto['descricao'];
        $preco = $produto['preco'];
        $estoque = $produto['estoque'];
        $imagem_atual = $produto['imagem'];
        $titulo_form = "Editar Produto";
        $acao = "atualizar";
    }
}

// Processar o formulário de cadastro/atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $preco = floatval(str_replace(',', '.', $_POST['preco']));
    $estoque = intval($_POST['estoque']);
    $imagem_path = isset($_POST['imagem_atual']) ? $_POST['imagem_atual'] : "";
    
    // Processar upload de imagem se enviada
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $upload_dir = "../uploads/";
        
        // Criar diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Nome do arquivo com timestamp para evitar duplicatas
        $file_name = time() . '_' . $_FILES['imagem']['name'];
        $file_tmp = $_FILES['imagem']['tmp_name'];
        
        // Mover o arquivo para o diretório de upload
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            // Se houver uma imagem anterior e não for a padrão, exclui
            if ($_POST['acao'] == "atualizar" && !empty($_POST['imagem_atual']) && file_exists(".." . $_POST['imagem_atual'])) {
                unlink(".." . $_POST['imagem_atual']);
            }
            
            $imagem_path = "/uploads/" . $file_name;
        } else {
            $erro_upload = "Erro ao fazer upload da imagem.";
        }
    }
    
    if (!isset($erro_upload)) {
        if ($_POST['acao'] == "cadastrar") {
            // Inserir novo produto
            $sql = "INSERT INTO produtosx (nome, descricao, preco, estoque, imagem) 
                    VALUES ('$nome', '$descricao', $preco, $estoque, '$imagem_path')";
            
            if ($conn->query($sql) === TRUE) {
                $mensagem = "Produto cadastrado com sucesso!";
                $tipo = "success";
                $nome = $descricao = $preco = $estoque = $imagem_atual = ""; // Limpar formulário
            } else {
                $mensagem = "Erro ao cadastrar produto: " . $conn->error;
                $tipo = "danger";
            }
        } else if ($_POST['acao'] == "atualizar") {
            // Atualizar produto existente
            $id_atualizar = intval($_POST['id']);
            $sql = "UPDATE produtosx 
                    SET nome='$nome', descricao='$descricao', preco=$preco, estoque=$estoque" . 
                    (!empty($imagem_path) ? ", imagem='$imagem_path'" : "") . 
                    " WHERE id=$id_atualizar";
            
            if ($conn->query($sql) === TRUE) {
                $mensagem = "Produto atualizado com sucesso!";
                $tipo = "success";
                $id = $nome = $descricao = $preco = $estoque = $imagem_atual = ""; // Limpar formulário
                $titulo_form = "Cadastrar Novo Produto";
                $acao = "cadastrar";
            } else {
                $mensagem = "Erro ao atualizar produto: " . $conn->error;
                $tipo = "danger";
            }
        }
    } else {
        $mensagem = $erro_upload;
        $tipo = "danger";
    }
}

// Buscar todos os produtosx para listagem
$sql_listar = "SELECT * FROM produtosx ORDER BY nome";
$result_listar = $conn->query($sql_listar);
?>

<div class="container mt-4">
    <h2>Gerenciar produtos</h2>
    
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
                    <form action="gerenciar_produto.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="acao" value="<?= $acao ?>">
                        <input type="hidden" name="imagem_atual" value="<?= $imagem_atual ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?= htmlspecialchars($descricao) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="preco" class="form-label">Preço (R$)</label>
                            <input type="text" class="form-control" id="preco" name="preco" value="<?= $preco ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="estoque" class="form-label">Estoque</label>
                            <input type="number" class="form-control" id="estoque" name="estoque" value="<?= $estoque ?>" required>
                        </div>
                        
                        <?php if (!empty($imagem_atual)): ?>
                            <div class="mb-3">
                                <label class="form-label">Imagem Atual</label>
                                <div class="border p-2 mb-2">
                                    <img src="<?= htmlspecialchars($imagem_atual) ?>" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="imagem" class="form-label">
                                <?= ($acao == "cadastrar") ? "Imagem do Produto" : "Nova Imagem (opcional)" ?>
                            </label>
                            <input type="file" class="form-control" id="imagem" name="imagem" <?= ($acao == "cadastrar") ? "required" : "" ?>>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?= ($acao == "cadastrar") ? "Cadastrar" : "Atualizar" ?>
                        </button>
                        
                        <?php if ($acao == "atualizar"): ?>
                            <a href="gerenciar_produto.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Listagem de produtosx -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Lista de produtos</h3>
                </div>
                <div class="card-body">
                    <?php if ($result_listar && $result_listar->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagem</th>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($produto = $result_listar->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $produto['id'] ?></td>
                                            <td>
                                                <?php if (!empty($produto['imagem'])): ?>
                                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="Thumbnail" style="max-height: 50px;">
                                                <?php else: ?>
                                                    <span class="text-muted">Sem imagem</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($produto['nome']) ?></td>
                                            <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                            <td><?= $produto['estoque'] ?></td>
                                            <td>
                                                <a href="gerenciar_produto.php?editar=<?= $produto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="gerenciar_produto.php?excluir=<?= $produto['id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum produto cadastrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="../index.php" class="btn btn-secondary">Voltar para Home</a>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>