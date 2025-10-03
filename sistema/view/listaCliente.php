<!DOCTYPE html>
<html lang="pt-br">
<head> 
    <title>Lista de Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="lista-page">
    <div class="tabela-container">
        <h2>Lista de Cliente</h2>
        <a href="../view/cliente.php" class="btn">+ Novo Cliente</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                        echo "<th>Id</th>";
echo "<th>Nome</th>";
echo "<th>Telefone</th>";
echo "<th>Email</th>";

                        echo "<th>Ações</th>";
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once("../dao/clienteDao.php");
                    $dao = new ClienteDAO();
                    $dados = $dao->listaGeral();
                    
                    if (!empty($dados)):
                        foreach($dados as $dado) {
                            echo "<tr>";
                                echo "<td>{$dado['id']}</td>";
echo "<td>{$dado['nome']}</td>";
echo "<td>{$dado['telefone']}</td>";
echo "<td>{$dado['email']}</td>";

                            echo "<td class=\"acoes\">";
                            echo "<a href='../view/cliente.php?id={$dado['id']}' class='btn-acao btn-editar'>Alterar</a>";
                            echo "<a href='../control/clienteControl.php?id={$dado['id']}&a=2' 
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