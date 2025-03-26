<?php
$servername = "localhost";
$username = "root"; // Usuário padrão do USBWebServer
$password = "usbw"; // Senha padrão (vazia)
$database = "loja_de_instrumentos"; // Nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
