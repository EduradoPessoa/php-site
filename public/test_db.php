<?php
require_once '../config/config.php';

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

try {
    echo "<h2>Verificando arquivo do banco de dados:</h2>";
    echo "Caminho do banco: " . DB_PATH . "<br>";
    echo "Arquivo existe? " . (file_exists(DB_PATH) ? "Sim" : "Não") . "<br>";
    echo "Permissão de leitura? " . (is_readable(DB_PATH) ? "Sim" : "Não") . "<br>";
    echo "Permissão de escrita? " . (is_writable(DB_PATH) ? "Sim" : "Não") . "<br>";
    
    echo "<h2>Verificando tabelas:</h2>";
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas encontradas: " . implode(", ", $tables) . "<br>";
    
    echo "<h2>Testando consulta na tabela products:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de produtos: " . $result['total'] . "<br>";
    
    echo "<h2>Estrutura da tabela products:</h2>";
    $stmt = $pdo->query("PRAGMA table_info(products)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Erro:</h2>";
    echo $e->getMessage() . "<br>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
}
