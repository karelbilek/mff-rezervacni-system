<?php

$CHOSEN_COLOR = 'red';

//TODO:put elsewhere
$TICKET_RESERVED = 'R';
$TICKET_AVAILABLE = 'A';


function napis_button($reserveVIP, $reserveLibraries){

    if ($reserveLibraries) {
        return "Nastavit knih. místa";
    }

    if ($reserveVIP) {
        return "Rezervovat VIP";
    }
    return "Rezervovat";
}

function next_action_button($reserveVIP, $reserveLibraries) {
    if ($reserveLibraries) {
        return "admin_saleplaces_do";
    }
    
    if ($reserveVIP) {
        return "admin_reserve_vip";
    }
    return "reserve";
}

//funkce, ktera resi, jak se ma vykreslovat zidlicka a co delat po kliknuti
function what_to_do($status, $price, $can_reserve,$seat_number, $reservation_customer_id, $user_to_change, $reserved_user_type, $user_type, $reserveLibraries) {



    global $TICKET_RESERVED;
    global $TICKET_AVAILABLE;
    
    if ($reserveLibraries) {
        if ($status=='A') {
            return array('yellow', 'Cena: '.$price, true, 
                "change_image('seat_$seat_number',$price, 'yellow', $can_reserve);", false); 
        }
        if ($status=='L') {
            return array('red', 'Cena: '.$price, true, 
                "change_image('seat_$seat_number',$price, 'yellow', $can_reserve);", true); 
        }
	    return array('DarkGray', 'Prodáno či rezervováno', false, "", false);
    }

    
    //$mapa .= "<div $seat_attr style=\"position:absolute; top:${y}px; left:${x}px; cursor:Pointer;background-color:${cl};\" onclick=\"change_image('seat_$seat_number',$price,'${cl}', $can_reserve);\" title=\"Cena: $price\"/>$seat_label</div>\n";
    
    //vysledek:
    //color, titulek, jefunkce, jakafunkce, increaseCounter
    
    if ($status == $TICKET_AVAILABLE || ($user_type=='S' && $status=='L')) {
        return array('yellow', 'Cena: '.$price, true, 
            "change_image('seat_$seat_number',$price, 'yellow', $can_reserve);", false);
    }
    
    if ($status=='L') {
        return array('#99FFFF', 'Dostupné jen v knihovnách', false, "", false);
    }
    
    if ($status == $TICKET_RESERVED || $status=='V') {
    

	    if ($reservation_customer_id == $user_to_change) {
	        return array('red', 'Cena: '.$price, true, 
                "change_image('seat_$seat_number',$price, 'yellow', $can_reserve);", true);
        } else  { 
               //VIP rezervovany se tvari jako prodany, pokud to neni samotneho uzivatele
            if ($reserved_user_type == 'V') {
                return array('DarkGray', 'Prodáno', false, "", false);
            }
    
            return array('PaleGreen', 'Rezervováno', false, "", false);
        }
	}
	//$mapa .= "<div $seat_attr style=\"background-color: DarkGray; position:absolute; top:${y}px; left:${x}px;\" title=\"Prodáno\" />$seat_label</div>\n";	
	return array('DarkGray', 'Prodáno', false, "", false);
    
    
}



