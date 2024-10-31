<?php
define('PP_ZP_GATEWAY_VERSION','1.0');
define('USE_PROXY',FALSE);
define('VERSION', '3.0');
function PP_Payment_Init(){
	global $API_Endpoint,$API_UserName,$API_Password,$API_Signature,$dl_paypalpro, $pp_opts;
	$pp_opts 		= $dl_paypalpro->getAdminOptions();
	$API_UserName 	= $pp_opts['api_username'];
	$API_Password 	= $pp_opts['api_password'];
	$API_Signature 	= $pp_opts['api_signature'];
	$API_Endpoint 	= $pp_opts['api_endpoint'];
	$API_KEY 		= $pp_opts['api_key'];
	$admin_email	= $pp_opts['admin_email'];

}
//Payment process
function PP_Payment_GetTransactionDetails($vars){
	return PP_Payment_Execute('GetTransactionDetails',$vars);
}
function PP_Payment_DoDirect($vars){
	if (!isset($vars['PAYMENTACTION'])) $vars['PAYMENTACTION'] = 'Sale';
	return PP_Payment_Execute('DoDirectPayment',$vars);
}
function PP_Payment_Execute($method, $vars){
	global $API_Endpoint,$API_UserName,$API_Password,$API_Signature;
	if (!$API_Endpoint) PP_Payment_Init();
	if (!isset($vars['IPADDRESS'])) $vars['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];
	$nvpStr = PP_Payment_Query($vars);
	return PP_HttpPost($method,$nvpStr,$API_UserName,$API_Password,$API_Signature,$API_Endpoint);
}
function PP_Payment_Query($vars, $start='&'){
	$nvpStr = "";
	$comma = $start;
	foreach ($vars as $key=>$val){
		$nvpStr .= $comma . $key . "=" . urlencode($val);
		$comma = "&";
	}
	return $nvpStr;
}
function PP_HttpPost($methodName_, $nvpStr_,$API_UserName,$API_Password,$API_Signature,$API_Endpoint) {
	// Set up your API credentials, PayPal end point, and API version.
	$API_UserName = urlencode($API_UserName);
	$API_Password = urlencode($API_Password);
	$API_Signature = urlencode($API_Signature);
	$version = urlencode(VERSION);	
	// Set the API operation, version, and API signature in the request.
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

	$ret = HP_SendPost($API_Endpoint, $nvpreq);
	if ($ret['error']) return $ret;
	// Get response from the server.
	$httpResponse = $ret['response'];	
	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		$ret['error'][] = "Invalid HTTP Response for request to Paypal";//exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		return $ret;
		//exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}
	$ret['paypal'] = $httpParsedResponseAr;
	return $ret;
}
function PP_Payment_Errors($response){
	if (!$response) return null;
	if (strtoupper($response['ACK']) == 'SUCCESS') return null;
	$ret = array();
	foreach ($response as $key=>$code){
		if (strpos($key, 'L_ERRORCODE') === 0){
			$err = urldecode($response[str_replace('L_ERRORCODE','L_LONGMESSAGE',$key)]);
			$ret[] = "[$code] $err";
		}
	}
	if (count($ret) < 1) 
		$ret[] = "Unknown Error";
	return $ret;
}
//Payment Validate 
function PP_Payment_ValidateField($key, $type, $vars){
	//check is input
	//print_r($type);exit();
	if (!isset($vars[$key])){
		if (($type['minlen'] !== '-1' ) && ($type['minlen'] !== '0' ))
			return $type['name'] . " is required.";
		return '';
	}
	$val = $vars[$key];
	if (!$val){
		if ($type['minlen'] !== '-1')
			return $type['name'] . " is required.";
		return '';
	}
	$len = strlen ($val);
	if (($type['minlen'] > 0) && ($len < $type['minlen'])){
		return $type['name'] . " must be at least " . $type['minlen'] ." characters.";
	}
	if (($type['maxlen'] > 0) && ($len > $type['maxlen'])){
		return $type['name'] . " must be less than or equal to max " . $type['maxlen'] ." characters.";
	}
	if ($type['type']== 'acct'){
		if (!validAcct($val))
			return "Invalid ". $type['name'] . ".";
	}else if ($type['type']== 'expdate'){
		if (!allNumbers($val))
			return "Invalid ". $type['name'] . ".";
	}else if ($type['type']== 'cvv'){
		if (!validCvv($val))
			return "Invalid ". $type['name'] . ".";
	}else if ($type['type']== 'email'){
		if (!validEmail($val))
			return $type['name'] . " must be an email address.";
	}else if ($type['type']== 'num'){
		if (!allNumbers($val))
			return $type['name'] . " must be a number.";
	}else if ($type['type']== 'phone'){
		if (!allNumbers($val))
			return $type['name'] . " must be a phone number.";
	}
	return "";
}
function validAcct($val) {
	return allNumbers($val);
}
function validCvv($val) {
	return allNumbers($val);
}
function validEmail($val) {
	if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.([a-z]){2,4})$",$val)) {
		return true;
	} else {
		return false;
	}
}
function allNumbers($val) { // FASTER?
	return preg_replace("/[\D]/","",$val);
}
function PP_Payment_Validate($vars){
	$fields = array(
		'CREDITCARDTYPE'=>array('type'=>''		,'minlen'=>'0','maxlen'=>'0','name'=>'Credit Card Type')
		,'ACCT'=>array('type'=>'acct'			,'minlen'=>'10','maxlen'=>'20','name'=>'Credit Card Number')
		,'EXPDATE'=>array('type'=>'expdate'		,'minlen'=>'6','maxlen'=>'6','name'=>'Credit Card Expiration')
		,'CVV2'=>array('type'=>'cvv'			,'minlen'=>'1','maxlen'=>'3','name'=>'Credit Card Security Code')
		//,'STARTDATE'=>array('type'=>'num','minlen'=>'-1','maxlen'=>'0')
		//,'ISSUENUMBER'=>array('type'=>'num','minlen'=>'-1','maxlen'=>'0')
		
		,'FIRSTNAME'=>array('type'=>''			,'minlen'=>'0','maxlen'=>'25','name'=>'First Name')
		,'LASTNAME'=>array('type'=>''			,'minlen'=>'0','maxlen'=>'25','name'=>'Last Name')
		,'STREET'=>array('type'=>''				,'minlen'=>'0','maxlen'=>'100','name'=>'Street Address')
		,'STREET2'=>array('type'=>''			,'minlen'=>'-1','maxlen'=>'100','name'=>'Street Address 2')
		,'CITY'=>array('type'=>''				,'minlen'=>'-1','maxlen'=>'40','name'=>'City')
		,'STATE'=>array('type'=>''				,'minlen'=>'-1','maxlen'=>'40','name'=>'State/Province')
		,'COUNTRYCODE'=>array('type'=>'code'	,'minlen'=>'2','maxlen'=>'2','name'=>'Country')
		,'ZIP'=>array('type'=>'zip'				,'minlen'=>'0','maxlen'=>'20','name'=>'Zip/Postal Code')
		,'PHONENUM'=>array('type'=>'phone'		,'minlen'=>'-1','maxlen'=>'20','name'=>'Phone of billing')
		,'EMAIL'=>array('type'=>'email'			,'minlen'=>'0','maxlen'=>'127','name'=>'Email Address')
		
		,'SHIPTONAME'=>array('type'=>''			,'minlen'=>'0','maxlen'=>'32','name'=>'Shipping [Name]')
		,'SHIPTOSTREET'=>array('type'=>''		,'minlen'=>'0','maxlen'=>'100','name'=>'Shipping [Street Address]')
		,'SHIPTOSTREET2'=>array('type'=>''		,'minlen'=>'-1','maxlen'=>'100','name'=>'Shipping [Street Address 2]')
		,'SHIPTOCITY'=>array('type'=>''			,'minlen'=>'0','maxlen'=>'40','name'=>'Shipping [City]')
		,'SHIPTOSTATE'=>array('type'=>''		,'minlen'=>'-1','maxlen'=>'40','name'=>'Shipping [State/Province]')
		,'SHIPTOZIP'=>array('type'=>'zip'		,'minlen'=>'0','maxlen'=>'20','name'=>'Shipping [Zip/Postal Code]')
		,'SHIPTOCOUNTRYCODE'=>array('type'=>'code','minlen'=>'0','maxlen'=>'2','name'=>'Shipping [Country]')
		,'SHIPTOPHONENUM'=>array('type'=>'phone','minlen'=>'-1','maxlen'=>'20','name'=>'Shipping [Phone]')
	);
	$errors = array();
	foreach ($fields as $key=>$type){
		$error = PP_Payment_ValidateField($key, $type, $vars);
		if ($error) $errors[] = $error;
	}
	if (!$errors){
		if (isset($vars['COUNTRYCODE']) && 
			(($vars['COUNTRYCODE'] == 'US' && $vars['STATE'] == '') 
			|| ($vars['COUNTRYCODE'] == 'CA' && $vars['STATE'] == ''))){
			$errors[] = "State/Province is required.";
		}
		if (isset($vars['SHIPTOCOUNTRYCODE']) && 
			(($vars['SHIPTOCOUNTRYCODE'] == 'US' && $vars['SHIPTOSTATE'] == '') 
			|| ($vars['SHIPTOCOUNTRYCODE'] == 'CA' && $vars['SHIPTOSTATE'] == ''))){
			$errors[] = "Shipping [State/Province] is required.";
		}
	}
	return $errors;
}

