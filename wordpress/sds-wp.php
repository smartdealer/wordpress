<?php 
/*
Plugin Name: Smart Dealer	
Plugin URI: http://www.smartdealer.com.br
Description: Integração do estoque da concessionária - Sistema Smart Dealer
Version: 1.0
Author: Patrick Otto
Author URI: http://www.smartdealer.com.br
*/

class SmartDealer{

	public function ativar(){
		update_option('sds_instancia', '');
		update_option('sds_usuario', '');
		update_option('sds_senha', '');
	
	}
	public function desativar(){
		delete_option('sds_instancia');
		delete_option('sds_usuario');
		delete_option('sds_senha');
	}
	public function criarMenu(){
		
		add_menu_page('Integração Smart Dealer', 'Smart Dealer',10, 'sds-wp/sds-wp-config.php', null, 'https://botta.smartdealer.com.br/numeor/view/img/favicon.ico');
		
		add_submenu_page('sds-wp/sds-wp-config.php', 'Veículos Novos', 'Novos', 10, 'sds-wp/sds-wp-novos.php');
		add_submenu_page('sds-wp/sds-wp-config.php', 'Veículos Usados', 'Usados', 10, 'sds-wp/sds-wp-usados.php');
		add_submenu_page('sds-wp/sds-wp-config.php', 'Veículos Corsia', 'Corsia', 10, 'sds-wp/sds-wp-corsia.php');
		
	}

	public function adicionaFrase($textoDoPost){		
	
	
		return "ADICIONANDO FRASSSEE";
	
		/*$frase = get_option('meu_wp');	
		
		if( strlen( $frase ) > 0 ){
			//famos dar um estilo pra diferenciar do resto!
			$frase = '<span style="color: #f00; font-size: 18px;">'.$frase.'</span>';
			
			return $frase."<br /><br />".$textoDoPost;
		}
		else{
			return $textoDoPost;
		}*/
	}
}

$pathPlugin = substr(strrchr(dirname(__FILE__),DIRECTORY_SEPARATOR),1).DIRECTORY_SEPARATOR.basename(__FILE__);

// Função ativar
register_activation_hook( $pathPlugin, array('SmartDealer','ativar'));

// Função desativar
register_deactivation_hook( $pathPlugin, array('SmartDealer','desativar'));

//Ação de criar menu
add_action('admin_menu', array('SmartDealer','criarMenu'));

//Filtro do conteúdo
#add_filter("the_content", array("SmartDealer","adicionaFrase"));


?>