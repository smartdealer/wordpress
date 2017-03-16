<?php

$NUSOAP_PATCH = 'nusoap/nusoap.php';
$WS_SDS_PATCH = 'https://' . get_option('sds_instancia') . '.smartdealer.com.br/webservice/core.php?wsdl';
$WS_SDS_USER = get_option('sds_usuario');
$WS_SDS_OWNER = get_option('sds_instancia');
$WS_SDS_KEY = get_option('sds_senha');
$PLUGIN_URL = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/plugins/sds-wp/';
$CACHE_LOCAL_PACH = $PLUGIN_URL . 'img-cache/';
$WS_SDS_ROWS_TOTAL = 0;

$WS_SDS_MODUS = 'novos';
$WS_SDS_REQUIRE = 'CarrosNovos';

// - - - - - - - - - - - - - - - - - - -
// Livre configura��o
// - - - - - - - - - - - - - - - - - - -

$WS_SDS_PAGE = 1;
$WS_SDS_PAGE_SIZE = 20;
$WS_SDS_PAGINATOR_MAX_LINKS = 12;
$WS_CODIGO_FILIAL = '';
$WS_CODIGO_ESTOQUE = '';

// - - - - - - - - - - - - - - - - - - -
// Inst�ncia do WS_SDS
// - - - - - - - - - - - - - - - - - - -

require($NUSOAP_PATCH);
$WS_SDS = new nusoap_client($WS_SDS_PATCH, true);

$WS_SDS_ERROR = $WS_SDS->getError();

if ($WS_SDS_ERROR):
    echo '<h2>Erro na constru��o: </h2><pre>' . $WS_SDS_ERROR . '</pre>';
    exit;
endif;


$WS_SDS->setCredentials($WS_SDS_USER, $WS_SDS_KEY);

// - - - - - - - - - - - - - - - - - - -
// Fun�oes de apoio
// - - - - - - - - - - - - - - - - - - -

function MakePager() {
    $get = '?';
    $pages = array();
    global $WS_SDS_PAGES_TOTAL, $WS_SDS_PAGE, $WS_SDS_PAGINATOR_MAX_LINKS;

    if (isset($_GET['pp']))
        unset($_GET['pp']);

    foreach (array_keys($_GET) as $param)
        $get .= $param . "=" . urlencode($_GET[$param]) . '&';

    if ($WS_SDS_PAGE != 1):
        $pages[] = array(
            'link' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . $get . 'pp=1',
            'text' => ' &laquo; Primeira',
            'atual' => '0'
        );

        $pages[] = array(
            'link' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . $get . 'pp=' . ($WS_SDS_PAGE - 1),
            'text' => ' &laquo; Anterior',
            'atual' => '0'
        );
    endif;

    for ($k = 1; $k <= $WS_SDS_PAGES_TOTAL; ++$k):
        $atual = 0;
        if ($k == $WS_SDS_PAGE)
            $atual = 1;

        $INT = ceil($WS_SDS_PAGINATOR_MAX_LINKS / 2) - 1;
        $menor_pagina = $WS_SDS_PAGE - $INT;
        $maior_pagina = $WS_SDS_PAGE + $INT;

        if (($k > $menor_pagina) and ( $k < $maior_pagina))
            $pages[] = array(
                'link' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . $get . 'pp=' . $k,
                'text' => $k,
                'atual' => $atual
            );
    endfor;

    if ($WS_SDS_PAGES_TOTAL != $WS_SDS_PAGE):
        $pages[] = array(
            'link' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . $get . 'pp=' . ($WS_SDS_PAGE + 1),
            'text' => 'Proximo &raquo;',
            'atual' => '0'
        );

        $pages[] = array(
            'link' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . $get . 'pp=' . $WS_SDS_PAGES_TOTAL,
            'text' => 'Ultima &raquo;',
            'atual' => '0'
        );
    endif;


    return $pages;
}

