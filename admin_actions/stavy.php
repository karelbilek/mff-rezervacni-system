<?php


function beginsWith( $str, $sub ) {
    return ( substr( $str, 0, strlen( $sub ) ) == $sub );
}

function date_to_javascript($timestamp) {
    return "new Date( 2013, ".StrFTime ("%m", $timestamp)."-1, ".StrFTime ("%d", $timestamp).", ".StrFTime ("%k", $timestamp).",".StrFTime ("%M", $timestamp).",".StrFTime ("%S", $timestamp).", 0)";
}

$idtimer=0;
function timerSince($timestamp) {
    global $idtimer;
    $idtimer++;
    $res = '<span id="timer'.$idtimer.'"></span>';
    
    $res .= '<script>$(document).ready(function() {
		$("#timer'.$idtimer.'").countdown({since: '.date_to_javascript($timestamp).'});
	});</script>';
	return $res;
}

function timerUntil($timestamp) {
    global $idtimer;

    $idtimer++;
    $res = '<span id="timer'.$idtimer.'"></span>';
    
    $res .= '<script>$(document).ready(function() {
		$("#timer'.$idtimer.'").countdown({until: '.date_to_javascript($timestamp).'});
	});</script>';
	return $res;
}


function to($inet) {
    if ($inet=='A') {
        return "volné";
    }
    if ($inet=='L') {
        return "knihovny";
    }
    if ($inet=='R') {
        return "rezervace";
    }
    if ($inet=='S') {
        return "prodané";
    }
    if ($inet=='V') {
        return "VIP";
    }
}

