<?php
include_once __DIR__ . '/../config/db.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>E-commerce de Instrumentos Musicais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<header class="bg-dark text-white py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">E-commerce de Instrumentos Musicais</h1>
            <nav>
                <a href="/index.php" class="btn btn-outline-light me-2">Home</a>
                <a href="/pages/clientes.php" class="btn btn-outline-light me-2">Clientes</a>
                <a href="/pages/produtos.php" class="btn btn-outline-light me-2">Produtos</a>
                <a href="/pages/carrinho.php" class="btn btn-warning">Carrinho</a>
            </nav>
        </div>
    </div>
</header>

<main class="container mt-4">
