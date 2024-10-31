<?php
global $dl_paypalpro;
global $pp_errors;
global $error_title;
global $pp_opts;
global $pp_vars;
global $zp_vars;
global $country_select,$state_select,$province_select,$credit_cards;
//$fname, $lname, $address1, $city, $state, $zip, $country, $eaddr, $cc_type, $cc_num, $cc_exp, $cc_cvc, $pay_success,$amount,$OrderID,$notify_url,$item_name,$cancel_return;
//$spacer = "<tr><td colspan='2'>&nbsp;</td></tr>";
?>
<form method='POST' action='<?php echo $_SERVER['REQUEST_URI'];?>'>
<table id="payPalPro">
	<tr>
		<td colspan='2' class="productbox"><?php echo $pp_opts['pay_form_title']; ?></td>
	</tr>
<?php
	if (count($pp_errors) > 0) {
		if (!$error_title) $error_title = "Please correct the following";
		echo "<tr><td valign='top' colspan='2' class='error'><div class='error_main'><b>$error_title:</b><br/>";
		echo implode("</div><div class='error'>",$pp_errors);
		echo "</div></td></tr>";
	}
?>
	<tr>
		<th>First Name:</td>
		<td ><input type='text' name='FIRSTNAME' class="textfield" value='<?php echo htmlSafe($pp_vars['FIRSTNAME']); ?>' size='30'></td>
	</tr>

	<tr>
		<th>Last Name:</td>
		<td><input type='text' name='LASTNAME' class="textfield" value='<?php echo htmlSafe($pp_vars['LASTNAME']); ?>' size='30'></td>
	</tr>

	<tr>
		<th>Street Address:</td>
		<td><input type='text' name='STREET' class="textfield" value='<?php echo htmlSafe($pp_vars['STREET']); ?>' size='30'></td>
	</tr>
	<tr>
		<th>Street Address 2:</td>
		<td><input type='text' name='STREET2' class="textfield" value='<?php echo htmlSafe($pp_vars['STREET2']); ?>' size='30'></td>
	</tr>
	<tr>
		<th>City:</td>
		<td><input type='text' name='CITY' class="textfield" value='<?php echo htmlSafe($pp_vars['CITY']); ?>' size='30'></td>
	</tr>

	<tr>
		<th>State/Province:</td>
		<td>
<?php
echo formSelect('STATE', $pp_vars['STATE'], array_merge($state_select,$province_select ), '-- Select --');
?>		</td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Zip/Postal Code:</td>
		<td ><input type='text' name='ZIP' class="textfield" value='<?php echo htmlSafe($pp_vars['ZIP']); ?>' size='10'></td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Country:</td>
		<td>
<?php
echo formSelect('COUNTRYCODE', $pp_vars['COUNTRYCODE'], $country_select);
?>		</td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Email Address:</td>
		<td ><input type='text' name='EMAIL' class="textfield" value='<?php echo htmlSafe($pp_vars['EMAIL']); ?>' size='30'></td>
	</tr>
	
<?php echo $spacer; ?>
<?php echo $spacer; ?>
<!-- End Shipping -->
<?php if(!$zp_vars['no_shipping']){?>
	<tr>
		<td colspan="2" class="productbox">Shipping Information: </td>
	</tr>
	<tr>
		<th>Full Name:</td>
		<td ><input type='text' name='SHIPTONAME' class="textfield" value='<?php echo htmlSafe($pp_vars['SHIPTONAME']); ?>' size='30'></td>
	</tr>
	<tr>
		<th>Street Address:</td>
		<td><input type='text' name='SHIPTOSTREET' class="textfield" value='<?php echo htmlSafe($pp_vars['SHIPTOSTREET']); ?>' size='30'></td>
	</tr>
	<tr>
		<th>Street Address 2:</td>
		<td><input type='text' name='SHIPTOSTREET2' class="textfield" value='<?php echo htmlSafe($pp_vars['SHIPTOSTREET2']); ?>' size='30'></td>
	</tr>
	<tr>
		<th>City:</td>
		<td><input type='text' name='SHIPTOCITY' class="textfield" value='<?php echo htmlSafe($pp_vars['SHIPTOCITY']); ?>' size='30'></td>
	</tr>

	<tr>
		<th>State/Province:</td>
		<td>
<?php
echo formSelect('SHIPTOSTATE', $pp_vars['SHIPTOSTATE'], array_merge($state_select,$province_select ), '-- Select --');
?>		</td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Zip/Postal Code:</td>
		<td ><input type='text' name='SHIPTOZIP' class="textfield" value='<?php echo htmlSafe($pp_vars['SHIPTOZIP']); ?>' size='10'></td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Country:</td>
		<td>
<?php
echo formSelect('SHIPTOCOUNTRYCODE', $pp_vars['SHIPTOCOUNTRYCODE'], $country_select);
?>		</td>
	</tr>
<?php echo $spacer; ?>
<?php echo $spacer; ?>
<?php 
}
?>
<!-- End Shipping -->
<tr>
	<td colspan="2" class="productbox">Credit Card Information: </td>
  </tr>
<?php echo $spacer; ?>
	<tr>
		<th>Credit Card Type:</td>
		<td >
<?php
// credit card type CREDITCARDTYPE','ACCT','EXPDATE','CVV2');
echo formSelect('CREDITCARDTYPE', $pp_vars['CREDITCARDTYPE'], $credit_cards);
?>		</td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Credit Card Number:</td>
		<td><input type='text' name='ACCT' class="textfield" value='<?php echo htmlSafe($pp_vars['ACCT']); ?>' size='16'></td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Credit Card Expiration:</td>
		<td>
<?php
echo expDateDropdown('EXPDATE',$pp_vars['EXPDATE']);
?>		</td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Credit Card Security Code:</td>
		<td ><input type='text' name='CVV2' class="textfield" value='<?php echo htmlSafe($pp_vars['CVV2']); ?>' maxlength="3"></td>
	</tr>
<?php echo $spacer; ?>
	<tr>
		<th>Payment Amount:</td>
		<td ><?php echo currency($zp_vars['amount']); ?></td>
	</tr>
	<?php foreach ($zp_vars as $key=>$val){?>
	<input type="hidden" name="zp_<?php echo $key ;?>" value="<?php echo $val; ?>">
	<?php 
	} 
	?>
<?php echo $spacer; ?>	
	<tr>
		<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type='submit' id="ccSecCode" name='submitpayment' value='Send Payment'>&nbsp;
		<input type='button' id="ccSecCode" name='cancelpayment' value='Cancel' onclick="location.href='<?PHP echo $zp_vars['cancel_return']?>'">
		</td>
	</tr>
</form>
</table>