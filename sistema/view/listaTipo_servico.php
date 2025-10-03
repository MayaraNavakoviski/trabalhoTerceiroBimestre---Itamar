<!DOCTYPE html>
<html lang="pt-br">
<head> 
    <title>Lista de Tipo_servico</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="lista-page">
    <div class="tabela-container">
        <h2>Lista de Tipo_servico</h2>
        <a href="../view/tipo_servico.php" class="btn">+ Novo Tipo_servico</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                        echo "<th>Id</th>";
echo "<th>Nome</th>";

                        echo "<th>Ações</th>";
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once("../dao/tipo_servicoDao.php");
                    $dao = new Tipo_servicoDAO();
                    $dados = $dao->listaGeral();
                    
                    if (!empty($dados)):
                        foreach($dados as $dado) {
                            echo "<tr>";
                                echo "<td>{$dado['id']}</td>";
echo "<td>{$dado['nome']}</td>";

                            echo "<td class=\"acoes\">";
                            echo "<a href='../view/tipo_servico.php?id={$dado['id']}' class='btn-acao btn-editar'>Alterar</a>";
                            echo "<a href='../control/tipo_servicoControl.php?id={$dado['id']}&a=2' 
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