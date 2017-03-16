<?php
$msg = '';

if ($_POST) {
    update_option('sds_instancia', $_POST['instancia']);
    update_option('sds_usuario', $_POST['usuario']);
    update_option('sds_senha', $_POST['senha']);
    update_option('sds_path_novos', $_POST['path_novos']);
    update_option('sds_path_usados', $_POST['path_usados']);
    update_option('sds_path_corsia', $_POST['path_corsia']);
    $msg = '<div class="updated" id="message"><p>As configurações foram salvas com sucesso!</p></div>';
}

$pages = get_pages();
?>

<div class="row">
    <?php echo $msg; ?>
</div>

<div class="sd-scope sd-config">
    <div class="col-md-12">
        <div class="col-md-12">
            <h2 class="page-header">Smart Dealer</h2>
            <p class="no-padding" style="margin-top: -20px;">Integração via webservices</p>
        </div>
        <form action="" method="post">
            <div class="col-md-6">
                <h4 style="margin-bottom: -15px;">Configuração</h4>
                <hr>
                <div class="form-group">
                    <label>Instância</label>
                    <input type="text" name="instancia" class="form-control" placeholder="ex: phipasa" value="<?php echo get_option('sds_instancia'); ?>"/>
                </div>
                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="usuario" class="form-control" value="<?php echo get_option('sds_usuario'); ?>"/>
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" value="<?php echo get_option('sds_senha'); ?>"/>
                </div>
            </div>
            <div class="col-md-6">
                <h4 style="margin-bottom: -15px;">Rotas personalizadas</h4>
                <hr>
                <div class="form-group">
                    <label>Página de novo</label>
                    <select class="form-control" name="path_novos">
                        <option value="">Nenhuma</option>
                        <?php foreach ($pages AS $page): ?>
                            <option value="<?php echo $page->ID ?>" <?php if (get_option('sds_path_novos') == $page->ID) echo "selected"; ?>><?php echo $page->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Página de usado</label>
                    <select class="form-control" name="path_usados">
                        <option value="">Nenhuma</option>
                        <?php foreach ($pages AS $page): ?>
                            <option value="<?php echo $page->ID ?>" <?php if (get_option('sds_path_usados') == $page->ID) echo "selected"; ?>><?php echo $page->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Página de corsia</label>
                    <select class="form-control" name="path_corsia">
                        <option value="">Nenhuma</option>
                        <?php foreach ($pages AS $page): ?>
                            <option value="<?php echo $page->ID ?>" <?php if (get_option('sds_path_corsia') == $page->ID) echo "selected"; ?>><?php echo $page->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <hr>
                <input type="submit" name="Submit" class="button-primary pull-right" value="Salvar altera&ccedil;&otilde;es" /> 
            </div>
        </form>
    </div>
    <div class="clear clearfix"></div>
</div>

<!-- add styles -->
<link href="<?php echo SmartDealer::url('css/bootstrap-minimal.css'); ?>" rel=stylesheet>

<!-- custom for page -->
<style type="text/css">

    .wp-admin select, .wp-admin input {
        padding: 2px;
        line-height: 28px;
        height: 28px;
        vertical-align: middle;
    }

    .no-padding{
        padding: 0px;
    }

    .sd-config{
        margin-top: 15px;
        padding-bottom: 15px;
        margin-right: 15px;
    }

    .notice, div.error, div.updated {
        background: #fff;
        border-left: 4px solid greenyellow;
        -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        margin: 15px 15px 0px 0px;
        padding: 1px 10px;
    }


</style>