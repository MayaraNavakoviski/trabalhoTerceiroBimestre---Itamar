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
        if (isset($_GET['id']))
            $this->buscaBancodeDados();
        else {
            $this->sistema = $_POST["sistema"] ?? "EasyMVC System";
            $this->criaDiretorios();
            $this->conectar(1);
            $this->buscaTabelas();
            $this->ClassesModel();
            $this->ClasseConexao();
            $this->ClassesControl();
            $this->classesView();
            $this->home();
            $this->ClassesDao();
            $this->compactar();
            header("Location:index.php?msg=2");
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
                }
            }
        }
        copy('style.css', 'sistema/css/style.css');

        copy('style.css', 'sistema/style.css');
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
                $this->senha
            );
        } catch (Exception $e) {

            header("Location:index.php?msg=1");
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
            header("Location:index.php?msg=1");
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
                "mysql:host=" . \$this->server . ";dbname=" . \$this->banco,\$this->usuario,
                \$this->senha
            );
            return \$conn;
        } catch (Exception \$e) {
            echo "Erro ao conectar com o Banco de dados: " . \$e->getMessage();
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

            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI")
                    continue;
                $atributo = $atributo->Field;
                $posts .= "\$this->{$nomeTabela}->set" . ucFirst($atributo) .
                    "(\$_POST['{$atributo}']);\n\t\t";
            }

            $idSetter = "";
            $idField = "";
            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI") {
                    $idField = $atributo->Field;
                    $idSetter = "\$this->{$nomeTabela}->set" . ucFirst($idField) . "(\$_POST['{$idField}']);\n\t\t";
                    break;
                }
            }


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
      \$this->acao=\$_GET["a"];
      \$this->verificaAcao(); 
    }
    function verificaAcao(){
       switch(\$this->acao){
          case 1:
            \$this->inserir();
          break;
          case 2:
            \$this->excluir();
          break;
          case 3:
            \$this->alterar();
          break;
       }
    }
  
    function inserir(){
        {$posts}
        \$this->dao->inserir(\$this->{$nomeTabela});
    }
    function excluir(){
        \$this->dao->excluir(\$_REQUEST['id']);
    }
    function alterar(){
        {$posts}
        \$idValue = \$_REQUEST['id']; 
        if (\$idValue) {
             \$this->dao->alterar(\$this->{$nomeTabela}, \$idValue);
        } else {
             header("Location:../view/lista{$nomeClasse}.php?erro=id_ausente"); 
        }

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
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        return $zip->close();
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

            $atributosParaCrud = array_map(function ($obj) {
                if ($obj->Key == "PRI") {
                    return null;
                }
                return $obj->Field;
            }, $atributos);


            $atributosParaCrud = array_filter($atributosParaCrud, function ($valor) {
                return !is_null($valor);
            });

            $sqlCols = implode(', ', $atributosParaCrud);
            $placeholders = implode(', ', array_fill(0, count($atributosParaCrud), '?'));
            $sqlUpdate = implode('=?, ', $atributosParaCrud) . '=?';

            $vetAtributos = [];
            $AtributosMetodos = "";

            foreach ($atributosParaCrud as $atributo) {
                $atr = ucfirst($atributo);
                array_push($vetAtributos, "\${$atributo}");
                $AtributosMetodos .= "\${$atributo}=\$obj->get{$atr}();\n";
            }
            $atributosOk = implode(",", $vetAtributos);

            $conteudo = <<<EOT
<?php
require_once("../model/conexao.php");
class {$nomeClasse}Dao {
    private \$con;
    public function __construct(){
       \$this->con=(new Conexao())->conectar();
    }
function inserir(\$obj) {
    \$sql = "INSERT INTO {$nomeTabela} ({$sqlCols}) VALUES ({$placeholders})";
    \$stmt = \$this->con->prepare(\$sql);
    {$AtributosMetodos}
    \$stmt->execute([{$atributosOk}]);
    header("Location:../view/lista{$nomeClasse}.php");
}

function listaGeral(){
    \$sql = "select * from {$nomeTabela}";
    \$query = \$this->con->query(\$sql);
    \$dados = \$query->fetchAll(PDO::FETCH_ASSOC);
    return \$dados;
}

function excluir(\$id){
    \$sql = "delete from {$nomeTabela} where {$id}=\$id";
    \$query = \$this->con->query(\$sql);
    header("Location:../view/lista{$nomeClasse}.php");
}

function buscaPorId(\$id){
    \$sql = "select * from {$nomeTabela} where {$id} = :id";
    \$stmt = \$this->con->prepare(\$sql);
    \$stmt->bindParam(':id', \$id);
    \$stmt->execute();
    \$dados = \$stmt->fetch(PDO::FETCH_ASSOC);
    return \$dados;
}

function alterar(\$obj, \$idValue){
    \$sql = "UPDATE {$nomeTabela} SET {$sqlUpdate} WHERE {$id}=?";
    \$stmt = \$this->con->prepare(\$sql);
    {$AtributosMetodos}
    \$stmt->execute([{$atributosOk}, \$idValue]); 
    header("Location:../view/lista{$nomeClasse}.php");
}
}
?>
EOT;
            file_put_contents("sistema/dao/{$nomeTabela}Dao.php", $conteudo);
        }
    }


    function verificaTipo($tipo)
    {
        if ($tipo->Key == "PRI") {
            return "hidden";
        }

        $fieldType = strtolower($tipo->Type);

        if (strpos($fieldType, 'int') !== false || strpos($fieldType, 'decimal') !== false || strpos($fieldType, 'float') !== false) {
            return "number";
        }

        if (strpos($fieldType, 'date') !== false || strpos($fieldType, 'time') !== false) {
            return "date";
        }

        if (strpos($fieldType, 'password') !== false || strpos($tipo->Field, 'senha') !== false) {
            return "password";
        }

        return "text";
    }


    function classesView()
    {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);
            $atributos = $this->buscaAtributos($nomeTabela);
            $formCampos = "";
            foreach ($atributos as $atributo) {
                $tipo = $this->verificaTipo($atributo);
                $campo = $atributo->Field;
                $labelTexto = ucfirst(str_replace('_', ' ', $campo));

                if ($tipo == "hidden") {
                    $formCampos .= "\n<input type='hidden' id='{$campo}' name='{$campo}' value='<?php echo \$obj?\$obj['{$campo}']:''; ?>'>\n";
                } else {
                    $required = ($atributo->Null == "NO" && $tipo != "hidden") ? "required" : "";
                    $formCampos .= "\n<div class=\"campo-grupo\">\n";
                    $formCampos .= "<label for='{$campo}'>{$labelTexto}</label>\n";
                    $formCampos .= "<input type='{$tipo}' id='{$campo}' name='{$campo}' value='<?php echo \$obj?\$obj['{$campo}']:''; ?>' {$required}>\n";
                    $formCampos .= " </div>\n";
                }
            }
            $conteudo = <<<HTML
