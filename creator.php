<?php
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class Creator
{
    private $con;
    private $servidor;
    private $banco;
    private $usuario;
    private $senha;
    private $tabelas;
    private $sistema;

    function __construct()
    {
        if (isset($_GET['id'])) {
            $this->buscaBancodeDados();
        } else {
            if (!isset($_POST["sistema"], $_REQUEST["servidor"], $_REQUEST["usuario"], $_REQUEST["senha"], $_POST["banco"])) {
                header("Location:index.php?msg=0");
                exit();
            }

            $this->sistema = $_POST["sistema"];
            $this->criaDiretorios();
            $this->conectar(1);
            $this->buscaTabelas();
            
            $this->ClasseConexao();
            $this->ClassesModel();
            $this->ClassesDao();
            $this->ClassesControl();
            $this->classesView();
            $this->home();

            $this->compactar();
        }
    }

    function criaDiretorios()
    {
        $dirs = [
            "sistema",
            "sistema/model",
            "sistema/control",
            "sistema/view",
            "sistema/dao",
            "sistema/css"
        ];
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    header("Location:index.php?msg=0");
                    exit();
                }
            }
        }
        
        if (file_exists('style.css')) {
            copy('style.css', 'sistema/css/style.css');
        }
    } 

    function conectar($id)
    {
        $this->servidor = $_REQUEST["servidor"];
        $this->usuario = $_REQUEST["usuario"];
        $this->senha = $_REQUEST["senha"];
        if ($id == 1) {
            $this->banco = $_POST["banco"];
        } else {
            $this->banco = "mysql";
        }
        try {
            $this->con = new PDO(
                "mysql:host=" . $this->servidor . ";dbname=" . $this->banco,
                $this->usuario,
                $this->senha,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            header("Location:index.php?msg=1");
            exit();
        }
    } 

    function buscaBancodeDados()
    {
        try {
            $this->conectar(0);
            $sql = "SHOW databases";
            $query = $this->con->query($sql);
            $databases = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($databases as $database) {
                echo "<option>" . $database["Database"] . "</option>";
            }
            $this->con = null;
        } catch (Exception $e) {
            header("Location:index.php?msg=3");
            exit();
        }
    } 

    function buscaTabelas()
    {
        try {
            $sql = "SHOW TABLES";
            $query = $this->con->query($sql);
            $this->tabelas = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            header("Location:index.php?msg=3");
            exit();
        }
    } 

    function buscaAtributos($nomeTabela)
    {
        $sql = "show columns from " . $nomeTabela;
        $atributos = $this->con->query($sql)->fetchAll(PDO::FETCH_OBJ);
        return $atributos;
    } 

    function ClassesModel()
    {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $atributos = $this->buscaAtributos($nomeTabela);
            $nomeAtributos = "";
            $geters_seters = "";
            foreach ($atributos as $atributo) {
                $atributo = $atributo->Field;
                $nomeAtributos .= "\tprivate \${$atributo};\n";
                $metodo = ucfirst($atributo);
                $geters_seters .= "\tfunction get" . $metodo . "(){\n";
                $geters_seters .= "\t\treturn \$this->{$atributo};\n\t}\n";
                $geters_seters .= "\tfunction set" . $metodo . "(\${$atributo}){\n";
                $geters_seters .= "\t\t\$this->{$atributo}=\${$atributo};\n\t}\n";
            }
            $nomeClasse = ucfirst($nomeTabela);
            $conteudo = <<<EOT
<?php
class {$nomeClasse} {
{$nomeAtributos}
{$geters_seters}
}
?>
EOT;
            file_put_contents("sistema/model/{$nomeTabela}.php", $conteudo);
        }
    } 

    function ClasseConexao()
    {
        $conteudo = <<<EOT
<?php
class Conexao {
    private \$server;
    private \$banco;
    private \$usuario;
    private \$senha;
    
    function __construct() {
        \$this->server = '{$this->servidor}';
        \$this->banco = '{$this->banco}';
        \$this->usuario = '{$this->usuario}';
        \$this->senha = '{$this->senha}';
    }
    
    function conectar() {
        try {
            \$conn = new PDO(
                "mysql:host=" . \$this->server . ";dbname=" . \$this->banco,
                \$this->usuario,
                \$this->senha,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return \$conn;
        } catch (Exception \$e) {
            echo "Erro ao conectar com o Banco de dados: " . \$e->getMessage();
            return null;
        }
    }
}
?>
EOT;
        file_put_contents("sistema/model/conexao.php", $conteudo);
    }

    function ClassesControl()
    {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $atributos = $this->buscaAtributos($nomeTabela);
            $nomeClasse = ucfirst($nomeTabela);
            $posts = "";
            
            $foreign_keys_load = "";
            
            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI")
                    continue;

                $atributo_nome = $atributo->Field;
                
                if (strpos($atributo_nome, 'id_') === 0 && $atributo_nome != 'id') {
                    $tabela_fk = str_replace('id_', '', $atributo_nome);
                    $nome_dao_fk = ucfirst($tabela_fk) . "Dao";
                    $nome_var_fk = $tabela_fk . "s";
                    $foreign_keys_load .= "require_once('../dao/{$nome_dao_fk}.php');\n\t\t";
                    $foreign_keys_load .= "\${$nome_var_fk} = (new {$nome_dao_fk}())->listaGeral();\n\t\t";
                }
                
                $posts .= "\$this->{$nomeTabela}->set" . ucFirst($atributo_nome) .
                    "(\$_POST['{$atributo_nome}']);\n\t\t";
            }
            
            $busca_alterar = "";
            
            $conteudo = <<<EOT
<?php
require_once("../model/{$nomeTabela}.php");
require_once("../dao/{$nomeTabela}Dao.php");

class {$nomeClasse}Control {
    private \${$nomeTabela};
    private \$acao;
    private \$dao;

    public function __construct(){
        \$this->{$nomeTabela}=new {$nomeClasse}();
        \$this->dao=new {$nomeClasse}Dao();
        \$this->acao=\$_REQUEST["a"] ?? null;
        \$this->verificaAcao(); 
    }
    
    function verificaAcao(){
        if(\$this->acao == 1){
            \$this->inserir();
        } else if (\$this->acao == 2){
            \$this->excluir();
        } else if (\$this->acao == 3){
            \$this->alterar();
        } else {
        }
    }
 
    function inserir(){
        {$posts}
        \$this->dao->inserir(\$this->{$nomeTabela});
        header("Location:../view/lista{$nomeClasse}.php");
    }

    function excluir(){
        \$this->dao->excluir(\$_REQUEST['id']);
        header("Location:../view/lista{$nomeClasse}.php");
    }

    function alterar(){
        {$posts}
        \$this->dao->alterar(\$this->{$nomeTabela}, \$_REQUEST['id']); 
        header("Location:../view/lista{$nomeClasse}.php");
    }

}
new {$nomeClasse}Control();
?>
EOT;
            file_put_contents("sistema/control/{$nomeTabela}Control.php", $conteudo);
        }
    } 

    function compactar()
{
    $folderToZip = 'sistema';
    $outputZip = 'sistema.zip';
    $zip = new ZipArchive();

    if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return false;
    }

    $folderPath = realpath($folderToZip);
    if (!is_dir($folderPath)) {
        return false;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = 'sistema/' . substr($filePath, strlen($folderPath) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="sistema.zip"');
    header('Content-Length: ' . filesize($outputZip));
    readfile($outputZip);
    exit;
}


    function ClassesDao()
    {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeClasse = ucfirst($nomeTabela);
            $atributos = $this->buscaAtributos($nomeTabela);
            $id = "";
            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI")
                    $id = $atributo->Field;
            }
            $atributos = array_map(function ($obj) {
                if ($obj->Key == "PRI") {
                    return null;
                }
                return $obj->Field;
            }, $atributos);

            $atributos = array_filter($atributos, function ($valor) {
                return !is_null($valor);
            });
            $sqlCols = implode(', ', $atributos);
            $placeholders = implode(', ', array_fill(0, count($atributos), '?'));
            $sqlUpdate = implode('=?, ', $atributos) . '=?';
            $vetAtributos = [];
            $AtributosMetodos = "";

            foreach ($atributos as $atributo) {

                $atr = ucfirst($atributo);
                array_push($vetAtributos, "\$obj->get{$atr}()");
            }
            $atributosOk = implode(",", $vetAtributos);
            
            $colunas_busca = "";
            foreach($this->buscaAtributos($nomeTabela) as $attr) {
                $colunas_busca .= "\$obj->set" . ucfirst($attr->Field) . "(\$dados['{$attr->Field}']);\n\t\t";
            }
            
            $conteudo = <<<EOT
<?php
require_once("../model/conexao.php");
require_once("../model/{$nomeTabela}.php");

class {$nomeClasse}Dao {
    private \$con;
    public function __construct(){
        \$this->con=(new Conexao())->conectar();
    }

    function inserir(\$obj) {
        \$sql = "INSERT INTO {$nomeTabela} ({$sqlCols}) VALUES ({$placeholders})";
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->execute([{$atributosOk}]);
    }

    function alterar(\$obj, \$idValue){
        \$sql = "UPDATE {$nomeTabela} SET {$sqlUpdate} WHERE {$id}=?";
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->execute([{$atributosOk}, \$idValue]);
    }

    function listaGeral(){
        \$sql = "select * from {$nomeTabela}";
        \$query = \$this->con->query(\$sql);
        \$dados = \$query->fetchAll(PDO::FETCH_ASSOC);
        return \$dados;
    }
    
    function buscarTodos(){
        return \$this->listaGeral();
    }

    function excluir(\$id){
        \$sql = "delete from {$nomeTabela} where {$id}=:id";
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->bindParam(':id', \$id, PDO::PARAM_INT);
        \$stmt->execute();
    }

    function buscaPorId(\$id){
        \$sql = "select * from {$nomeTabela} where {$id}=:id"; 
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->bindParam(':id', \$id, PDO::PARAM_INT);
        \$stmt->execute();
        \$dados = \$stmt->fetch(PDO::FETCH_ASSOC);
        return \$dados;
    }

}
?>
EOT;
            file_put_contents("sistema/dao/{$nomeTabela}Dao.php", $conteudo);
        }
    } 

    function verificacao($tipo, $campo)
    {
        if ($tipo->Key == "PRI") {
            return ["type" => "hidden"];
        }
        
        if (strpos($campo, 'id_') === 0 && $campo !== 'id') {
            $tabela_fk = str_replace('id_', '', $campo);
            return [
                "type" => "select", 
                "data_source" => "{$tabela_fk}s"
            ];
        }

        $mysql_type = strtolower($tipo->Type);
        
        if (strpos($mysql_type, 'date') !== false) {
            return ["type" => "date"];
        }
        if (strpos($mysql_type, 'int') !== false || strpos($mysql_type, 'float') !== false || strpos($mysql_type, 'decimal') !== false) {
            return ["type" => "number"];
        }
        if (strpos($mysql_type, 'text') !== false) {
            return ["type" => "textarea"];
        }
        
        return ["type" => "text"];
    }
    
    function classesView()
    {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);
            $atributos = $this->buscaAtributos($nomeTabela);
            $formCampos = "";
            $controlIncludes = "";
            
            foreach ($atributos as $atributo) {
                $campo = $atributo->Field;
                $labelTexto = ucfirst(str_replace('_', ' ', $campo));
                $config = $this->verificacao($atributo, $campo);
                $tipo = $config['type'];

                if ($tipo == "hidden") {
                    $formCampos .= "\n<input type='hidden' id='{$campo}' name='{$campo}' value='<?php echo \$dados?\$dados['{$campo}']:''; ?>'>\n";
                } elseif ($tipo == "select") {
                    $dataSource = $config['data_source'];
                    $tabelaFK = str_replace('id_', '', $campo);
                    $nomeDaoFK = ucfirst($tabelaFK) . "Dao";
                    $campoExibicao = ($tabelaFK == 'cliente') ? 'nome' : 'nome';

                    $controlIncludes .= "require_once('../dao/{$nomeDaoFK}.php');\n\t";
                    $controlIncludes .= "\${$dataSource} = (new {$nomeDaoFK}())->buscarTodos();\n\t";
                    
                    $formCampos .= "\n<div class=\"campo-grupo\">\n";
                    $formCampos .= "<label for='{$campo}'>{$labelTexto}</label>\n";
                    $formCampos .= "<select id='{$campo}' name='{$campo}' required>\n";
                    $formCampos .= "<option value=''>-- Selecione --</option>\n";
                    $formCampos .= "<?php foreach(\${$dataSource} as \$d) { ?>\n";
                    $formCampos .= "<option value=\"<?= \$d['id'] ?>\" <?= (isset(\$dados) && \$dados['{$campo}'] == \$d['id']) ? 'selected' : '' ?>>\n";
                    $formCampos .= "<?= \$d['{$campoExibicao}'] ?>\n";
                    $formCampos .= "</option>\n";
                    $formCampos .= "<?php } ?>\n";
                    $formCampos .= "</select>\n";
                    $formCampos .= "</div>\n";
                } elseif ($tipo == "textarea") {
                    $formCampos .= "\n<div class=\"campo-grupo\">\n";
                    $formCampos .= "<label for='{$campo}'>{$labelTexto}</label>\n";
                    $formCampos .= "<textarea id='{$campo}' name='{$campo}' required><?php echo \$dados?\$dados['{$campo}']:''; ?></textarea>\n";
                    $formCampos .= " </div>\n";
                } else {
                    $required = "required";
                    $input_type = $tipo;
                    
                    $formCampos .= "\n<div class=\"campo-grupo\">\n";
                    $formCampos .= "<label for='{$campo}'>{$labelTexto}</label>\n";
                    $formCampos .= "<input type='{$input_type}' id='{$campo}' name='{$campo}' value='<?php echo \$dados?\$dados['{$campo}']:''; ?>' {$required}>\n";
                    $formCampos .= " </div>\n";
                }
            }
            
            $conteudo = <<<HTML