// - - - - - - - - - - - - - - - - - - -
// Recebe parametro de PP via $_GET
// - - - - - - - - - - - - - - - - - - -

if (isset($_GET['pp']))
    $WS_SDS_PAGE = addslashes($_GET['pp']);

// - - - - - - - - - - - - - - - - - - -
// Processa FORM 
// - - - - - - - - - - - - - - - - - - -

$in = array(
    'pp' => $WS_SDS_PAGE, // -> (integer) N�mero da p�gina requisitada
    'qtd_por_pp' => $WS_SDS_PAGE_SIZE   // -> (integer) Quantidade de registros por p�gina
);

if ($WS_CODIGO_FILIAL != '')
    $in['filial'] = $WS_CODIGO_FILIAL;

if ($WS_CODIGO_ESTOQUE != '')
    $in['estoque'] = $WS_CODIGO_ESTOQUE;

if (isset($_GET['buscar_x'])) {

    // 'marca'       => '', // -> (string) Marca solicitadao ou n�o passar o par�metro em caso de todas
    // 'modelo'      => '', // -> (string) Termo de busca para modelo ou n�o passar o par�metro em caso de todas
    // 'ano_min'     => '44310.00', // -> (string) Piso do ano solicitadao ou n�o passar o par�metro em caso de todas
    // 'ano_max'     => '', // -> (string) Teto do ano solicitadao ou n�o passar o par�metro em caso de todas
    // 'preco_min'   => '40000', // -> (string) Piso do valor ou n�o passar o par�metro em caso de todas
    // 'preco_max'   => '60000', // -> (string) Teto do valor ou n�o passar o par�metro em caso de todas
    // 'campo_ordenador' => 'valor', // -> (string) ['modelo', 'ano', 'valor']
    // 'sentido_ordenacao' => 'desc', // -> (string) ['asc', 'desc']        



    if ($_GET['familia'] != '')
        $in['familia'] = urldecode($_GET['familia']);

    if ($_GET['cor'] != '')
        $in['cor'] = urldecode($_GET['cor']);

    if ($_GET['combustivel'] != '')
        $in['combustivel'] = urldecode($_GET['combustivel']);
}





// - - - - - - - - - - - - - - - - - - -
// Processa EuQuero!
// - - - - - - - - - - - - - - - - - - -

$contato['show'] = false;

if (isset($_POST['querer_x'])):

    $err_form = 0;

    if (($_POST['mail'] == '') and ( $_POST['tel'] == '')) {
        $contato['msn'] = 'Aten��o, sua mensagem n�o pode ser enviada!! <br/ ><br/ > <strong>Por favor preencha seu e-mail ou seu telefone!</strong>';
        $contato['class'] = 'ReturnMessageErro';
        $contato['show'] = true;
        ++$err_form;
    }

    if ($_POST['nome'] == '') {
        $contato['msn'] = 'Aten��o, sua mensagem n�o pode ser enviada!! <br/ ><br/ > <strong>Por favor preencha o seu nome!</strong>';
        $contato['class'] = 'ReturnMessageErro';
        $contato['show'] = true;
        ++$err_form;
    }


    if ($err_form == 0):

        $mensagem = array(
            'carro_id' => $_POST['id'],
            'nome' => $_POST['nome'],
            'telefone' => $_POST['tel'],
            'e_mail' => $_POST['mail'],
            'mensagem' => $_POST['mensagem'],
            'modal' => $WS_SDS_MODUS
        );

        $res = $WS_SDS->call('EuQuero', array('mensagem' => $mensagem));

        if ($res == 'sucesso') {
            $contato['msn'] = 'Obrigado por demonstrar interesse em um de nossos veiculos. <br /> Brevemente um de nossos vendedores entrar&aacute; em contato.';
            $contato['class'] = 'ReturnMessageSucesso';
            $contato['show'] = true;
        }

    endif;


endif;


