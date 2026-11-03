
<?php

header("Content-Type: text/plain; charset=utf-8");
header("Cache-Control: no-store");

try {

    $ficheiro = "C:/xampp/subterra-private/database.php";

    if (!file_exists($ficheiro)) {
        throw new Exception(
            "Ficheiro database.php nao encontrado."
        );
    }

    require_once $ficheiro;

    echo "SUBTERRA - DIAGNOSTICO\n\n";

    echo "Ficheiro de configuracao: OK\n";

    echo "A tentar conectar...\n\n";

    $conexao = conectarBD();

    $resultado = $conexao->query(
        "SELECT DATABASE() AS banco, @@port AS porta"
    );

    $dados = $resultado->fetch();

    echo "Estado: CONECTADO\n";
    echo "Base de dados: " . $dados["banco"] . "\n";
    echo "Porta: " . $dados["porta"] . "\n";

    echo "\nPHP + MySQL: FUNCIONAL\n";

} catch (Throwable $erro) {

    http_response_code(500);

    echo "SUBTERRA - ERRO DETECTADO\n\n";

    echo "Tipo: " . get_class($erro) . "\n";

    echo "Codigo: " . $erro->getCode() . "\n";

    echo "Mensagem: " . $erro->getMessage() . "\n";

}
