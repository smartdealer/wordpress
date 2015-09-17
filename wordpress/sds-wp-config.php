<?php 
$msg = '';

if($_POST){
	update_option('sds_instancia', $_POST['instancia']);
	update_option('sds_usuario', $_POST['usuario']);
	update_option('sds_senha', $_POST['senha']);
	
	$msg = '<div class="updated" id="message"><p>As configurações foram salvas com sucesso!</p></div>';
}
?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br /></div> 

<h2>Smart Dealer - Configurações de Integração</h2>
<?php echo $msg; ?>
<form action="" method="post">
  <br><b>Web Service</b><br><br>
 
  <table>
	<tr>
		<td style="width:100px;text-align:right;">Instância:&nbsp;</td>
		<td>  <input type="text" name="instancia" value="<?php echo get_option('sds_instancia');?>"/></td>
	</tr>

<tr>
		<td style="width:100px;text-align:right;">Usuario:&nbsp;</td>
		<td>  <input type="text" name="usuario" value="<?php echo get_option('sds_usuario');?>"/></td>
	</tr>
<tr>
		<td style="width:100px;text-align:right;">Senha:&nbsp;</td>
		<td>  <input type="password" name="senha" value="<?php echo get_option('sds_senha');?>"/></td>
	</tr>
  </table>

<br><br>

<input type="submit" name="Submit" class="button-primary" value="Salvar altera&ccedil;&otilde;es" /> 
</form>
</div>
</div>