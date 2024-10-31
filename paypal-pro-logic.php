<?php
function DoPPActivity() {
	PP_ProcessPayment();
	PP_Form();
}
function PP_Form_Payment_Init(){
	global $pp_vars;
	global $zp_vars;
	$credits 	= array('CREDITCARDTYPE','ACCT','EXPDATE','CVV2');
	$bills		= array('FIRSTNAME','LASTNAME','STREET','STREET2','CITY','STATE','ZIP','COUNTRYCODE','EMAIL');
	$shippings 	= array('SHIPTONAME','SHIPTOSTREET','SHIPTOSTREET2','SHIPTOCITY','SHIPTOSTATE','SHIPTOZIP','SHIPTOCOUNTRYCODE','SHIPTOPHONENUM');
	$zps 		= array('cmd','notify_url','amount','no_shipping','item_name','currency_code','business','cancel_return','return','address1','address2','city','country','state','zip');
	$zp_to_bills 		= array('address1'=>'STREET','address2'=>'STREET2','city'=>'CITY','state'=>'STATE','zip'=>'ZIP','country'=>'COUNTRYCODE');
	$zp_to_shippings 	= array('address1'=>'SHIPTOSTREET','address2'=>'SHIPTOSTREET2','city'=>'SHIPTOCITY','state'=>'SHIPTOSTATE','zip'=>'SHIPTOZIP','country'=>'SHIPTOCOUNTRYCODE');
	if (($_SERVER['REQUEST_METHOD'] == "POST") && isset($_POST['submitpayment'])) {
		foreach ($zps as $name){
			$zp_vars[$name] 		= PP_Form_GetParam("zp_$name");
		}
		foreach ($credits as $name){
			$pp_vars[$name] 	= PP_Form_GetParam($name);
		}
		foreach ($bills as $name){
			$pp_vars[$name] 	= PP_Form_GetParam($name);
		}
		if (!$zp_vars['no_shipping']){
			foreach ($shippings as $name){
				$pp_vars[$name] 	= PP_Form_GetParam($name);
			}
		}
		$pp_vars['CURRENCYCODE']= $zp_vars['currency_code'];
		$pp_vars['L_NAME0'] 	= $zp_vars['item_name'];
		$amt 					= $zp_vars['amount'];
		if ($amt > 0){
			$pp_vars['AMT'] 		= number_format($amt,2,'.','');
			$pp_vars['L_AMT0'] 		= $pp_vars['AMT'];
		}
		//print_r($pp_vars);exit();
	}else{
		foreach ($zps as $name){
			$zp_vars[$name] 		= PP_Form_GetParam("$name");
		}
		foreach ($zp_to_bills as $from => $to){
			$pp_vars[$to] 		= $zp_vars[$from];
		}
		if (!$zp_vars['no_shipping']){
			foreach ($zp_to_shippings as $from => $to){
				$pp_vars[$to] 		= $zp_vars[$from];
			}
		}
	}
	//print_r($_POST);exit();
	//print_r($pp_vars);exit();
}
function PP_Form_GetParam($name){
	if (isset($_POST[$name])) return trim(stripslashes($_POST[$name]));
	return "";
}
function PP_Form() {
	global $pp_step;
	include (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-cart.php");
	if($pp_step==PAYPAL_PRO_STEP_SHOW_FORM){
		include (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-form-payment.php");
	}else if($pp_step==PAYPAL_PRO_STEP_SHOW_UPDATE_ZP_ERROR){
		include (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-form-payment-zp-error.php");
	}else{
		include (PAYPAL_PRO_ZP_GATEWAY_ROOT . "paypal-pro-form-payment-result.php");
	}
}
function PP_ProcessPayment() {
	global $pp_vars;
	global $zp_vars;
	global $pp_errors;
	global $error_title;
	global $pp_step;
	PP_Form_Payment_Init();
	$pay_success = FALSE;
	$pp_errors = array();
	$pp_step = PAYPAL_PRO_STEP_SHOW_FORM;
	if (!(($_SERVER['REQUEST_METHOD'] == "POST") && isset($_POST['submitpayment']))) {
		//SHOW FORM
		return 0;
	}
	//Validate input data
	$pp_errors = PP_Payment_Validate($pp_vars);
	if (count($pp_errors) > 0) return $pp_errors;

        //Check for demo mode
        if (defined('PAYPAL_PRO_DEMO_MODE')) {
          $pp_step = PAYPAL_PRO_STEP_SHOW_RESULT;
          return 0;
        }
	
	//do payment	
	$payment = PP_Payment_DoDirect($pp_vars);
	//check error
	if ($payment['error']){
		//Transaction error
		$error_title = "Payment transaction error";
		$pp_errors = $payment['error'];
		if (count($pp_errors) > 0) return $pp_errors;
	}
	//print_r($payment);
	$res = $payment['paypal'];
	$errors = PP_Payment_Errors($res);
	if ($errors){
		//Transaction error
		$error_title = "Payment transaction error";
		$pp_errors = $errors;
		return $pp_errors;
	}
	//Transaction is ok
	//Process OK
	/*
			$ack = strtoupper($resArray["ACK"]);
	*/
	$after = PP_ProcessPayment_After($res);
	if ($after == 0){
		$pp_step = PAYPAL_PRO_STEP_SHOW_RESULT;
	}else{
		$pp_step = PAYPAL_PRO_STEP_SHOW_UPDATE_ZP_ERROR;
	}
	return 0;
}
function PP_ProcessPayment_After($res){
	global $pp_vars;
	global $zp_vars;
	global $pp_opts;
	//print_r($res);
	$vars["TRANSACTIONID"] = $res["TRANSACTIONID"];
	$paypal = PP_Payment_GetTransactionDetails($vars);
	//print_r($ppDetail);exit();
	if (!$paypal || !isset($paypal['paypal'])) {
		$ppDetail['FIRSTNAME'] = $pp_vars['FIRSTNAME'];
		$ppDetail['LASTNAME'] = $pp_vars['LASTNAME'];
		$ppDetail['TRANSACTIONID'] = $res["TRANSACTIONID"];
		PP_ProcessPayment_Mail_Notice($ppDetail);
		return -1;
	}
	$ppDetail = $paypal['paypal'];	
	$zpVars = array(
		"mc_gross"=>""
		,"protection_eligibility"=>""
		,"address_street"=>$pp_vars['STREET']
		,"charset"=>""
		,"address_name"=>$pp_vars['FIRSTNAME'] . " " . $pp_vars['LASTNAME']
		,"address_zip"=>$pp_vars['ZIP']
		,"address_country"=>$pp_vars['COUNTRYCODE']
		,"address_state"=>$pp_vars['STATE']
		,"address_city"=>$pp_vars['CITY']
		,"mc_fee"=>""
		,"custom"=>""	
		,"business"=>$pp_opts["admin_email"]
		,"verify_sign"=>""
		,"payer_email"=>""
		,"item_number"=>"1"
		,"residence_country"=>$ppDetail['COUNTRYCODE']
		,"handling_amount"=>""
		,"transaction_subject"=>""
		,"shipping"=>""
		,"ApiKey"=>$pp_opts["api_key"]
	);
	$mapping = array(
		"RECEIVERBUSINESS" => "",
		"RECEIVEREMAIL" => "receiver_email",
		"RECEIVERID" => "receiver_id",
		"EMAIL" => "payer_email",
		"PAYERID" => "payer_id",
		"PAYERSTATUS" => "",
		"COUNTRYCODE" => "address_country_code",
		"ADDRESSOWNER" => "address_owner",
		"ADDRESSSTATUS" => "address_status",
		"SALESTAX" => "sales_tax",
		"TIMESTAMP" => "",
		"CORRELATIONID" => "correlation_id",
		"ACK" => "ack",
		"VERSION" => "notify_version",
		"BUILD" => "build",
		"FIRSTNAME" => "first_name",
		"LASTNAME" => "last_name",
		"TRANSACTIONID" => "txn_id",
		"RECEIPTID" => "receipt_id",
		"TRANSACTIONTYPE" => "txn_type",
		"PAYMENTTYPE" => "payment_type",
		"ORDERTIME" => "payment_date",
		"AMT" => "payment_gross",
		"FEEAMT" => "payment_fee",
		"TAXAMT" => "tax",
		"CURRENCYCODE" => "mc_currency",
		"PAYMENTSTATUS" => "payment_status",
		"PENDINGREASON" => "pending_reason",
		"REASONCODE" => "reason_code",
		"L_NAME0" => "item_name",
		"L_QTY0" => "quantity",
		"L_CURRENCYCODE0" => "",
		"L_AMT0" => ""
	);
	$req = "";
	$comma = "";
	foreach ($mapping as $from=>$to){
		if($to){
			if (isset($ppDetail[$from])){
				$zpVars[$to] = $ppDetail[$from];
			}else{
				$zpVars[$to] = "";
			}
		}
	}
	$req = PP_Payment_Query($zpVars, "");
	pp_zp_log("update zetaprints");
	pp_zp_log("url=[" . $zp_vars['notify_url'] . "]");
	pp_zp_log("req=[$req]");
	
	for($i=1;$i<6;$i++){
		$ret = HP_SendPost($zp_vars['notify_url'],$req);
		if (!$ret['error']) break;
	}
	pp_zp_log("post to zp done");
	if ($ret['error'] || (strpos($ret['response'],'<ok') === false)){
		//fail
		pp_zp_log("end update zetaprints error=[" . $ret['error'] . "]");
		pp_zp_log("end update zetaprints response=[" . $ret['response'] . "]");
		PP_ProcessPayment_Mail_Notice($ppDetail);
		return -1;
	}else{
		//ok
		pp_zp_log("end update zetaprints ret=[ok]");
		PP_ProcessPayment_Mail_Ok($ppDetail);
	}
	return 0;
}
function PP_ProcessPayment_Mail_Ok($res){
	global $pp_vars;
	global $zp_vars;
	global $pp_opts;
	$p_date = date("M d, Y");
	$subject = $pp_opts['confirm_email_subject'];
	$body = $pp_opts['confirm_email_template'];
	$body = str_replace('{firstname}',$pp_vars['FIRSTNAME'],$body);
	$body = str_replace('{lastname}',$pp_vars['LASTNAME'],$body);
	$body = str_replace('{date}',$p_date,$body);
	$body = str_replace('{total}',$zp_vars['amount'],$body);
	$body = str_replace('{transactionid}',$res['TRANSACTIONID'],$body);
	
	PP_SendMail($pp_opts['admin_email'],$pp_vars['EMAIL'], $subject, $body);
}
function PP_ProcessPayment_Mail_Notice($res){
	global $pp_vars;
	global $zp_vars;
	global $pp_opts;
	$p_date = date("M d, Y");
	$subject = $pp_opts['email_subject'];
	$body = $pp_opts['email_template'];
	$body = str_replace('{firstname}',$pp_vars['FIRSTNAME'],$body);
	$body = str_replace('{lastname}',$pp_vars['LASTNAME'],$body);
	$body = str_replace('{date}',$p_date,$body);
	$body = str_replace('{total}',$zp_vars['amount'],$body);
	$body = str_replace('{transactionid}',$res['TRANSACTIONID'],$body);
	//$body .= "\nThere is problem to notify url.";
	//Transaction ID:
	PP_SendMail($pp_opts['admin_email'],$pp_opts['admin_email'], $subject, $body);
}
function PP_SendMail($from, $to, $subject, $body){
	$headers = "From: ".$from."\r\n";
	$headers .= "Return-Path: ".$from."\r\n";
	pp_zp_log("mail from [$from] to [$to],subject=[$subject] body =[$body]  --  end mail");
	@mail($to, $subject, $body, $headers);
}
?>