// - - - - - - - - - - - - - - - - - - -
// Monta resultado da consulta
// - - - - - - - - - - - - - - - - - - -

$carros = $WS_SDS->call($WS_SDS_REQUIRE, array('parametrospage' => $in));

if (empty($carros))
    $carros = array();

if (!is_array($carros))
    $carros = array();


$carros_cout = count($carros);
?>

<script src="<?php echo $PLUGIN_URL ?>js/jquery.ui.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $PLUGIN_URL ?>js/highslide/highslide.js" type="text/javascript" charset="utf-8"></script>

<link href="<?php echo $PLUGIN_URL ?>css/highslide/highslide.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $PLUGIN_URL ?>css/ui.smoothness/jquery-ui.css" rel="stylesheet" type="text/css"/>

<script type="text/javascript">
    hs.graphicsDir = './js/highslide/graphics/';
    hs.outlineType = 'rounded-white';

    $(document).ready(function () {

        $('.euquero').click(function () {

            html = '<div title="Eu Quero!!!">';
            html = html + '<form id="euquero_form" method="post" action="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']; ?>">';
            html = html + '<table border="0" cellspacing="0" cellpadding="0">';
            html = html + '<tr>';
            html = html + '<td height="30" colspan="2" valign="top">Deixe seus dados que um de nosso vendedores entrar� em contato!</td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td height="30" colspan="2">&nbsp;</td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td width="75" height="30" valign="top"><label for="nome">Nome</label></td>';
            html = html + '<td width="425"><input type="text" name="nome" id="nome" style="width: 350px" value="<?php if (isset($_POST['nome'])) echo $_POST['nome']; ?>"/>';
            html = html + '<input type="hidden" name="id" id="id" value="' + $(this).attr('name') + '" />';
            html = html + '</td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td height="30" valign="top">e-mail</td>';
            html = html + '<td><input type="text" name="mail" id="mail" style="width: 350px" value="<?php if (isset($_POST['mail'])) echo $_POST['mail']; ?>"/></td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td height="30" valign="top">Telefone</td>';
            html = html + '<td><input type="text" name="tel" id="tel" style="width: 150px" value="<?php if (isset($_POST['tel'])) echo $_POST['tel']; ?>"/></td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td height="30" valign="top">Mensagem</td>';
            html = html + '<td><textarea name="mensagem" id="mensagem" style="width: 350px"></textarea></td>';
            html = html + '</tr>';
            html = html + '<tr>';
            html = html + '<td height="30" colspan="2" align="right" valign="top" style="padding-right:18px"><label>';
            html = html + '<input type="image" id="querer" name="querer" src="<?php echo $PLUGIN_URL ?>img/contato.png" class="clearInput" />';
            html = html + '</label></td>';
            html = html + '</tr>';
            html = html + '</table>';
            html = html + '</form>';
            html = html + '</div>';
            var $dialog = $(html);

            $dialog.dialog({autoOpen: false, modal: true, height: 440, width: 470, close: function () {
                    $(this).remove();
                }});

            $dialog.dialog('open');

        });

    });
</script>


