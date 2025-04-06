<?php
include_once __DIR__ . '/../config/db.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>E-commerce de Instrumentos Musicais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7xj1z5l5e5e5e5e5e5e5e5e5e5e5e5e5" crossorigin="anonymous">
</head>
<body>

<header class="bg-dark text-white py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">E-commerce de Instrumentos Musicais</h1>
            <nav>
                <a href="/index.php" class="btn btn-outline-light me-2">Home</a>
                <a href="/pages/cadastro_cliente.php" class="btn btn-outline-light me-2">Clientes</a>
                <a href="/pages/cadastro_produto.php" class="btn btn-outline-light me-2">Produtos</a>
                <a href="/pages/carrinho.php" class="btn btn-warning">Carrinho</a>
            </nav>
        </div>
    </div>
</header>

<main class="container mt-4">
