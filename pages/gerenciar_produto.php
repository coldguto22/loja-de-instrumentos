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
$titulo_form = "Cadastrar Novo Produto";
$acao = "cadastrar";

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
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
                $conn->commit();
                $mensagem = "Produto cadastrado com sucesso!";
                $tipo = "success";
                $nome = $descricao = $preco = $estoque = $categoria = ""; // Limpar formulário
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
                $conn->commit();
                $mensagem = "Produto atualizado com sucesso!";
                $tipo = "success";
                $id = $nome = $descricao = $preco = $estoque = $categoria = ""; // Limpar formulário
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
$sql_listar = "SELECT * FROM produtosx ORDER BY nome";
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
                    <form action="gerenciar_produto.php" method="POST">
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
