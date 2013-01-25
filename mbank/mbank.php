<?php

function exit_error() {
	exit("Error with mBank");
}
error_reporting(-1);
require_once('../localSettings.php');

function get_web($ch, $url) {
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,0);
	
	return curl_exec($ch);
}

function curl_prepare() {
	
	//Various curl settings
	$agent= 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_6) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7';
	
	$ch = curl_init();
	
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
	curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE); 
//	curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile"); 
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); 
//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	
	return $ch;
}


function get_login_parameters($mbank_result) {
	$res = find_state_validation($mbank_result, 1);
	//Get some needed parameters
	$found = preg_match(
		'/id="seed" value="([^"]+=)"/', 
		$mbank_result, 
		$matches);
	
	if ($found==0) {
		exit_error();
	}
	$res["seed"] = $matches[1];
	
	$res["localDT"] = date("D M d y H:i:s")." GMT 0100 (CET)";
	
	$res["customer"]="93535722";
	global $mbankpass;
	$res["password"]=$mbankpass;
	return $res;
}


function get_post($ch, $url, $data) {
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
	return curl_exec($ch);
}


function get_account_parameters($mbank_result) {
	$res = find_state_validation($mbank_result, 0);
	
	//hledam kod akce v mBance
	//hack - jde o ctvrty vyskyt tohoto stringu, to vim
	//ten ucet je 4 ucet v poradi ve vypisu
	
	$found = preg_match_all('/onclick="doSubmit\(\'\/account_oper_list.aspx\',\'\',\'POST\',\'([^\']*)\',false,false,false,null\); return false;" onmouseover="window.status = \'\'; return true;" href="#">Historie/', $mbank_result, $matches);
	
	$right = $matches[1][3];
	
	$res["__PARAMETERS"] = $right;
	return $res;
}

function get_list_parameters($mbank_result) {
	
	
	$res = find_state_validation($mbank_result, 1);
	
	$res["rangepanel_group"] = "lastdays_radio";
	
	//tohle je pocet MESICU, ne DNI!
	$res["lastdays_days"] = "5";
	$res["lastdays_period"] = "M";
	$res["accoperlist_typefilter_group"] = "ABO000000";
	$res["accoperlist_amountfilter_amountmin"]="";
	$res["accoperlist_amountfilter_amountmax"]="";
	$res["export_oper_history_check"]="";
	$res["export_oper_history_format"]="ABO";
	
	return $res;
}


function get_logged_mbank() {

    $res = array();
	$ch = curl_prepare();
	
	//nactu nutne veci ze stranky
	$html_mbank_website = get_web($ch, "https://cz.mbank.eu");
	
	$params = get_login_parameters($html_mbank_website);
	
	//naloguju se
	get_post($ch, "https://cz.mbank.eu/logon.aspx", $params);
	
	//zobrazim seznam accountu
	$html_mbank_list = get_web($ch, "https://cz.mbank.eu/accounts_list.aspx");
	
	//beru "spravny" account action
	$params = get_account_parameters($html_mbank_list);
	
	//zadam o formular k historii
	$html_oper_list = get_post($ch, "https://cz.mbank.eu/account_oper_list.aspx", $params);
	
	//beru si z nej ruzny kody
	$params = get_list_parameters($html_oper_list);
	
	//skutecne uz prichozi platby za posledni rok
	$ABO = get_post($ch, "https://cz.mbank.eu/printout_oper_list.aspx", $params);
	
	$platby = explode("\n", $ABO);
	
	//tohle neni off by one chyba
	//umyslne ignoruju prvni a posledni radek
	for ($i=1; $i<count($platby)-1; $i++) {
		$radek = $platby[$i];
		
		$castka = substr($radek, 48, 11);
		$var = substr($radek, 62, 9);
		
		//jestli existuje nekdo s castkou $castka a user id 220.ID, vsechno prodam
		//jinak pokud existuje s danou ID ale ne danou castka, zobrazim jako chybu
		//jinak ignoruju
		
		$castka = 0+$castka;
		$castka= intval($castka / 10);
		$var = 0+$var;
		
		
		echo "Cislo $i - $castka <- castka; $var <- var <br>";
		
		
		$should_be_330 = substr($var, 0, 3);
		
		//Jestli nezacina na "330", preskocit (potom)
		
		if ($should_be_330 != "330") {
			echo "$var variabilni symbol nezacina na 330, preskakuju<br>";
			/*if ($castka==4000) {
				echo "TOMU TO DAM<br>";
				$var = "2201503";
			} else {*/
				continue;
		//	}
		}
		
		
		$should_be_id = substr($var, 3);
		$res[$should_be_id] = $castka;

		
	}

	
	return $res;
	
}

function find_state_validation($mbank_result, $event_validation) {
	$found = preg_match(
		'/id="__STATE" value="([^"]+)"/', 
		$mbank_result, 
		$matches);
	
	if ($found==0) {
		var_dump($mbank_result);
		exit_error();
	}
	
	$res["__STATE"] = $matches[1];
	
	if ($event_validation == 1) {
	
		$found = preg_match(
			'/id="__EVENTVALIDATION" value="([^"]+)"/', 
			$mbank_result, 
			$matches);
	
		if ($found==0) {
			exit_error();
		}
		$res["__EVENTVALIDATION"] = $matches[1];
	}
	
	$res["__PARAMETERS"]="";
	$res["__VIEWSTATE"]="";
	return $res;
}

?>
