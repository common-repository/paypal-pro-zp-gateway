<?php
	global $_PPP_ZP_GATEWAY_SETTING_UPDATED;
	global $devOptions;
	if ($_PPP_ZP_GATEWAY_SETTING_UPDATED){
?>
<div class="updated"><p><strong><?php _e("Settings Updated.", "PayPalPro");?></strong></p></div>
<?php
	} ?>	
<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>PayPal Pro - ZetaPrints Gateway</h2>
<p>You need to have a PayPal Pro account with PayPal and an account with ZetaPrints.com.<br/>
Insert <b>[paypalpro]</b> shortcode into a page or post where the payments form should appear.<br/>
Plugin support: <a href="mailto:admin@zetaprints.com">admin@zetaprints.com</a></p>

<h3>API Username</h3>
<input type='text' name='api_username' value='<?php _e($devOptions['api_username'],'PayPalPro') ?>' size='50'>

<h3>API Password</h3>
<input type='text' name='api_password' value='<?php _e($devOptions['api_password'],'PayPalPro') ?>' size='20'>

<h3>API Signature</h3>
<input type='text' name='api_signature' value='<?php _e($devOptions['api_signature'],'PayPalPro') ?>' size='70'>

<h3>API Endpoint</h3>
<input type='text' name='api_endpoint' value='<?php _e($devOptions['api_endpoint'],'PayPalPro') ?>' size='50'>

<h3>PayPal URL</h3>
<input type='text' name='paypal_url' value='<?php _e($devOptions['paypal_url'],'PayPalPro') ?>' size='70'>

<h3>ZetaPrints API Key</h3>
<input type='text' name='api_key' value='<?php _e($devOptions['api_key'],'PayPalPro') ?>' size='70'>
<br/><small>Grab the key from your <a href="http://www.zetaprints.com/help/about-web-to-print-api/">API page</a> on ZetaPrints</small><br/>

<h3>Payment Form Title</h3>
<input type='text' name='pay_form_title' value='<?php _e($devOptions['pay_form_title'],'PayPalPro') ?>' size='50'>

<h3>Send failure reports to</h3>
<input type='text' name='admin_email' value='<?php _e($devOptions['admin_email'],'PayPalPro') ?>' size='70'>

<!--h3>Charge Amount</h3>
$<input type='text' name='charge_amount' value='<?php //_e($devOptions['charge_amount'],'PayPalPro') ?>' size='6'-->


<h3>Failure notification email subject</h3>
<input type='text' name='email_subject' value='<?php _e($devOptions['email_subject'],'PayPalPro') ?>' size='50'>

<h3>Failure notification email body</h3>
<textarea rows="10" name='email_template' cols="70"><?php _e($devOptions['email_template'],'PayPalPro') ?></textarea>
<br/><small>Tags:<i>{firstname},{lastname},{date},{transactionid},{total}</i><br/>
Email is sent to the admin when a payment was processed, but order status could not be updated.</small><br/>

<h3>Payment confirmation email subject</h3>
<input type='text' name='confirm_email_subject' value='<?php _e($devOptions['confirm_email_subject'],'PayPalPro') ?>' size='50'>

<h3>Payment confirmation email body</h3>
<textarea rows="10" name='confirm_email_template' cols="70"><?php _e($devOptions['confirm_email_template'],'PayPalPro') ?></textarea>
<br/><small>Tags:<i>{firstname},{lastname},{date},{transactionid},{total}</i><br/>
Email is sent to the user upon completion of the transaction.</small><br/>

<div class="submit">
<input type="submit" name="update_PaypalProSettings"
value="<?php _e('Update Settings', 'PayPalPro') ?>" /></div>
</form>
</div>