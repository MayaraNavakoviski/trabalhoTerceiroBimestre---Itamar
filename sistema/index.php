<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mayara - Menu Principal</title>
    <link rel="stylesheet" href="./css/style.css"> 
</head>
<body class="sistema-principal">
    <div class="cabecalho">
        <div class="conteudo-cabecalho">
            <div class="logotipo">Mayara</div>
            
            <nav class="barra-navegacao">
                <ul class="menu-navegacao">
                    <li class="item-navegacao">
                        <a href="#" class="link-navegacao">Cadastros</a>
                        <div class="menu-suspenso">
                            <a href='./view/cliente.php' class='link-menu-suspenso' target='iframe' >Cadastro de Cliente</a>
<a href='./view/ordem_servico.php' class='link-menu-suspenso' target='iframe' >Cadastro de Ordem_servico</a>
<a href='./view/tipo_servico.php' class='link-menu-suspenso' target='iframe' >Cadastro de Tipo_servico</a>

                        </div>
                    </li>
                    
                    <li class="item-navegacao">
                        <a href="#" class="link-navegacao">Relatórios</a>
                        <div class="menu-suspenso">
                            <a href='./view/listaCliente.php' class='link-menu-suspenso' target='iframe'>Lista de Cliente</a>
<a href='./view/listaOrdem_servico.php' class='link-menu-suspenso' target='iframe'>Lista de Ordem_servico</a>
<a href='./view/listaTipo_servico.php' class='link-menu-suspenso' target='iframe'>Lista de Tipo_servico</a>

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