function action_display_reservations($hall_ID, $user_to_change, $id_spojeni, $dbPrefix, $session_key, $user_id, $user_type, $reserveVIP, $reserveLibraries) {

    

    global $RESERV_OK;
    global $TICKET_COUNT_LIMIT;
    
    if ($reserveVIP && ($user_type != 'A')) {
        return array("", null, 1, 
          "Jen admin smí spravovat VIP.");
    }
    
    if ($reserveLibraries && ($user_type != 'A')) {
        return array("", null, 1, 
          "Jen admin smí spravovat VIP.");
    }
    
    
    if (!($reserveVIP|| $reserveLibraries||$user_type=='P')) {
        $limit = $TICKET_COUNT_LIMIT;
    } else {
        $limit=100000;
    }
    
    $sql_query = "
      SELECT
       unix_timestamp(HT.reserv_from_dt) AS ht_reserv_from_dt,
         unix_timestamp(HT.reserv_to_dt) AS ht_reserv_to_dt
      FROM ${dbPrefix}Hall H
        INNER JOIN ${dbPrefix}Hall_type HT ON H.hall_type_ID = HT.id 
      WHERE H.id = $hall_ID";
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
    {
  		return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  	
  	
  	
  	$arr = mysql_fetch_array ( $vysledek );
  	
  	$ht_reserv_to_dt = $arr['ht_reserv_to_dt'];
  	$ht_reserv_from_dt = $arr['ht_reserv_from_dt'];
    
  	
    
    $can_reserve;
    $tc = reservationTimeCheck($user_type, $ht_reserv_from_dt, $ht_reserv_to_dt);
  	if ($tc!=$RESERV_OK) {
  	   $can_reserve=0;
  	} else {
      	$can_reserve = 1;    
    }

    $choosen_color='red';

    $res = "";
    
    $res .= '<script type="text/javascript" src="js/check_seats_js.php?limit='.$limit.'"></script>';

        $res .= "<form name=\"mapa\" action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"post\">";
    
    // je-li prihlasen prodejce a v user_to_change je radne vyplneno cislo zakaznika, 
    // zobrazi se rezervace zakaznika. V jakemkoliv jinem pripade se zobrazi
    // rezervace aktualne prihlaseneho uzivatele
    
    if ($user_type == 'C' || $user_to_change == "" || ! is_numeric($user_to_change) ) {

        $user_to_change = $user_id;

    }    
    

    
    if ( $hall_ID == "" || ! is_numeric($hall_ID) ) {
        $error=1;
        return array("", null, 1, "Invalid parameter value.");

    } 

    /*
    $query = "SELECT 
			C.hall_ID, C.x, C.y, C.number
		FROM ".$dbPrefix."Chairs C
		WHERE C.hall_ID = $hall_ID;";


    $vysl = mysql_query ($query, $id_spojeni);
    if ( $vysl == FALSE) {
        log_write("DB_ERR[1]", "generate_map");

	    return array("", null, 1, "!Error reading database.");
    }

    while ( $radka = mysql_fetch_row ( $vysl ))
    {
	    $sn = $radka[3];
	    $souradnice_x [ "$sn" ] = $radka[1];
	    $souradnice_y [ "$sn" ] = $radka[2];
	    //barva a lze_rez je vzdycky totez :)
	    //$barva        [ "$sn" ] = $radka[4];
	    //$lze_rez      [ "$sn" ] = $radka[5];
    }*/
    
    $sql_query = "SELECT width,height FROM ".$dbPrefix."Hall WHERE id=$hall_ID";
    
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    if ( $vysledek == FALSE ) {
        log_write("DB_ERROR[5]", "GetRoomObjects");

	    return array("", null, 1, "?Error reading database. $sql_query");
    }
    $row = mysql_fetch_array ( $vysledek );
    
    $mapa ="<div id='generovanaMapa' class='well' style='width:".$row['width']."px; height:".$row['height']."px;'>";
    
    //-------generovani sten
    $sql_query = "SELECT x,y,width,length,text FROM ".$dbPrefix."Walls WHERE hall_ID=$hall_ID";
    
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    if ( $vysledek == FALSE ) {
        log_write("DB_ERROR[5]", "GetRoomObjects");

	    return array("", null, 1, "?Error reading database.");
    }
    
    

    
    while ( $row = mysql_fetch_row ( $vysledek )) {
	    $x = $row[0];
	    $y = $row[1];
	    $width = $row[2];	
	    $length = $row[3]; 
	    $text = $row[4];	//text steny

	    $mapa .= "<div title=\"$text\" class=\"wall\" style=\"position:absolute;left:${x}px;top:${y}px;width:${width}px;height:${length}px;border-width:0px; color:white; background-color:black\">$text</div>\n";
		
    }
	    
	//-------generovani stolu
	$sql_query = "SELECT * FROM ".$dbPrefix."Tables WHERE hall_ID=$hall_ID";
	
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    
    if ( $vysledek == FALSE ) {
        log_write("DB_ERROR[6]", "GetRoomObjects");
	    return array("", null, 1, ".Error reading database.");
    }
    
    
    while ( $row = mysql_fetch_array ( $vysledek )) {
	    //var_dump($row);
	    $type 	= $row['type'];
	    $x		= $row['x'];
	    $y		= $row['y'];
	    $width	= $row['width'];	
	    $length = $row['length']; 
	    $number = $row['table_number'];
	    $strana = $row['radius'] / sqrt(2);
	    $cl     = 'gray';
	    $label  = $row['label']; 
		
		if ($label == '') {
		    $label = $number;
		}
		 
    	if ( $type == "rectangular" ) {	
		    $mapa .= "<div title=\"Table: $label\" class=\"tablestul\" style=\"position:absolute;left:${x}px;top:${y}px;width:${width}px;height:${length}px;background-color:${cl};\">$label</div>\n";
	    } else	//round
	    {
		    $strana -=1;
		    $x -= $strana  ;
	    	$y -= $strana  ;
		    $strana *= 2 ;
		$mapa .= "<!---round---><div title=\"Table: $label\" class=\"tablestul\" style=\"position:absolute;left:${x}px;top:${y}px;width:${strana}px;height:${strana}px;background-color:${cl};\">$label</div>\n";
	    }	
    }
    
    //-----------generovani zidli
    $sql_query = 
         "SELECT S.seat_number, S.label, S.status, S.customer_ID, S.x, S.y, HT.price, USR.status AS usrstatus"
        ." FROM ".$dbPrefix."Seats S "
        ." LEFT JOIN ".$dbPrefix."Hall H ON S.hall_ID = H.id"
        ." LEFT JOIN ".$dbPrefix."User USR ON USR.id = S.customer_ID"
        ." LEFT JOIN  ".$dbPrefix."Hall_type HT ON HT.id = H.hall_type_ID
        WHERE  H.id = ${hall_ID};";
    	$vysledek = mysql_query ($sql_query, $id_spojeni );
    	if ( $vysledek == FALSE )
    	{
     	    log_write("DB_ERR[2] - $sql_query", "generate_map");
     	    return array("", null, 1, "Error reading database! ".$sql_query);
    	}
    	

    	
    	//choosen.... omg
    	//ale nechavam, aby se neco nepodelalo
        $choosen_count = 0;
        $choosen_price = 0;

    	while ($row = mysql_fetch_array ( $vysledek) )
    	{
    		$seat_number = $row ['seat_number'];
    		$seat_label = $row ['label']; 
    		if ($seat_label == '') $seat_label = $seat_number;
    		$status = $row ['status'];
    		$price = $row ['price'];
    		$usrstatus = $row ['usrstatus'];
    		
    		$reservation_customer_id = $row ['customer_ID'];
    		
    		$x = $row [ "x"];	//x-ova souradnice zidle
    		$y = $row [ "y"];	//y-ova souradnice zidle
    		$cl= 'yellow';  //barva zidle
    		//$y +=$relativni_posun;
    		//$lze_rezervovat = $lze_rez ["$seat_number"];
    		$choosen = 0;
    
    		//vygenerovat absolutne pozicovany obrazek
    		//
    		$seat_attr = "id=\"idseat_$seat_number\" name=\"seat_$seat_number\" class=\"seat\" ";
    		
    		list ($color, $titulek, $jefunkce, $jakafunkce, $isChoosen) = what_to_do($status, $price, $can_reserve, $seat_number, $reservation_customer_id, $user_to_change, $usrstatus, $user_type, $reserveLibraries);
    		
    		if ($jefunkce) {
    		    $mapa .= "<div $seat_attr style=\"position:absolute; top:${y}px; left:${x}px; cursor:Pointer;background-color:${color};\" onclick=\"${jakafunkce}\" title=\"${titulek}\"/>$seat_label</div>\n";
    		} else {
    		    $mapa .= "<div $seat_attr style=\"position:absolute; top:${y}px; left:${x}px;background-color:${color};\" title=\"${titulek}\"/>$seat_label</div>\n";
    		}
    		if ($isChoosen) {
    		    $choosen = 1;
    		    $choosen_count += 1;
                $choosen_price += $price; 
    		}
    		
            $mapa .= "<input type=\"hidden\" name=\"seat_$seat_number\" value=\"$choosen\" />\n";
    	}
    	
    	
	    $mapa.="</div>";#gerenerovanaMapa
	
	


	
	$table= "<div class='well' style='width:250px'>".
	//"<form class='form-horizontal'>".
	'<div class="control-group">
    <label class="control-label" for="count">Počet vstupenek</label>
    <div class="controls">
      <input type="text" id="count" name="count" value="'.$choosen_count.'" readonly="readonly" maxlength="3"/> 
    </div>
    </div>
    
    <div class="control-group">
    <label class="control-label" for="price">Celková cena</label>
    <div class="controls">
      <input type="text" id="price" name="price" value="'.$choosen_price.'" readonly="readonly" maxlength="3"/> 
    </div>
    </div>
    
    <div class="control-group">
    <div class="controls">
      
      <input  type="submit" value="'.napis_button($reserveVIP, $reserveLibraries).'" maxlength="5" onclick="return check_seats()" class="btn btn-success"/>
    </div>
  </div><!--control group-->
    <!--/form-->
    
    </div><!---well-->
	
	';
    	
    	
    	/*
    	$table= "<table> <tr> <td>\n";
    	
    	$table.= "<div style=\"background-color:white;margin:auto;padding:10px;width:400px; \">\n";
    	$table.= "<table style=\"text-align:center; margin:auto;\">\n";
    	$table.= "<tr><td colspan=\"3\">Vyberte vstupenky a stiskněte Rezervovat</td></tr>\n";
    	$table.= "<tr><td>Počet vstupenek:</td><td>Celková cena:</td></tr>\n";
    	$table.= "<tr><td><input style=\"width:40px;margin-left:50px;margin-right:50px;\" type=\"text\" name=\"count\" value=\"$choosen_count\" readonly=\"readonly\" maxlength=\"3\"/></td><td><input style=\"width:60px;margin-left:50px;margin-right:50px;\" type=\"text\" name=\"price\" value=\"$choosen_price\" readonly=\"readonly\"/></td><td><input  type=\"submit\" value=\"Rezervovat\" maxlength=\"5\" onclick=\"return check_seats()\" /></td></tr>\n";
    	$table.= "</table>\n";
    	
    	$table.= "</div>\n";
    	
    	$table.= "</td>\n";
    	$table.= "<td style=\"padding-left:20px;\">\n";
    		
    	$table.= "<div style=\"background-color:white;margin:auto;padding:10px; width:400px;\">\n";
    	#$res.= "<table style=\"text-align:center; margin:auto;\">\n";
    	
    	
    	$table .= "</div>\n";
    	$table .= "</td></tr>\n";
    	$table .= "</table>\n\n";*/
    	$res .= $table;
    	$res .= $mapa;
    	
    	
    	$other_count=0;
        $sql_query = "SELECT * FROM ".$dbPrefix."Seats WHERE status='R' AND customer_ID=$user_to_change AND hall_ID!=$hall_ID";
        
        $vysledek = mysql_query ( $sql_query, $id_spojeni );
    
        if ( $vysledek == FALSE ) {
            log_write("DB_ERROR[6]", "GetRoomObjects");
	        return array("", null, 1, ".Error reading database.".$sql_query);
        }
        
    
    
    while ( $row = mysql_fetch_array ( $vysledek )) {
        $other_count++;
    }
    	
    	// zapis do formulare pocet vstupenek rezervovanych v jinych salech
    	//
    	$res .= "<input type=\"hidden\" name=\"other_count\" value=\"$other_count\" />\n";	

    	// predej stavove promenne - akci, id session, event id (par1) 
      // prip. cislo zakaznika, se kterym pracuje prodejce (par2)
    	//
    	$res .= "<input type=\"hidden\" name=\"act\" value=\"".next_action_button($reserveVIP, $reserveLibraries)."\" />\n";
        $res .= "<input type=\"hidden\" name=\"sid\" value=\"$session_key\" />\n";
        $res .= "<input type=\"hidden\" name=\"par1\" value=\"$hall_ID\" />\n";
        $res .= "<input type=\"hidden\" name=\"par2\" value=\"$user_to_change\" />\n";
    	 $res .= "<input type=\"hidden\" name=\"par3\" value=\"no\" />\n";
    	
    	//konec formulare
    	$res .= "</form>\n";
/**/
    
     return array($res, null, 0, "");
}

?>
