<?php
$smartdealer = (new SmartDealer());

$update = false;

if (!empty($_POST['command']) && $_POST['command'] === 'save') {
    update_option('smartdealer_instancia', filter_input(INPUT_POST, 'instancia', FILTER_SANITIZE_STRING));
    update_option('smartdealer_usuario', filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    update_option('smartdealer_senha', filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));
    update_option('smartdealer_template', filter_input(INPUT_POST, 'template', FILTER_SANITIZE_STRING));
    $update = $smartdealer->resetCache();
}

if (!empty($_POST['command']) && $_POST['command'] === 'reset-cache') {
    $update = $smartdealer->resetCache();
}
?>

<?php if ($update) : ?>
    <div class="row" style="margin-top: 15px;">
        <div class="updated"><p>As configurações foram salvas com sucesso!</p></div>
    </div>
<?php endif; ?>

<h2 class="page-header">Smart Dealer</h2>
<p class="no-padding" style="margin-top: -20px;">Integração via webservices</p>

<hr />

<form method="POST">

    <input type="hidden" name="command" value="save"/>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="instancia">Instância</label>
                </th>
                <td><input name="instancia" type="text" id="instancia" placeholder="ex: cliente.smartdealer.app" value="<?php echo get_option('smartdealer_instancia'); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="usuario">Usuário de integração</label>
                </th>
                <td><input name="usuario" type="text" id="usuario" placeholder="ex: cliente" value="<?php echo get_option('smartdealer_usuario'); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="senha">Senha</label>
                </th>
                <td><input name="senha" type="password" id="senha" placeholder="ex: abcde12345" value="<?php echo get_option('smartdealer_senha'); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="senha">Template</label>
                </th>
                <td>
                    <select class="regular-text" name="template">
                        <?php $tpl = $smartdealer->templates(); ?>
                        <?php foreach ($tpl AS $k => $label): ?>
                            <option value="<?php echo $k; ?>" <?php if (get_option('smartdealer_template') == $k): ?>selected="selected"<?php endif; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                        <?php if(!$tpl): ?>
						    <option value="default">Padrão</option>
						<?php endif; ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Salvar alterações">
    </p>

</form>

<form method="POST">
    <input type="hidden" name="command" value="reset-cache"/>
    <input type="submit" id="delete-cache" class="button button-secondary" value="Limpar cache"/>
</form>

<!-- add styles -->
<link href="<?php echo SmartDealer::url('css/config.css'); ?>" rel="stylesheet">
