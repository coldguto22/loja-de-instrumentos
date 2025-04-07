<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once '../config/db.php';
include_once '../includes/header.php';

// Verificar se o carrinho existe e não está vazio
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit;
}

// Processar o pedido quando o formulário for enviado
$pedido_finalizado = false;
$pedido_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Obter e sanitizar os dados do formulário
        $nome = $conn->real_escape_string($_POST['nome']);
        $email = $conn->real_escape_string($_POST['email']);
        $telefone = $conn->real_escape_string($_POST['telefone']);
        $forma_pagamento = $conn->real_escape_string($_POST['forma_pagamento']);
        
        // 1. Verificar se o cliente já existe
        $sql_verifica = "SELECT id FROM clientes WHERE email = ?";
        $stmt = $conn->prepare($sql_verifica);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result_verifica = $stmt->get_result();
        
        if ($result_verifica->num_rows > 0) {
            // Cliente já existe, pega o ID
            $row = $result_verifica->fetch_assoc();
            $cliente_id = $row['id'];
        } else {
            // Cliente não existe, então insere
            $sql_cliente = "INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_cliente);
            $stmt->bind_param("sss", $nome, $email, $telefone);
            $stmt->execute();
            $cliente_id = $conn->insert_id;
        }
        
        // 3. Inserir pedido
        $sql_pedido = "INSERT INTO pedidos (cliente_id, forma_pagamento, status, data_pedido) 
        VALUES (?, ?, 'Pendente', NOW())";
        $stmt = $conn->prepare($sql_pedido);
        $stmt->bind_param("is", $cliente_id, $forma_pagamento);
        
        if ($pedido_id) {
            $total_pedido = 0;
            
            // Inserir itens do pedido
            foreach ($_SESSION['carrinho'] as $item) {
                $produto_id = $item['id'];
                $quantidade = $item['quantidade'];
                $preco = $item['preco'];
                $subtotal = $quantidade * $preco;
                $total_pedido += $subtotal;
                
                $sql_item = "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) 
                              VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_item);
                $stmt->bind_param("iiid", $pedido_id, $produto_id, $quantidade, $preco);
                $stmt->execute();
                
                // Atualizar estoque do produto
                $sql_estoque = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
                $stmt = $conn->prepare($sql_estoque);
                $stmt->bind_param("ii", $quantidade, $produto_id);
                $stmt->execute();
            }
            
            // Atualizar o total do pedido
            $sql_total = "UPDATE pedidos SET total = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_total);
            $stmt->bind_param("di", $total_pedido, $pedido_id);
            $stmt->execute();
            
            // Finalizar transação
            $conn->commit();
            
            // Limpar o carrinho
            unset($_SESSION['carrinho']);
            
            $pedido_finalizado = true;
            $mensagem = "Pedido realizado com sucesso! Seu número de pedido é: #$pedido_id";
            $tipo = "success";
        } else {
            throw new Exception("Erro ao inserir pedido");
        }
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        $mensagem = "Erro ao processar pedido: " . $e->getMessage();
        $tipo = "danger";
    }
}
?>

<div class="container mt-4">
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($pedido_finalizado): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2 class="card-title">Pedido Confirmado</h2>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h3 class="mt-3">Obrigado pela sua compra!</h3>
                    <p class="lead">Seu pedido #<?= $pedido_id ?> foi recebido e está sendo processado.</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Dados do Cliente</h4>
                        <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                        <p><strong>Telefone:</strong> <?= htmlspecialchars($telefone) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4>Informações do Pedido</h4>
                        <p><strong>Número do Pedido:</strong> #<?= $pedido_id ?></p>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i:s') ?></p>
                        <p><strong>Status:</strong> Pendente</p>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="../index.php" class="btn btn-primary">Voltar para Home</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="card-title">Finalizar Pedido</h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <h4 class="mb-3">Dados para Entrega</h4>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" required>
                            </div>
                            
                            <h4 class="mb-3 mt-4">Forma de Pagamento</h4>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="boleto" value="boleto" checked>
                                <label class="form-check-label" for="boleto">
                                    Boleto Bancário
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="cartao" value="cartao">
                                <label class="form-check-label" for="cartao">
                                    Cartão de Crédito
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="radio" name="forma_pagamento" id="pix" value="pix">
                                <label class="form-check-label" for="pix">
                                    PIX
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">Confirmar Compra</button>
                                <a href="carrinho.php" class="btn btn-outline-secondary">Voltar para o Carrinho</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="card-title mb-0">Resumo do Pedido</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group mb-3">
                            <?php $total = 0; foreach ($_SESSION['carrinho'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-sm">
                                    <div>
                                        <h6 class="my-0"><?= htmlspecialchars($item['nome']) ?></h6>
                                        <small class="text-muted"><?= $item['quantidade'] ?> x R$ <?= number_format($item['preco'], 2, ',', '.') ?></small>
                                    </div>
                                    <span class="text-muted">R$ <?= number_format($item['quantidade'] * $item['preco'], 2, ',', '.') ?></span>
                                </li>
                                <?php $total += $item['quantidade'] * $item['preco']; ?>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Frete</span>
                                <strong>Grátis</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total</span>
                                <strong>R$ <?= number_format($total, 2, ',', '.') ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>