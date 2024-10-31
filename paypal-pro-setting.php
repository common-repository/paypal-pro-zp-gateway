<?php
class PayPalPro {
	var $adminOptionsName = "PayPalProAdminOptions";

	function PayPalPro() { //constructor
	}
	
	function init() {
		$this->getAdminOptions();
	}
	
	//Returns an array of admin options
	function getAdminOptions() {
		$paypalproAdminOptions = array('api_username'=>'','api_password'=>'',
			'api_signature'=>'', 'api_endpoint'=>'', 'paypal_url'=>'','charge_amount'=>'');
		$devOptions = get_option($this->adminOptionsName);
		if (!empty($devOptions)) {
			foreach ($devOptions as $key => $option) {
				$paypalproAdminOptions[$key] = $option;
			}
		}
		if (!isset($paypalproAdminOptions['email_subject']) || !$paypalproAdminOptions['email_subject']){
			$paypalproAdminOptions['email_subject'] = 'Failure notification email';
		}
		if (!isset($paypalproAdminOptions['email_template']) || !$paypalproAdminOptions['email_template']){
			$paypalproAdminOptions['email_template'] = 'The following payment has been processed and failed to update the order status:
Payment from {firstname} {lastname}
Payment Date: {date}
Transaction ID: {transactionid}
Payment Amount: ${total}';
		}
		if (!isset($paypalproAdminOptions['confirm_email_subject']) || !$paypalproAdminOptions['confirm_email_subject']){
			$paypalproAdminOptions['confirm_email_subject'] = 'Payment confirmation email';
		}
		if (!isset($paypalproAdminOptions['confirm_email_template']) || !$paypalproAdminOptions['confirm_email_template']){
			$paypalproAdminOptions['confirm_email_template'] = 'Thank you for your payment.
The details of your payment are as follows:
Payment from {firstname} {lastname}
Payment Date: {date}
Transaction ID: {transactionid}
Payment Amount: ${total}
Thank You!';
		}
		
		
		update_option($this->adminOptionsName, $paypalproAdminOptions);
		return $paypalproAdminOptions;
	}
	// Prints out the admin page
	function printAdminPage() {
		global $_PPP_ZP_GATEWAY_SETTING_UPDATED;
		global $devOptions;
		$_PPP_ZP_GATEWAY_SETTING_UPDATED = 0;
		$devOptions = $this->getAdminOptions();
		if (isset($_POST['update_PaypalProSettings'])) {
			if (isset($_POST['api_username'])) {
				$devOptions['api_username'] = $_POST['api_username'];
			}
			if (isset($_POST['api_password'])) {
				 $devOptions['api_password'] = $_POST['api_password'];
			}
			if (isset($_POST['api_signature'])) {
				$devOptions['api_signature'] = $_POST['api_signature'];
			}
			if (isset($_POST['api_endpoint'])) {
				$devOptions['api_endpoint'] = $_POST['api_endpoint'];
			}
			if (isset($_POST['paypal_url'])) {
				$devOptions['paypal_url'] = $_POST['paypal_url'];
			}
			if (isset($_POST['api_key'])) {
				$devOptions['api_key'] = $_POST['api_key'];
			}
			if (isset($_POST['admin_email'])) {
				$devOptions['admin_email'] = $_POST['admin_email'];
			}
			if (isset($_POST['charge_amount'])) {
				$devOptions['charge_amount'] = $_POST['charge_amount'];
			}
			if (isset($_POST['pay_form_title'])) {
				$devOptions['pay_form_title'] = $_POST['pay_form_title'];
			}				
			if (isset($_POST['email_subject'])) {
				$devOptions['email_subject'] = $_POST['email_subject'];
			}
			if (isset($_POST['email_template'])) {
				$devOptions['email_template'] = $_POST['email_template'];
			}
			if (isset($_POST['confirm_email_subject'])) {
				$devOptions['confirm_email_subject'] = $_POST['confirm_email_subject'];
			}
			if (isset($_POST['confirm_email_template'])) {
				$devOptions['confirm_email_template'] = $_POST['confirm_email_template'];
			}
			if (isset($_POST['email_from'])) {
				$devOptions['email_from'] = $_POST['email_from'];
			}
			if (isset($_POST['notification_email_addr'])) {
				$devOptions['notification_email_addr'] = $_POST['notification_email_addr'];
			}
			update_option($this->adminOptionsName, $devOptions);
			$_PPP_ZP_GATEWAY_SETTING_UPDATED = 1;
		}
		//Display Form
		include (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-form-setting.php");
	}
}// End Class PayPalPro ////////////////
//Initialize the admin panel
function PayPalPro_ap() {
	global $dl_paypalpro;
	if (!isset($dl_paypalpro)) {
		return;
	}
	if (function_exists('add_options_page')) {
		add_options_page('PayPal Pro Plugin', 'PayPalPro', 9, basename(__FILE__), array(&$dl_paypalpro, 'printAdminPage'));
	}
}
function PP_amount() {
	global $dl_paypalpro;
	if (!isset($dl_paypalpro)) {
		return;
	}
	$pp_opts = $dl_paypalpro->getAdminOptions();			
	$pp_amount = ($pp_opts['charge_amount'] > 0)?$pp_opts['charge_amount']:99;
	return $pp_amount;
}
?>