function formSelect($var_name, $selected, $options, $empty = "", $jscript="", $style="") {
	// Create and return an HTML <SELECT>	
	if ($jscript > "") { $jscript = " $jscript "; }
	if ($style > "") { $style = " style='$style'"; }
		
	$select = "<SELECT class='drpdown' NAME='$var_name'$jscript$style>\n";
	
	if ($empty > "") {
		$select .= "<OPTION VALUE=''>$empty</OPTION>\n";
	}
	
	foreach($options AS $value=>$option) {
		$select .= "<OPTION VALUE='$value'";
		
		if (is_array($selected)) {
			if (in_array($value,$selected)) {
				$select .= " SELECTED";
			}
		} else {
			if ($value == $selected) {
				$select .= " SELECTED";
			}
		}
		
		$select .= ">".htmlSafe($option)."</OPTION>\n";
	}

	$select .= "</SELECT>\n";
	return $select;
}
$state_select = array('AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','CA' => 'California','CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware','DC' => 'District of Columbia','FL' => 'Florida','GA' => 'Georgia','HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky','LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota','MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire','NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio','OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','RI' => 'Rhode Island','SC' => 'South Carolina','SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','VA' => 'Virginia','WA' => 'Washington','WV' => 'West Virginia','WI' => 'Wisconsin','WY' => 'Wyoming');

