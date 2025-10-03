<?php
class Conexao {
    private $server;
    private $banco;
    private $usuario;
    private $senha;
    
    function __construct() {
        $this->server = 'localhost';
        $this->banco = 'db_gestao_computadores';
        $this->usuario = 'root';
        $this->senha = '';
    }
    
    function conectar() {
        try {
            $conn = new PDO(
                "mysql:host=" . $this->server . ";dbname=" . $this->banco,
                $this->usuario,
                $this->senha,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (Exception $e) {
            echo "Erro ao conectar com o Banco de dados: " . $e->getMessage();
            return null;
        }
    }
}
?>