<div style="border:1px solid red;" class="container">
    <div>
        <div class="container_16">

            <?php if ($contato['show']): ?>
                <div class="clear" style="height:20px"></div>

                <div style="padding-left:10px; padding-right: 10px;">
                    <div class="<?php echo $contato['class']; ?>">
                        <?php echo $contato['msn']; ?>
                    </div>
                </div>

                <div class="clear"></div>
            <?php endif; ?>

            <div class="clear" style="height:10px"></div>

            <div class="grid_16" style="background-color: #f4f4f4; border-top: solid 3px #900">

                <form method="get" action="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']; ?>">

                    <div class="grid_12 top10 alpha">

                        <div class="grid_4 alpha" style="">
                            <div style="padding-left:10px">
                                <div style="margin-left:2px">Filtrar por fam&iacute;lia:</div>
                                <select name="familia" id="familia" style="width:206px;">
                                    <option value="" selected="selected">Escolha </option>
                                    <?php
                                    $prametros = array('modo' => $WS_SDS_MODUS);
                                    $itens = $WS_SDS->call('GetFamilias', array('parametrosbusca' => $prametros));
                                    foreach ($itens as $item):
                                        ?>
                                        <option value="<?php echo urlencode($item['item']); ?>"><?php echo $item['item']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid_4" style="">
                            <div style="padding-left:10px">            
                                <div style="margin-left:2px">Filtrar por cor:</div>
                                <select name="cor" id="cor" style="width:206px;">
                                    <option value="" selected="selected">Escolha </option>
                                    <?php
                                    $itens = $WS_SDS->call('GetCores', array('parametrosbusca' => $prametros));
                                    foreach ($itens as $item):
                                        ?>
                                        <option value="<?php echo urlencode($item['item']); ?>"><?php echo $item['item']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid_4 omega" style="">
                            <div style="padding-left:10px">
                                <div style="margin-left:2px">Filtrar por Combust&iacute;vel:</div>
                                <select name="combustivel" id="combustivel" style="width:206px;">
                                    <option value="" selected="selected">Escolha </option>
                                    <?php
                                    $itens = $WS_SDS->call('GetCombustiveis', array('parametrosbusca' => $prametros));
                                    foreach ($itens as $item):
                                        ?>
                                        <option value="<?php echo urlencode($item['item']); ?>"><?php echo $item['item']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="clear"></div>

                        <div class="grid_12 top10 alpha">

                            <div class="grid_8 alpha">
                                <div style="padding-left:10px">
                                    &nbsp; 
                                </div>
                            </div>

                            <div class="grid_4 omega right" style="">
                                <input name="buscar" type="image" id="buscar" src="<?php echo $PLUGIN_URL ?>img/bot_busca_estoque.jpg" alt="buscar" class="clearInput" style="margin:2px"/>
                            </div>

                        </div>            

                    </div>

                    <div class="grid_4 omega">        
                        &nbsp;      
                    </div>

                </form>

                <div class="clear" style="height:10px"></div>
            </div>


            <div class="clear" style="height:30px"></div>


            <div class="grid_16">

                <?php if ($carros_cout == 0 or empty($carros)): ?>
                    N&atilde;o foi encontrado nenhum carro que atenda aos crit&eacute;rios fornecidos.
                <?php endif; ?>


                <?php
                if ($carros_cout != 0):
                    $i = 1;
                    $img_w = '195';
                    $big_img_w = '500';
                    $img_bg = '255,255,255';


                    foreach ($carros as $carro):

                        $modelo = '';
                        $a = explode('/', $carro['url_imagem']);
                        if (isset($a[0]))
                            $modelo = $a[0];

                        $cor = '';
                        $a = explode('.', $a[1]);
                        if (isset($a[0]))
                            $cor = $a[0];

                        $c = '';
                        if ($carro['status'] == 'A')
                            $c = '&reservado';
                        if ($carro['status'] == 'V')
                            $c = '&reservado';
                        if ($carro['status'] == 'CR')
                            $c = '&reservado';
                        if (($carro['status'] == 'X') or ( $carro['status'] == 'L'))
                            $c = '&vendido';

                        $url_imagem = $CACHE_LOCAL_PACH . $modelo . '/' . $cor . '/' . $WS_SDS_OWNER . '/' . $img_w . '/1.png' . $c;
                        $big_url_imagem = $CACHE_LOCAL_PACH . $modelo . '/' . $cor . '/' . $WS_SDS_OWNER . '/' . $big_img_w . '/1.png' . $c;


                        $class = '';
                        if ($i == 5)
                            $i = 1;
                        if ($i == 1)
                            $class = 'alpha';
                        if ($i == 4)
                            $class = 'omega';

                        $WS_SDS_PAGES_TOTAL = $carro['p_total'];
                        $WS_SDS_ROWS_TOTAL = $carro['total'];
                        ?>

                        <div class="grid_4 <?php echo $class; ?>" style="background-color:#f4f4f4; border-top: solid 3px #900">

                            <div class="center top10">
                                <?php if ($carro['modelo'] != ''): ?> <span style="font-size:18px;"><?php echo utf8_encode($carro['modelo']); ?></span><br /><?php endif; ?>
                            </div>


                            <div class="center top10">
                                <a href="<?php echo $big_url_imagem; ?>" class="highslide" onclick="return hs.expand(this)">
                                    <img src="<?php echo $url_imagem; ?>" style="border:none;" />  
                                </a>
                            </div>

                            <div class="center" style="margin-top:10px">
                                <img src="<?php echo $PLUGIN_URL ?>img/eu-quero.png" class="euquero pointer" name="<?php echo $carro['id']; ?>" />
                            </div> 

                            <div style="padding:10px; line-height: 1.5 ">

                                <?php if ($carro['preco'] != ''): ?>
                                    <strong></strong> 
                                    <span style="font-size:18px">
                                        <?php echo 'R$ ' . number_format((int) utf8_encode($carro['preco']), 0, '', '.'); ?><br />
                                    </span>
                                <?php endif; ?>

                                <?php if ($carro['cor'] != ''): ?>
                                    <strong>Cor:</strong> 
                                    <span style="font-size:10px">
                                        <?php echo utf8_encode($carro['cor']); ?><br />
                                    </span>
                                <?php endif; ?>

                                <?php if ($carro['ano_fab_mod'] != ''): ?> 
                                    <strong>Ano:</strong> 
                                    <span style="font-size:10px">
                                        <?php echo $carro['ano_fab_mod']; ?><br /> 
                                    </span>	
                                <?php endif; ?>

                                <?php if ($carro['combustivel'] != ''): ?> 
                                    <strong>Combust&iacute;vel:</strong> 
                                    <span style="font-size:10px">
                                        <?php echo utf8_encode($carro['combustivel']); ?><br /> 
                                    </span>
                                <?php endif; ?>

                                <?php if ($carro['opcionais'] != ''): ?> 
                                    <strong>Opcionais:</strong> 
                                    <span style="font-size:10px">
                                        <?php echo utf8_encode($carro['opcionais']); ?><br /> 
                                    </span>
                                <?php endif; ?>

                                <?php if ($carro['estoque'] == 'T') : ?>
                                    <span style="font-size:10px">
                                        [EM TRANSITO]
                                    </span><br />
                                <?php endif; ?>

                            </div>

                        </div>

                        <?php if ($i == 4): ?><div class="clear" style="height:20px"></div><?php endif; ?>
                        <?php
                        ++$i;
                    endforeach;
                endif;
                ?>


            </div>

            <div class="clear" style="height:30px"></div>

            <div class="grid_16 center" style="padding:20px; font-size: 12pt; background-color: #f4f4f4; border-top: solid 3px #900;width:900px;">  

                Foram encontrados: <?php echo ($WS_SDS_ROWS_TOTAL) ? $WS_SDS_ROWS_TOTAL : 0; ?> carros

                <div class="clear top10"></div>

                <?php
                $pages = MakePager();
                $pages_cout = count($pages);
                $k = 1;

                foreach ($pages as $link):
                    ?>

                    <a href="<?php echo $link['link']; ?>">
                        <span <?php if ($link['atual']): ?>style="font-weight: bold"<?php endif; ?>><?php echo $link['text']; ?></span>
                    </a>

                    <?php if ($k < ($pages_cout)) echo ' | '; ?>
                    <?php
                    ++$k;
                endforeach;
                ?>

            </div>

            <div class="clear" style="height:10px"></div>

        </div>

    </div>      
</div>
