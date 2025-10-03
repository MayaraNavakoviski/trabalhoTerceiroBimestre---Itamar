<?php
class Cliente {
	private $id;
	private $nome;
	private $telefone;
	private $email;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id=$id;
	}
	function getNome(){
		return $this->nome;
	}
	function setNome($nome){
		$this->nome=$nome;
	}
	function getTelefone(){
		return $this->telefone;
	}
	function setTelefone($telefone){
		$this->telefone=$telefone;
	}
	function getEmail(){
		return $this->email;
	}
	function setEmail($email){
		$this->email=$email;
	}

}
?>