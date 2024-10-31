<?php
global $dl_paypalpro,$pp_opts;
global $pp_errors;
global $pp_vars;
global $zp_vars;
//header("Location:" . $zp_vars['return']);
?>
<table id="payPalPro">
	<tr>
		<td><b>Thank you for your payment! We have been notified of payment. You should receive an email receipt shortly.</b></td>
	</tr>
	<tr>
		<td>
			<br/>
			<input type='button' id="ccSecCode" name='cancelpayment' value='Return to the order' onclick="location.href='<?PHP echo $zp_vars['cancel_return']?>'">
		</td>
	</tr>
</table>