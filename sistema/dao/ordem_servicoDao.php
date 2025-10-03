<?php
require_once("../model/conexao.php");
require_once("../model/ordem_servico.php");

class Ordem_servicoDao {
    private $con;
    public function __construct(){
        $this->con=(new Conexao())->conectar();
    }

    function inserir($obj) {
        $sql = "INSERT INTO ordem_servico (descricao_problema, data_abertura, prazo_estimado, status, id_cliente, id_tipo_servico) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getDescricao_problema(),$obj->getData_abertura(),$obj->getPrazo_estimado(),$obj->getStatus(),$obj->getId_cliente(),$obj->getId_tipo_servico()]);
    }

    function alterar($obj, $idValue){
        $sql = "UPDATE ordem_servico SET descricao_problema=?, data_abertura=?, prazo_estimado=?, status=?, id_cliente=?, id_tipo_servico=? WHERE id=?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$obj->getDescricao_problema(),$obj->getData_abertura(),$obj->getPrazo_estimado(),$obj->getStatus(),$obj->getId_cliente(),$obj->getId_tipo_servico(), $idValue]);
    }

    function listaGeral(){
        $sql = "select * from ordem_servico";
        $query = $this->con->query($sql);
        $dados = $query->fetchAll(PDO::FETCH_ASSOC);
        return $dados;
    }
    
    function buscarTodos(){
        return $this->listaGeral();
    }

    function excluir($id){
        $sql = "delete from ordem_servico where id=:id";
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function buscaPorId($id){
        $sql = "select * from ordem_servico where id=:id"; 
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados;
    }

}
?>