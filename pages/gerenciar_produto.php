<?php
// Adicionar no início para exibir erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../config/db.php';
include_once '../includes/header.php';

// Verificar se a tabela tem a coluna 'categoria'
$sql_check_column = "SHOW COLUMNS FROM produtosx LIKE 'categoria'";
$result_check_column = $conn->query($sql_check_column);
$tem_categoria = $result_check_column && $result_check_column->num_rows > 0;

// Se a coluna não existir, criá-la
if (!$tem_categoria) {
    $sql_add_column = "ALTER TABLE produtosx ADD COLUMN categoria VARCHAR(100) AFTER estoque";
    $conn->query($sql_add_column);
    $tem_categoria = true; // Agora temos a coluna
}

// Inicializar variáveis para o formulário
$id = "";
$nome = "";
$descricao = "";
$preco = "";
$estoque = "";
$categoria = "";
$imagens = array(); // Array para guardar as imagens do produto
$titulo_form = "Cadastrar Novo Produto";
$acao = "cadastrar";

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
    // Primeiro, buscar as imagens associadas ao produto
    $sql_imagens = "SELECT id, caminho FROM imagens WHERE produto_id = $id_excluir";
    $result_imagens = $conn->query($sql_imagens);
    
    if ($result_imagens && $result_imagens->num_rows > 0) {
        while ($imagem = $result_imagens->fetch_assoc()) {
            // Excluir o arquivo físico
            $caminho_arquivo = "../" . $imagem['caminho'];
            if (file_exists($caminho_arquivo)) {
                unlink($caminho_arquivo);
            }
            
            // Excluir o registro da imagem no banco
            $id_imagem = $imagem['id'];
            $sql_del_imagem = "DELETE FROM imagens WHERE id = $id_imagem";
            $conn->query($sql_del_imagem);
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
        $categoria = isset($produto['categoria']) ? $produto['categoria'] : '';
        
        // Buscar imagens associadas a este produto
        $sql_imagens = "SELECT id, caminho FROM imagens WHERE produto_id = $id";
        $result_imagens = $conn->query($sql_imagens);
        if ($result_imagens && $result_imagens->num_rows > 0) {
            while ($img = $result_imagens->fetch_assoc()) {
                $imagens[] = $img;
            }
        }
        
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
    $categoria = isset($_POST['categoria']) ? $conn->real_escape_string($_POST['categoria']) : '';
    
    // Tratar o caso de categoria personalizada
    if (isset($_POST['outra-categoria']) && !empty($_POST['outra-categoria']) && $categoria == 'outro') {
        $categoria = $conn->real_escape_string($_POST['outra-categoria']);
    }
    
    try {
        // Iniciando transação para garantir integridade
        $conn->begin_transaction();
        
        if ($_POST['acao'] == "cadastrar") {
            // Inserir novo produto
            $sql = "INSERT INTO produtosx (nome, descricao, preco, estoque, categoria) 
                    VALUES ('$nome', '$descricao', $preco, $estoque, '$categoria')";
            
            if ($conn->query($sql) === TRUE) {
                $produto_id = $conn->insert_id; // ID do produto recém-inserido
                
                // Processar upload de imagens (múltiplas)
                if (isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
                    $upload_dir = "../images/produtos/";
                    
                    // Criar diretório se não existir
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Processar cada arquivo de imagem
                    $total_arquivos = count($_FILES['imagens']['name']);
                    
                    for ($i = 0; $i < $total_arquivos; $i++) {
                        if ($_FILES['imagens']['error'][$i] == 0) {
                            $file_name = time() . '_' . $_FILES['imagens']['name'][$i];
                            $file_tmp = $_FILES['imagens']['tmp_name'][$i];
                            
                            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                                $caminho_imagem = "/images/produtos/" . $file_name;
                                
                                // Inserir imagem no banco
                                $sql_img = "INSERT INTO imagens (produto_id, caminho) VALUES ($produto_id, '$caminho_imagem')";
                                $conn->query($sql_img);
                            }
                        }
                    }
                }
                
                $conn->commit();
                $mensagem = "Produto cadastrado com sucesso!";
                $tipo = "success";
                $nome = $descricao = $preco = $estoque = $categoria = ""; // Limpar formulário
                $imagens = array();
            } else {
                throw new Exception("Erro ao cadastrar produto: " . $conn->error);
            }
        } else if ($_POST['acao'] == "atualizar") {
            // Atualizar produto existente
            $id_atualizar = intval($_POST['id']);
            $sql = "UPDATE produtosx 
                    SET nome='$nome', descricao='$descricao', preco=$preco, estoque=$estoque, categoria='$categoria'
                    WHERE id=$id_atualizar";
            
            if ($conn->query($sql) === TRUE) {
                // Processar novas imagens se enviadas
                if (isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
                    $upload_dir = "../images/produtos/";
                    
                    // Criar diretório se não existir
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Processar cada arquivo de imagem
                    $total_arquivos = count($_FILES['imagens']['name']);
                    
                    for ($i = 0; $i < $total_arquivos; $i++) {
                        if ($_FILES['imagens']['error'][$i] == 0) {
                            $file_name = time() . '_' . $_FILES['imagens']['name'][$i];
                            $file_tmp = $_FILES['imagens']['tmp_name'][$i];
                            
                            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                                $caminho_imagem = "/images/produtos/" . $file_name;
                                
                                // Inserir imagem no banco
                                $sql_img = "INSERT INTO imagens (produto_id, caminho) VALUES ($id_atualizar, '$caminho_imagem')";
                                $conn->query($sql_img);
                            }
                        }
                    }
                }
                
                // Processar exclusão de imagens selecionadas
                if (isset($_POST['excluir_imagens']) && is_array($_POST['excluir_imagens'])) {
                    foreach ($_POST['excluir_imagens'] as $id_imagem) {
                        $id_imagem = intval($id_imagem);
                        
                        // Obter caminho da imagem
                        $sql_get_imagem = "SELECT caminho FROM imagens WHERE id = $id_imagem";
                        $result_get_imagem = $conn->query($sql_get_imagem);
                        
                        if ($result_get_imagem && $result_get_imagem->num_rows > 0) {
                            $imagem = $result_get_imagem->fetch_assoc();
                            $caminho_arquivo = "../" . $imagem['caminho'];
                            
                            // Excluir arquivo físico
                            if (file_exists($caminho_arquivo)) {
                                unlink($caminho_arquivo);
                            }
                            
                            // Excluir registro do banco
                            $sql_del_imagem = "DELETE FROM imagens WHERE id = $id_imagem";
                            $conn->query($sql_del_imagem);
                        }
                    }
                }
                
                $conn->commit();
                $mensagem = "Produto atualizado com sucesso!";
                $tipo = "success";
                $id = $nome = $descricao = $preco = $estoque = $categoria = ""; // Limpar formulário
                $imagens = array();
                $titulo_form = "Cadastrar Novo Produto";
                $acao = "cadastrar";
            } else {
                throw new Exception("Erro ao atualizar produto: " . $conn->error);
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $mensagem = $e->getMessage();
        $tipo = "danger";
    }
}

// Buscar categorias disponíveis (opcional - você pode adaptar isso para obter de uma tabela de categorias)
$categorias = ["Cordas", "Percussão", "Sopro", "Teclas", "Acessórios", "Amplificadores", "Outros"];

// Buscar todos os produtos para listagem
$sql_listar = "SELECT p.*, (SELECT caminho FROM imagens WHERE produto_id = p.id LIMIT 1) as imagem_principal 
               FROM produtosx p ORDER BY p.nome";
$result_listar = $conn->query($sql_listar);
?>

<div class="container mt-4">
    <h2>Gerenciar Produtos</h2>
    
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
                        
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="" <?= empty($categoria) ? 'selected' : '' ?>>Selecione uma categoria</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria == $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                                <!-- Opção adicional para inserir uma nova categoria -->
                                <option value="outro">Outra categoria</option>
                            </select>
                        </div>
                        <!-- Campo para digitar uma nova categoria (inicialmente oculto) -->
                        <div class="mb-3" id="outra-categoria-div" style="display: none;">
                            <label for="outra-categoria" class="form-label">Especifique a categoria</label>
                            <input type="text" class="form-control" id="outra-categoria" name="outra-categoria">
                        </div>
                        
                        <?php if (!empty($imagens)): ?>
                            <div class="mb-3">
                                <label class="form-label">Imagens Atuais</label>
                                <div class="row">
                                    <?php foreach ($imagens as $img): ?>
                                        <div class="col-4 mb-2">
                                            <div class="position-relative">
                                                <img src="<?= htmlspecialchars($img['caminho']) ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                                <div class="form-check position-absolute" style="top: 5px; right: 5px;">
                                                    <input class="form-check-input" type="checkbox" name="excluir_imagens[]" value="<?= $img['id'] ?>" id="excluir_img_<?= $img['id'] ?>">
                                                    <label class="form-check-label" for="excluir_img_<?= $img['id'] ?>">
                                                        <span class="badge bg-danger">Remover</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Marque as imagens que deseja remover.</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="imagens" class="form-label">
                                <?= ($acao == "cadastrar") ? "Imagens do Produto" : "Adicionar Novas Imagens" ?>
                            </label>
                            <input type="file" class="form-control" id="imagens" name="imagens[]" multiple <?= ($acao == "cadastrar" && empty($imagens)) ? "required" : "" ?>>
                            <small class="text-muted">Você pode selecionar múltiplas imagens.</small>
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
        
        <!-- Listagem de produtos -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Lista de Produtos</h3>
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
                                        <th>Categoria</th>
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
                                                <?php if (!empty($produto['imagem_principal'])): ?>
                                                    <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="Thumbnail" style="max-height: 50px;">
                                                <?php else: ?>
                                                    <span class="text-muted">Sem imagem</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($produto['nome']) ?></td>
                                            <td><?= htmlspecialchars(isset($produto['categoria']) ? $produto['categoria'] : '') ?></td>
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

<!-- Script para tratar a opção "Outra categoria" -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoriaSelect = document.getElementById('categoria');
    const outraCategoriaDiv = document.getElementById('outra-categoria-div');
    const outraCategoriaInput = document.getElementById('outra-categoria');
    
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', function() {
            if (this.value === 'outro') {
                outraCategoriaDiv.style.display = 'block';
                outraCategoriaInput.setAttribute('required', 'required');
            } else {
                outraCategoriaDiv.style.display = 'none';
                outraCategoriaInput.removeAttribute('required');
            }
        });
        
        // Verificar o estado inicial
        if (categoriaSelect.value === 'outro') {
            outraCategoriaDiv.style.display = 'block';
            outraCategoriaInput.setAttribute('required', 'required');
        }
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>