function action_stavy($user_type, $id_spojeni, $dbPrefix) {
    if ($user_type!='A') {
            return array("Nejsi admin!!1", null, 0, "");
    }
    
    $res="";
    
    
    $sql_query= "SELECT status, COUNT(status) AS count
FROM ".$dbPrefix."Seats
GROUP BY status";
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
    if ( $vysledek == false )
    {
  		return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  	
    $res .= '<h3>Statistika stavů lístků</h3>';
  	$res .= '<table border=1>';
  	while ($arr = mysql_fetch_array ( $vysledek )) {
  	    $res.='<tr>';
  	    $res.='<td>'.to($arr['status']).'</td><td>'.$arr['count'].'</td>';
  	    
  	    
  	    $res .= '</tr>';
  	}
  	$res .= '</table>';
  	
  	
  	
  	
  	
  	$sql_query = "
      SELECT
         H.name, U.login, U.address, U.firstname, U.lastname, T.label, unix_timestamp(S.reserv_dt) AS reserv_dt, unix_timestamp(S.reserv_to_dt) AS reserv_to_dt
      FROM ${dbPrefix}Hall H

        INNER JOIN ${dbPrefix}Seats S ON S.hall_ID = H.id 
        INNER JOIN ${dbPrefix}User U ON U.id = S.customer_id 
        INNER JOIN ${dbPrefix}Tables T ON T.hall_id = H.id AND T.table_number=S.table_number 
      WHERE S.status = 'V'
      ORDER BY H.id, T.table_number";
      
      
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
    {
  		return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  	
  	$res .= '<script type="text/javascript" src="js/jquery.countdown.js"></script>';
	$res.= '<script type="text/javascript" src="js/jquery.countdown-cs.js"></script>';
  	$res .= '<h3>Všechny VIP rezervace</h3>';
  	$res .= '<table class="table table-striped table-condensed">'.
  	        '<thead><tr><th>.</th><th>Sál</th><th>Stůl</th><th>Jméno</th><th>Proběhla před</th></tr></thead><tbody>';
  	$i=0;
  	while ($arr = mysql_fetch_array ( $vysledek )) {
  	    $i++;
  	    $res.='<tr><td>'.$i.'</td><td>'.$arr['name'].'</td><td>'.$arr['label'].'</td><td>'.$arr['firstname'].'</td>';
  	    
  	    $res .= '<td>'.timerSince($arr['reserv_dt']).'</td>';
  	    
  	    //$res .= '<td>'.timerUntil($arr['reserv_to_dt']).'</td>';
  	    
  	    //StrFTime ("%d.%m.%Y, %H:%M", $arr['reserv_to'])
  	    
  	    
  	    $res.='</tr>';
  	}
    $res .= '</tbody></table>';
    
    
    
  	
  	
  	
    
    $sql_query = "
      SELECT
         H.name, U.login, U.address, U.firstname, U.lastname, T.label, unix_timestamp(S.reserv_dt) AS reserv_dt, unix_timestamp(S.reserv_to_dt) AS reserv_to_dt
      FROM ${dbPrefix}Hall H

        INNER JOIN ${dbPrefix}Seats S ON S.hall_ID = H.id 
        INNER JOIN ${dbPrefix}User U ON U.id = S.customer_id 
        INNER JOIN ${dbPrefix}Tables T ON T.hall_id = H.id AND T.table_number=S.table_number 
      WHERE S.status = 'R'
      ORDER BY H.id, T.table_number";
      
      
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
    {
  		return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  	
  	$res .= '<script type="text/javascript" src="js/jquery.countdown.js"></script>';
	$res.= '<script type="text/javascript" src="js/jquery.countdown-cs.js"></script>';
  	$res .= '<h3>Všechny rezervace</h3>';
  	$res .= '<table class="table table-striped table-condensed">'.
  	        '<thead><tr><th>.</th><th>Sál</th><th>Stůl</th><th>Uživatel</th><th>Mail</th><th>Jméno</th><th>Příjmení</th><th>Proběhla před</th><th>Vyprší za</th></tr></thead><tbody>';
  	$i=0;
  	while ($arr = mysql_fetch_array ( $vysledek )) {
  	    $i++;
  	    $res.='<tr><td>'.$i.'</td><td>'.$arr['name'].'</td><td>'.$arr['label'].'</td><td>'.$arr['login'].'</td><td>'.$arr['address'].'</td><td>'.$arr['firstname'].'</td><td>'.$arr['lastname'].'</td>';
  	    
  	    $res .= '<td>'.timerSince($arr['reserv_dt']).'</td>';
  	    //die($arr['reserv_to_dt']);
  	    $res .= '<td>'.timerUntil($arr['reserv_to_dt']).'</td>';
  	    
  	    //StrFTime ("%d.%m.%Y, %H:%M", $arr['reserv_to'])
  	    
  	    
  	    $res.='</tr>';
  	}
    $res .= '</tbody></table>';
    
    
    
    
    $sql_query = "
      SELECT
         H.name, U.login, U.address, U.firstname, U.lastname, T.label, unix_timestamp(S.sold_dt) AS sold_dt
      FROM ${dbPrefix}Hall H

        INNER JOIN ${dbPrefix}Seats S ON S.hall_ID = H.id 
        INNER JOIN ${dbPrefix}User U ON U.id = S.customer_id 
        INNER JOIN ${dbPrefix}Tables T ON T.hall_id = H.id AND T.table_number=S.table_number 
      WHERE S.status = 'S'
      ORDER BY H.id, T.table_number";
      
      
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
    {
  		return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  	
  	$res .= '<h3>Všechny prodané lístky</h3>';
  	$res .= '<table class="table table-striped table-condensed">'.
  	        '<thead><tr><th>.</th><th>Sál</th><th>Stůl</th><th>Uživatel</th><th>Mail</th><th>Jméno</th><th>Příjmení</th><th>Prodáno před</th></tr></thead><tbody>';
  	$i=0;
  	while ($arr = mysql_fetch_array ( $vysledek )) {
  	    $i++;
  	    $res.='<tr><td>'.$i.'</td><td>'.$arr['name'].'</td><td>'.$arr['label'].'</td><td>';
  	    
  	    if (beginsWith($arr['login'], 'volny')) {    
      	    $res .= '<i>volny</i>';
      	    $arr['firstname'] = "<i>volný</i>";
      	    $arr['lastname'] = "<i>prodej</i>";
      	} else {
            $res .= $arr['login'];
      	}
  	    
  	    $res.='</td><td>'.$arr['address'].'</td><td>'.$arr['firstname'].'</td><td>'.$arr['lastname'].'</td>';
  	    
  	    $res .= '<td>'.timerSince($arr['sold_dt']).'</td>';
  	    
  	    
  	    //StrFTime ("%d.%m.%Y, %H:%M", $arr['reserv_to'])
  	    
  	    
  	    $res.='</tr>';
  	}
    $res .= '</tbody></table>';
    

  	
    
    return array($res, null, 0, "");
}

?>