// CANADIAN PROVINCES
$province_select = array(
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NL' => 'Newfoundland and Labrador',
		'NT' => 'Northwest Territories',
		'NS' => 'Nova Scotia',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon Territory'
);
$country_select = array(
		"US" => "United States",
		"AU" => "Australia",
		"CA" => "Canada",
		"DE" => "Germany",
		"HK" => "Hong Kong",
		"IN" => "India",
		"ID" => "Indonesia",
		"IE" => "Ireland",
		"MY" => "Malaysia",
		"NZ" => "New Zealand",
		"RU" => "Russia",
		"ES" => "Spain",
		"GB" => "United Kingdom",
		"US" => "United States",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan Republic",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BB" => "Barbados",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BR" => "Brazil",
		"VG" => "British Virgin Islands",
		"BN" => "Brunei",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CA" => "Canada",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"CD" => "Democratic Republic of the Congo",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"SV" => "El Salvador",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands",
		"FO" => "Faroe Islands",
		"FM" => "Federated States of Micronesia",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"GA" => "Gabon Republic",
		"GM" => "Gambia",
		"DE" => "Germany",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GT" => "Guatemala",
		"GN" => "Guinea",
		"GW" => "Guinea Bissau",
		"GY" => "Guyana",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IE" => "Ireland",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Laos",
		"LV" => "Latvia",
		"LS" => "Lesotho",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"MN" => "Mongolia",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"AN" => "Netherlands Antilles",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"NO" => "Norway",
		"OM" => "Oman",
		"PW" => "Palau",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn Islands",
		"PL" => "Poland",
		"PT" => "Portugal",
		"QA" => "Qatar",
		"CG" => "Republic of the Congo",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russia",
		"RW" => "Rwanda",
		"VC" => "Saint Vincent and the Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"ST" => "Sao Tome and Principe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"KR" => "South Korea",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SH" => "St. Helena",
		"KN" => "St. Kitts and Nevis",
		"LC" => "St. Lucia",
		"PM" => "St. Pierre and Miquelon",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen Islands",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania",
		"TH" => "Thailand",
		"TG" => "Togo",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"UY" => "Uruguay",
		"VU" => "Vanuatu",
		"VA" => "Vatican City State",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"WF" => "Wallis and Futuna Islands",
		"YE" => "Yemen",
		"ZM" => "Zambia"
);
	
