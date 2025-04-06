<?php
session_start();
include_once 'config/db.php';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    
    // Obter o ID do produto
    $product_id = intval($_POST['product_id']);
    
    // Buscar informações do produto no banco de dados
    $sql = "SELECT * FROM produtosx WHERE id = $product_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $produto = $result->fetch_assoc();
        
        // Inicializar o carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
        
        // Verificar se o produto já está no carrinho
        $encontrado = false;
        foreach ($_SESSION['carrinho'] as $key => $item) {
            if ($item['id'] == $product_id) {
                // Incrementar a quantidade
                $_SESSION['carrinho'][$key]['quantidade']++;
                $encontrado = true;
                break;
            }
        }
        
        // Se o produto não estiver no carrinho, adicionar
        if (!$encontrado) {
            $_SESSION['carrinho'][] = [
                'id' => $product_id,
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => 1,
                'imagem' => $produto['imagem']
            ];
        }
        
        // Redirecionar para a página do carrinho
        header("Location: pages/carrinho.php");
        exit;
    } else {
        // Produto não encontrado
        include_once 'includes/header.php';
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger" role="alert">Produto não encontrado!</div>';
        echo '<a href="index.php" class="btn btn-primary">Voltar para Home</a>';
        echo '</div>';
        include_once 'includes/footer.php';
    }
} else {
    // Redirecionamento se tentar acessar diretamente
    header("Location: index.php");
    exit;
}
?>