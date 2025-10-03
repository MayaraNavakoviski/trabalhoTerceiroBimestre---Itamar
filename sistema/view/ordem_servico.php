<?php
    require_once('../dao/ordem_servicoDao.php');
    require_once('../dao/ClienteDao.php');
	$clientes = (new ClienteDao())->buscarTodos();
	require_once('../dao/Tipo_servicoDao.php');
	$tipo_servicos = (new Tipo_servicoDao())->buscarTodos();
	

    $dados=null;
    if(isset($_GET['id']))
        $dados=(new Ordem_servicoDao())->buscaPorId($_GET['id']);

    $acao=$dados? 3:1; 
    $nome = $acao == 1 ? "Cadastrar" : "Alterar";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?= $nome ?> de Ordem_servico</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="form-page">
        <div class="container form-cadastro">
            <form id="form-ordem_servico" class="form-moderno" action="../control/ordem_servicoControl.php?a=<?php echo $acao ?><?php if(isset($_GET['id'])) {echo '&id='.$_GET['id'];} ?>" method="post">
                <h2><?= $nome ?> de Ordem_servico</h2>
                
<input type='hidden' id='id' name='id' value='<?php echo $dados?$dados['id']:''; ?>'>

<div class="campo-grupo">
<label for='descricao_problema'>Descricao problema</label>
<textarea id='descricao_problema' name='descricao_problema' required><?php echo $dados?$dados['descricao_problema']:''; ?></textarea>
 </div>

<div class="campo-grupo">
<label for='data_abertura'>Data abertura</label>
<input type='date' id='data_abertura' name='data_abertura' value='<?php echo $dados?$dados['data_abertura']:''; ?>' required>
 </div>

<div class="campo-grupo">
<label for='prazo_estimado'>Prazo estimado</label>
<input type='date' id='prazo_estimado' name='prazo_estimado' value='<?php echo $dados?$dados['prazo_estimado']:''; ?>' required>
 </div>

<div class="campo-grupo">
<label for='status'>Status</label>
<input type='text' id='status' name='status' value='<?php echo $dados?$dados['status']:''; ?>' required>
 </div>

<div class="campo-grupo">
<label for='id_cliente'>Id cliente</label>
<select id='id_cliente' name='id_cliente' required>
<option value=''>-- Selecione --</option>
<?php foreach($clientes as $d) { ?>
<option value="<?= $d['id'] ?>" <?= (isset($dados) && $dados['id_cliente'] == $d['id']) ? 'selected' : '' ?>>
<?= $d['nome'] ?>
</option>
<?php } ?>
</select>
</div>

<div class="campo-grupo">
<label for='id_tipo_servico'>Id tipo servico</label>
<select id='id_tipo_servico' name='id_tipo_servico' required>
<option value=''>-- Selecione --</option>
<?php foreach($tipo_servicos as $d) { ?>
<option value="<?= $d['id'] ?>" <?= (isset($dados) && $dados['id_tipo_servico'] == $d['id']) ? 'selected' : '' ?>>
<?= $d['nome'] ?>
</option>
<?php } ?>
</select>
</div>

                <button type="submit" class="btn-submit"><?= $acao == 1 ? 'Cadastrar' : 'Atualizar' ?></button>
            </form>
            <div class="voltar-lista">
                 <a href="listaOrdem_servico.php">Voltar para a lista</a>
            </div>
        </div>
    </body>
</html>