$credit_cards = array("Visa"=>"Visa","MasterCard"=>"MasterCard","Discover"=>"Discover","Amex"=>"American Express");


function htmlSafe($in_string) {
	return htmlspecialchars(stripslashes($in_string), ENT_QUOTES);
}
function expDateDropdown($varname, $selected = "") {
	// Dropdown for credit card expiration values
	$nowinfo = getdate();
	$nowyear = $nowinfo['year'];
	$nowmonth = $nowinfo['mon'];
	
	$html = "<select class='drpdown' name='$varname'>";

	for ($y = $nowyear; $y < $nowyear + 10; $y++) {
		for ($m = $nowmonth; $m < 13; $m++) {
			$m_val = str_pad($m, 2, "0", STR_PAD_LEFT);
			$y_val = $y;
			$d_val = $m_val.$y_val;
		
			$html .= "<option value='$d_val'";
			
			if ($d_val == $selected) {
				$html .= " SELECTED";
			}
	
			$html .= ">".$m_val."/".$y_val."</option>";
	
		}
		
		$nowmonth = 1;
	}
	
	$html .= "</select>";
	
	return $html;
	
}
function currency($a_number) {
    global $zp_vars;
    $a_number=floatval($a_number);
	$type = $zp_vars['currency_code'];
	if (!$type || $type == 'USD') $type='$';
	if ($type == 'EUR') $type='€';
	return "$type".number_format($a_number,2);
}

/* HTTP POST COMMON */
function HP_SendPost($url, $req) {
	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	// Set the request as a POST FIELD for curl.
	if ($req) curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

	// Get response from the server.
	$httpResponse = curl_exec($ch);
	$ret = array();
	$ret['error'] = array();
	if (curl_errno($ch)) {
		$_SESSION['curl_error_no']=curl_errno($ch) ;
		$_SESSION['curl_error_msg']=curl_error($ch);
		$ret['error'][] = "Can't connect to server";//exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		return $ret;
	} else {
		curl_close($ch);
	}
	if(!$httpResponse) {
		$ret['error'][] = "Can't connect to $url";//exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		return $ret;
	}	
	$ret['response'] = $httpResponse;
	return $ret;
}
function pp_zp_log($mess){
	//un-comment to enable logging into [pp_zp_log.log]
	//error_log( date('d.m.Y h:i:s') . "[pp_zp_log] $mess \n", 3, "pp_zp_log.log");
}
?>
