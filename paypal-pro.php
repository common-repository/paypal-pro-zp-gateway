<?php
/*
Plugin Name: PayPalPro ZetaPrints Gateway
Plugin URI: http://code.google.com/p/paypal-pro-wordpress-gateway/
Version: 2.0.2
Date:2009-08-28 09:40:00
Author: <a href="http://www.biinno.com">Pham Tri Cong</a>
Description: A plugin for <a href="http://code.google.com/p/paypal-pro-wordpress-gateway/">PayPal Pro gateway for ZetaPrints</a>. 
*/
//For debugging with FirePHP
//require_once WP_PLUGIN_DIR.'/wp-firephp/FirePHPCore/fb.php';

//Demo mode switcher
//Uncomment to enable demo mode
//define('PAYPAL_PRO_DEMO_MODE', true);

if (!defined('PAYPAL_PRO_ZP_GATEWAY_ROOT')) {
	define('PAYPAL_PRO_ZP_GATEWAY_ROOT', dirname( __FILE__) . "/");
	define('PAYPAL_PRO_ZP_GATEWAY_PATH', WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__),"",plugin_basename(__FILE__)));
	
	define('PAYPAL_PRO_STEP_SHOW_FORM', 1);
	define('PAYPAL_PRO_STEP_SHOW_RESULT', 2);
	define('PAYPAL_PRO_STEP_SHOW_UPDATE_ZP_ERROR', 3);
	
	include_once (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-setting.php");
	include_once (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-api.php");	
	include_once (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-logic.php");
	function paypalpro_shortcode($attr, $content = null){
		ob_start();
		$output = apply_filters('paypalpro_shortcode', '', $attr, $content);
		do_action('ppp_form');
		return ob_get_clean();
	}
	function ppp_css() {
		echo '<link rel="stylesheet" href="'. WP_PLUGIN_URL . '/paypal-pro-zp-gateway/paypal-pro.css" type="text/css" media="screen" />';
	}
}
global $dl_paypalpro;//
global $pp_opts;//Paypal Payment Setting Data
global $pp_vars;//Paypal Payment Submit By user Data
global $zp_vars;//Paypal Payment Submit By ZetaPrints
global $pp_step;//
$dl_paypalpro = new PayPalPro();
$pp_opts = $dl_paypalpro->getAdminOptions();
//add_action('wp_head', array(&$dl_paypalpro, 'addHeaderCode'), 1);
add_action('activate_paypal-pro/paypal-pro.php', array(&$dl_paypalpro, 'init'));	
add_action('admin_menu', 'PayPalPro_ap');
add_action('wp_head', 'ppp_css');
add_shortcode('paypalpro', 'paypalpro_shortcode');
add_action('ppp_form','DoPPActivity');
?>
