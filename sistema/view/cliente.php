<?php
    require_once('../dao/clienteDao.php');
    

    $dados=null;
    if(isset($_GET['id']))
        $dados=(new ClienteDao())->buscaPorId($_GET['id']);

    $acao=$dados? 3:1; 
    $nome = $acao == 1 ? "Cadastrar" : "Alterar";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?= $nome ?> de Cliente</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="form-page">
        <div class="container form-cadastro">
            <form id="form-cliente" class="form-moderno" action="../control/clienteControl.php?a=<?php echo $acao ?><?php if(isset($_GET['id'])) {echo '&id='.$_GET['id'];} ?>" method="post">
                <h2><?= $nome ?> de Cliente</h2>
                
<input type='hidden' id='id' name='id' value='<?php echo $dados?$dados['id']:''; ?>'>

<div class="campo-grupo">
<label for='nome'>Nome</label>
<input type='text' id='nome' name='nome' value='<?php echo $dados?$dados['nome']:''; ?>' required>
 </div>

<div class="campo-grupo">
<label for='telefone'>Telefone</label>
<input type='text' id='telefone' name='telefone' value='<?php echo $dados?$dados['telefone']:''; ?>' required>
 </div>

<div class="campo-grupo">
<label for='email'>Email</label>
<input type='text' id='email' name='email' value='<?php echo $dados?$dados['email']:''; ?>' required>
 </div>

                <button type="submit" class="btn-submit"><?= $acao == 1 ? 'Cadastrar' : 'Atualizar' ?></button>
            </form>
            <div class="voltar-lista">
                 <a href="listaCliente.php">Voltar para a lista</a>
            </div>
        </div>
    </body>
</html>