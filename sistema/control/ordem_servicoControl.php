<?php
require_once("../model/ordem_servico.php");
require_once("../dao/ordem_servicoDao.php");

class Ordem_servicoControl {
    private $ordem_servico;
    private $acao;
    private $dao;

    public function __construct(){
        $this->ordem_servico=new Ordem_servico();
        $this->dao=new Ordem_servicoDao();
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
        $this->ordem_servico->setDescricao_problema($_POST['descricao_problema']);
		$this->ordem_servico->setData_abertura($_POST['data_abertura']);
		$this->ordem_servico->setPrazo_estimado($_POST['prazo_estimado']);
		$this->ordem_servico->setStatus($_POST['status']);
		$this->ordem_servico->setId_cliente($_POST['id_cliente']);
		$this->ordem_servico->setId_tipo_servico($_POST['id_tipo_servico']);
		
        $this->dao->inserir($this->ordem_servico);
        header("Location:../view/listaOrdem_servico.php");
    }

    function excluir(){
        $this->dao->excluir($_REQUEST['id']);
        header("Location:../view/listaOrdem_servico.php");
    }

    function alterar(){
        $this->ordem_servico->setDescricao_problema($_POST['descricao_problema']);
		$this->ordem_servico->setData_abertura($_POST['data_abertura']);
		$this->ordem_servico->setPrazo_estimado($_POST['prazo_estimado']);
		$this->ordem_servico->setStatus($_POST['status']);
		$this->ordem_servico->setId_cliente($_POST['id_cliente']);
		$this->ordem_servico->setId_tipo_servico($_POST['id_tipo_servico']);
		
        $this->dao->alterar($this->ordem_servico, $_REQUEST['id']); 
        header("Location:../view/listaOrdem_servico.php");
    }

}
new Ordem_servicoControl();
?>