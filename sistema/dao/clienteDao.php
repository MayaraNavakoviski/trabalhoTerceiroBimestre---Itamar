<?php
require_once("../model/conexao.php");
require_once("../model/cliente.php");

class ClienteDao {
    private $con;
    public function __construct(){
        $this->con=(new Conexao())->conectar();
    }

    function inserir($obj) {
        $sql = "INSERT INTO cliente (nome, telefone, email) VALUES (?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getNome(),$obj->getTelefone(),$obj->getEmail()]);
    }

    function alterar($obj, $idValue){
        $sql = "UPDATE cliente SET nome=?, telefone=?, email=? WHERE id=?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getNome(),$obj->getTelefone(),$obj->getEmail(), $idValue]);
    }

    function listaGeral(){
        $sql = "select * from cliente";
        $query = $this->con->query($sql);
        $dados = $query->fetchAll(PDO::FETCH_ASSOC);
        return $dados;
    }
    
    function buscarTodos(){
        return $this->listaGeral();
    }

    function excluir($id){
        $sql = "delete from cliente where id=:id";
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function buscaPorId($id){
        $sql = "select * from cliente where id=:id"; 
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados;
    }

}
?>