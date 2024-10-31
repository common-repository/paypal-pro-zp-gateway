<?php
global $dl_paypalpro,$pp_opts;
global $pp_errors;
global $pp_vars;
global $zp_vars;
?>
<table id="payPalPro">
	<tr>
		<td><br/>Your payment has been processed, but <font color="red">we could not update our database to
allow product shipping / downloading </font>. Please, contact <a href="mailto:<?php echo $pp_opts['admin_email'] ?>"><?php echo $pp_opts['admin_email'] ?></a> for
immediate action. We apologize for the inconvenience.
<br/>
</td>
	</tr>
	<tr>
		<td>
			<br/>
			<input type='button' id="ccSecCode" name='cancelpayment' value='Return to the order' onclick="location.href='<?PHP echo $zp_vars['cancel_return']?>'">
		</td>
	</tr>
</table>