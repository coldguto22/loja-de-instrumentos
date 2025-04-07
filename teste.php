<?php
include 'config/db.php';
$result = $conn->query("SHOW TABLES FROM loja_de_instrumentos");
echo "<h2>Tabelas no banco de dados:</h2>";
echo "<ul>";
while ($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Supondo que você descubra o nome correto da tabela (por exemplo, 'produtos')
$table_name = "produtosx"; // substitua pelo nome correto
$columns = $conn->query("DESCRIBE $table_name");
echo "<h2>Estrutura da tabela $table_name:</h2>";
echo "<ul>";
while ($col = $columns->fetch_assoc()) {
    echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
}
echo "</ul>";
?>