<?php
    require_once('../dao/{$nomeTabela}Dao.php');
    {$controlIncludes}

    \$dados=null;
    if(isset(\$_GET['id']))
        \$dados=(new {$nomeTabelaUC}Dao())->buscaPorId(\$_GET['id']);

    \$acao=\$dados? 3:1; 
    \$nome = \$acao == 1 ? "Cadastrar" : "Alterar";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?= \$nome ?> de {$nomeTabelaUC}</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="form-page">
        <div class="container form-cadastro">
            <form id="form-{$nomeTabela}" class="form-moderno" action="../control/{$nomeTabela}Control.php?a=<?php echo \$acao ?><?php if(isset(\$_GET['id'])) {echo '&id='.\$_GET['id'];} ?>" method="post">
                <h2><?= \$nome ?> de {$nomeTabelaUC}</h2>
                {$formCampos}
                <button type="submit" class="btn-submit"><?= \$acao == 1 ? 'Cadastrar' : 'Atualizar' ?></button>
            </form>
            <div class="voltar-lista">
                 <a href="lista{$nomeTabelaUC}.php">Voltar para a lista</a>
            </div>
        </div>
    </body>
</html>
HTML;
            file_put_contents("sistema/view/{$nomeTabela}.php", $conteudo);
        }

        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);
            $atributos = $this->buscaAtributos($nomeTabela);
            $attr = "";
            $id = "";
            $campoId = "";
            
            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI") {
                    $campoId = $atributo->Field;
                    $id = "{\$dado['{$atributo->Field}']}";
                }

                // CORREÇÃO APLICADA: Removida a indentação excessiva para evitar o Parse Error
                $attr .= "echo \"<td>{\$dado['{$atributo->Field}']}</td>\";\n";
            }
            
            $cabecalhos = "";
            foreach ($atributos as $atributo) {
                $labelCabecalho = ucfirst(str_replace('_', ' ', $atributo->Field));
                // CORREÇÃO APLICADA: Removida a indentação excessiva para evitar o Parse Error
                $cabecalhos .= "echo \"<th>{$labelCabecalho}</th>\";\n";
            }

            $conteudo = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head> 
    <title>Lista de {$nomeTabelaUC}</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="lista-page">
    <div class="tabela-container">
        <h2>Lista de {$nomeTabelaUC}</h2>
        <a href="../view/{$nomeTabela}.php" class="btn">+ Novo {$nomeTabelaUC}</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                        {$cabecalhos}
                        echo "<th>Ações</th>";
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once("../dao/{$nomeTabela}Dao.php");
                    \$dao = new {$nomeTabelaUC}DAO();
                    \$dados = \$dao->listaGeral();
                    
                    if (!empty(\$dados)):
                        foreach(\$dados as \$dado) {
                            echo "<tr>";
                                {$attr}
                            echo "<td class=\"acoes\">";
                            echo "<a href='../view/{$nomeTabela}.php?id={$id}' class='btn-acao btn-editar'>Alterar</a>";
                            echo "<a href='../control/{$nomeTabela}Control.php?id={$id}&a=2' 
                                 class='btn-acao btn-excluir'
                                 onclick='return confirm(\"Tem certeza que quer excluir esse campo? Essa ação não tem volta!\")'>
                                 Excluir</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    else:
                        echo "<tr><td colspan=\"" . (count(\$atributos) + 1) . "\" class=\"sem-registros\">Nenhum registro encontrado.</td></tr>";
                    endif;
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
HTML;
            file_put_contents("sistema/view/lista{$nomeTabelaUC}.php", $conteudo);
        }
    }

    function home()
    {
        $listagem = "";
        $forms = "";
        $dynamic_links = "";

        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);

            $listagem .= "<a href='./view/lista{$nomeTabelaUC}.php' class='link-menu-suspenso' target='iframe'>Lista de {$nomeTabelaUC}</a>\n";
            $forms .= "<a href='./view/{$nomeTabela}.php' class='link-menu-suspenso' target='iframe' >Cadastro de {$nomeTabelaUC}</a>\n";

            $dynamic_links .= "
        <div class=\"cartao-entidade\">
            <h3>{$nomeTabelaUC}</h3>
            <div class=\"acoes-cartao\">
                <a href='./view/{$nomeTabela}.php' target='iframe' class='btn-acao btn-cadastro'>Cadastrar</a>
                <a href='./view/lista{$nomeTabelaUC}.php' target='iframe' class='btn-acao btn-lista'>Listar</a>
            </div>
        </div>";
        }

        $conteudo = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->sistema} - Menu Principal</title>
    <link rel="stylesheet" href="./css/style.css"> 
