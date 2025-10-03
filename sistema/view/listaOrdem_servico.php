<!DOCTYPE html>
<html lang="pt-br">
<head> 
    <title>Lista de Ordem_servico</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="lista-page">
    <div class="tabela-container">
        <h2>Lista de Ordem_servico</h2>
        <a href="../view/ordem_servico.php" class="btn">+ Novo Ordem_servico</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                        echo "<th>Id</th>";
echo "<th>Descricao problema</th>";
echo "<th>Data abertura</th>";
echo "<th>Prazo estimado</th>";
echo "<th>Status</th>";
echo "<th>Id cliente</th>";
echo "<th>Id tipo servico</th>";

                        echo "<th>Ações</th>";
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once("../dao/ordem_servicoDao.php");
                    $dao = new Ordem_servicoDAO();
                    $dados = $dao->listaGeral();
                    
                    if (!empty($dados)):
                        foreach($dados as $dado) {
                            echo "<tr>";
                                echo "<td>{$dado['id']}</td>";
echo "<td>{$dado['descricao_problema']}</td>";
echo "<td>{$dado['data_abertura']}</td>";
echo "<td>{$dado['prazo_estimado']}</td>";
echo "<td>{$dado['status']}</td>";
echo "<td>{$dado['id_cliente']}</td>";
echo "<td>{$dado['id_tipo_servico']}</td>";

                            echo "<td class=\"acoes\">";
                            echo "<a href='../view/ordem_servico.php?id={$dado['id']}' class='btn-acao btn-editar'>Alterar</a>";
                            echo "<a href='../control/ordem_servicoControl.php?id={$dado['id']}&a=2' 
                                 class='btn-acao btn-excluir'
                                 onclick='return confirm(\"Tem certeza que quer excluir esse campo? Essa ação não tem volta!\")'>
                                 Excluir</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    else:
                        echo "<tr><td colspan=\"" . (count($atributos) + 1) . "\" class=\"sem-registros\">Nenhum registro encontrado.</td></tr>";
                    endif;
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>