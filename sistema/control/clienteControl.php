<?php
require_once("../model/cliente.php");
require_once("../dao/clienteDao.php");

class ClienteControl {
    private $cliente;
    private $acao;
    private $dao;

    public function __construct(){
        $this->cliente=new Cliente();
        $this->dao=new ClienteDao();
        $this->acao=$_REQUEST["a"] ?? null;
        $this->verificaAcao(); 
    }
    
    function verificaAcao(){
        if($this->acao == 1){
            $this->inserir();
        } else if ($this->acao == 2){
            $this->excluir();
        } else if ($this->acao == 3){
            $this->alterar();
        } else {
        }
    }
 
    function inserir(){
        $this->cliente->setNome($_POST['nome']);
		$this->cliente->setTelefone($_POST['telefone']);
		$this->cliente->setEmail($_POST['email']);
		
        $this->dao->inserir($this->cliente);
        header("Location:../view/listaCliente.php");
    }

    function excluir(){
        $this->dao->excluir($_REQUEST['id']);
        header("Location:../view/listaCliente.php");
    }

    function alterar(){
        $this->cliente->setNome($_POST['nome']);
		$this->cliente->setTelefone($_POST['telefone']);
		$this->cliente->setEmail($_POST['email']);
		
        $this->dao->alterar($this->cliente, $_REQUEST['id']); 
        header("Location:../view/listaCliente.php");
    }

}
new ClienteControl();
?>