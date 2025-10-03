<?php
require_once("../model/tipo_servico.php");
require_once("../dao/tipo_servicoDao.php");

class Tipo_servicoControl {
    private $tipo_servico;
    private $acao;
    private $dao;

    public function __construct(){
        $this->tipo_servico=new Tipo_servico();
        $this->dao=new Tipo_servicoDao();
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
        $this->tipo_servico->setNome($_POST['nome']);
		
        $this->dao->inserir($this->tipo_servico);
        header("Location:../view/listaTipo_servico.php");
    }

    function excluir(){
        $this->dao->excluir($_REQUEST['id']);
        header("Location:../view/listaTipo_servico.php");
    }

    function alterar(){
        $this->tipo_servico->setNome($_POST['nome']);
		
        $this->dao->alterar($this->tipo_servico, $_REQUEST['id']); 
        header("Location:../view/listaTipo_servico.php");
    }

}
new Tipo_servicoControl();
?>