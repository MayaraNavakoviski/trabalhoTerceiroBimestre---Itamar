<?php
    require_once('../dao/tipo_servicoDao.php');
    

    $dados=null;
    if(isset($_GET['id']))
        $dados=(new Tipo_servicoDao())->buscaPorId($_GET['id']);

    $acao=$dados? 3:1; 
    $nome = $acao == 1 ? "Cadastrar" : "Alterar";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?= $nome ?> de Tipo_servico</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="form-page">
        <div class="container form-cadastro">
            <form id="form-tipo_servico" class="form-moderno" action="../control/tipo_servicoControl.php?a=<?php echo $acao ?><?php if(isset($_GET['id'])) {echo '&id='.$_GET['id'];} ?>" method="post">
                <h2><?= $nome ?> de Tipo_servico</h2>
                
<input type='hidden' id='id' name='id' value='<?php echo $dados?$dados['id']:''; ?>'>

<div class="campo-grupo">
<label for='nome'>Nome</label>
<input type='text' id='nome' name='nome' value='<?php echo $dados?$dados['nome']:''; ?>' required>
 </div>

                <button type="submit" class="btn-submit"><?= $acao == 1 ? 'Cadastrar' : 'Atualizar' ?></button>
            </form>
            <div class="voltar-lista">
                 <a href="listaTipo_servico.php">Voltar para a lista</a>
            </div>
        </div>
    </body>
</html>