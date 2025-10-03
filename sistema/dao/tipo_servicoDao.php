<?php
require_once("../model/conexao.php");
require_once("../model/tipo_servico.php");

class Tipo_servicoDao {
    private $con;
    public function __construct(){
        $this->con=(new Conexao())->conectar();
    }

    function inserir($obj) {
        $sql = "INSERT INTO tipo_servico (nome) VALUES (?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getNome()]);
    }

    function alterar($obj, $idValue){
        $sql = "UPDATE tipo_servico SET nome=? WHERE id=?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getNome(), $idValue]);
    }

    function listaGeral(){
        $sql = "select * from tipo_servico";
        $query = $this->con->query($sql);
        $dados = $query->fetchAll(PDO::FETCH_ASSOC);
        return $dados;
    }
    
    function buscarTodos(){
        return $this->listaGeral();
    }

    function excluir($id){
        $sql = "delete from tipo_servico where id=:id";
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function buscaPorId($id){
        $sql = "select * from tipo_servico where id=:id"; 
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados;
    }

}
?>