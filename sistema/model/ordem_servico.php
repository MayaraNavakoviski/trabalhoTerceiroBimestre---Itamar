<?php
class Ordem_servico {
	private $id;
	private $descricao_problema;
	private $data_abertura;
	private $prazo_estimado;
	private $status;
	private $id_cliente;
	private $id_tipo_servico;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id=$id;
	}
	function getDescricao_problema(){
		return $this->descricao_problema;
	}
	function setDescricao_problema($descricao_problema){
		$this->descricao_problema=$descricao_problema;
	}
	function getData_abertura(){
		return $this->data_abertura;
	}
	function setData_abertura($data_abertura){
		$this->data_abertura=$data_abertura;
	}
	function getPrazo_estimado(){
		return $this->prazo_estimado;
	}
	function setPrazo_estimado($prazo_estimado){
		$this->prazo_estimado=$prazo_estimado;
	}
	function getStatus(){
		return $this->status;
	}
	function setStatus($status){
		$this->status=$status;
	}
	function getId_cliente(){
		return $this->id_cliente;
	}
	function setId_cliente($id_cliente){
		$this->id_cliente=$id_cliente;
	}
	function getId_tipo_servico(){
		return $this->id_tipo_servico;
	}
	function setId_tipo_servico($id_tipo_servico){
		$this->id_tipo_servico=$id_tipo_servico;
	}

}
?>