<?php
    require_once('../dao/{$nomeTabela}Dao.php');
    \$obj=null;
    if(isset(\$_GET['id']))
        \$obj=(new {$nomeTabela}Dao())->buscaPorId(\$_GET['id']);

    \$acao=\$obj? 3:1; 
    \$nome = \$acao == 1 ? "Cadastrar" : "Editar";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= \$nome ?> de {$nomeTabelaUC}</title>
        <link rel="stylesheet" href="../css/style.css"> 
    </head>
    <body class="form-page">
        <div class="container form-cadastro">
            <form id="form-{$nomeTabela}" class="form-moderno" action="../control/{$nomeTabela}Control.php?a=<?php echo \$acao ?><?php if(isset(\$_GET['id'])) {echo '&id='.\$_GET['id'];} ?>" method="post">
                <h2><?= \$nome ?> {$nomeTabelaUC}</h2>
                {$formCampos}
                <button type="submit" class="btn-submit"><?= \$acao == 1 ? 'Cadastrar' : 'Atualizar' ?></button>
                <p class="voltar-lista"><a href="lista{$nomeTabelaUC}.php" target="_top">Voltar para a Lista</a></p>
            </form>
        </div>
    </body>
</html>
HTML;
            file_put_contents("sistema/view/{$nomeTabela}.php", $conteudo);
        }
        //Listas
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);
            $atributos = $this->buscaAtributos($nomeTabela);
            $attr = "";
            $id = "";

            foreach ($atributos as $atributo) {
                if ($atributo->Key == "PRI")
                    $id = "{\$dado['{$atributo->Field}']}";

                $attr .= "echo \"<td>{\$dado['{$atributo->Field}']}</td>\";\n";
            }

            $cabecalhos = "";
            foreach ($atributos as $atributo) {
                $labelCabecalho = ucfirst(str_replace('_', ' ', $atributo->Field));
                $cabecalhos .= "    echo \"<th>{$labelCabecalho}</th>\";\n";
            }

            $conteudo = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lista de {$nomeTabelaUC}</title>
        <link rel="stylesheet" href="../css/style.css"> 
    </head>
    <body class="lista-page">
        <div class="tabela-container">
            <h2>Lista de {$nomeTabelaUC}</h2>
            <a href="{$nomeTabela}.php" class="btn btn-novo">+ Novo {$nomeTabelaUC}</a>
            
            <?php
            require_once("../dao/{$nomeTabela}Dao.php");
            \$dao = new {$nomeTabela}DAO();
            \$dados = \$dao->listaGeral();
            
            if (!empty(\$dados)) {
                echo "<table class='data-table'>";
                echo "<thead><tr>";
                {$cabecalhos}
                echo "<th>Ações</th>";
                echo "</tr></thead>";
                
                echo "<tbody>";
                foreach(\$dados as \$dado) {
                    echo "<tr>";
                        {$attr}
                    echo "<td class='actions'>";
                    echo "<a href='{$nomeTabela}.php?id={$id}' class='btn-acao btn-editar'>Editar</a>";
                    echo "<a href='../control/{$nomeTabela}Control.php?id={$id}&a=2' onclick='return confirm(\"Tem certeza que deseja excluir este registro?\")' class='btn-acao btn-excluir'>Excluir</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p class='no-records'>Nenhum registro de {$nomeTabelaUC} encontrado.</p>";
            }
            ?>
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
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeTabelaUC = ucfirst($nomeTabela);
            $listagem .= "<a href='./view/lista{$nomeTabelaUC}.php' class='dropdown-link' target='iframe'>Lista de {$nomeTabelaUC}</a>\n";
            $forms .= "<a href='./view/{$nomeTabela}.php' class='dropdown-link' target='iframe' >Cadastro de {$nomeTabelaUC}</a>\n";
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
    <div class="header">
        <div class="header-content">
            <div class="logo">{$this->sistema}</div>
            
            <nav class="navbar">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#" class="nav-link">Cadastros</a>
                        <div class="dropdown">
                            {$forms}
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a href="#" class="nav-link">Relatórios</a>
                        <div class="dropdown">
                            {$listagem}
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    
    <div class="main-content">
        <div class="content-wrapper">
            <div class="iframe-container">
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
<body class="inicio-page">
    <div class="welcome-container">
        <div class="welcome-card">
            <h1>Bem-vindo!</h1>
            <p>Esta é a área de conteúdo do sistema **{$this->sistema}**.</p>
            
        </div>
    </div>
</body>
</html>
HTML;
        file_put_contents("sistema/inicio.html", $paginaInicio);
    }
}
new Creator();