</head>
<body class="sistema-principal">
    <div class="cabecalho">
        <div class="conteudo-cabecalho">
            <div class="logotipo">{$this->sistema}</div>
            
            <nav class="barra-navegacao">
                <ul class="menu-navegacao">
                    <li class="item-navegacao">
                        <a href="#" class="link-navegacao">Cadastros</a>
                        <div class="menu-suspenso">
                            {$forms}
                        </div>
                    </li>
                    
                    <li class="item-navegacao">
                        <a href="#" class="link-navegacao">Relatórios</a>
                        <div class="menu-suspenso">
                            {$listagem}
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    
    <div class="conteudo-principal">
        <div class="envolvedor-conteudo">
            <div class="container-iframe">
                <iframe id="contentFrame" name="iframe" src="inicio.html" seamless>
                    Seu navegador não suporta iframes.
                </iframe>
            </div>
        </div>
    </div>

</body>
</html>
HTML;
        file_put_contents("sistema/index.php", $conteudo);

        $paginaInicio = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Sistema</title>
    <link rel="stylesheet" href="./css/style.css"> 
</head>
<body class="pagina-inicial">
    <div class="container-boas-vindas">
        <div class="cartao-boas-vindas">
            <h1>Bem-vindo ao sistema {$this->sistema}!</h1>
            <p>Selecione uma entidade para começar a gerenciar seus dados:</p>
            <div class="lista-entidades">
                {$dynamic_links}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        file_put_contents("sistema/inicio.html", $paginaInicio);
    }
}
new Creator();