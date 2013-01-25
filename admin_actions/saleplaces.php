<?php


function admin_action_saleplaces_do($seat_array, $id_spojeni, $dbPrefix, $hall_ID, $userType) {
    if ($userType != 'A') {
    	    return array("", null, 1, "Nejsi admin!");
    }
    
    $pocet = 0;
  	$seat_prefix = 'seat_';
  	$pref_len = strlen($seat_prefix);
  	$choosen = array();
    foreach($seat_array as $key => $value)
  	{
  		if ($value == 1)
  		{	
  			$n = substr($key, $pref_len);
  			$choosen[$n] = 1;
  			$pocet++;
  		}
  	}
  	
  	$sql_query =
  		"SELECT seat_number, hall_ID FROM ${dbPrefix}Seats 
  		WHERE status = 'L' AND hall_ID=$hall_ID; ";
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
  	{
  	    return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  

  	$already_reserved = array();
  	while ( $arr = mysql_fetch_array ( $vysledek ))
  	{
  		if ($arr['hall_ID'] != $hall_ID)
  		{

  		}
  		else
  		{
  			$n = $arr['seat_number'];
  			$already_reserved[$n] = 1;
  		}
  	}
  	
  	
  	
  	$sql_query = "
      SELECT S.seat_number, T.label table_label
      FROM ${dbPrefix}Seats S LEFT JOIN ${dbPrefix}Tables T ON S.table_number = T.table_number
		    AND  S.hall_ID = T.hall_ID
      WHERE S.hall_ID = $hall_ID";
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
  	{
  	    return array("", null, 1, "SQL error ".$sql_query);
  	}
  
    // vsechny nove rezervovane vstupenky budou mit stejne datum a cas rezervace
    //
    $now = Time();
    

  	
  	$res = "<div style=\"background-color:white;text-align:center; padding:20px;\">";
  	$res .= "<table class=\"table\">\n";
  	$res .= ' <thead><tr><th>Místo</th><th>Stůl</th><th>Stav</th></tr></thead>';
  	while ( $row = mysql_fetch_array ($vysledek) )
  	{
  		$number = $row['seat_number'];
        $nazev = $number." <i>(stůl ".$row['table_label'].")</i>";
  		if ( $_POST ["seat_$number"] == 1 )
  		{
  			if ( array_key_exists($number, $already_reserved) && $already_reserved[$number] == 1 )
  			{
  				// sedadlo je zaskrtle, patri vsak jiz knihovne
  				$res .= "<tr style=\"color:black;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zůstává rezervovaná.</td></tr>\n";
  			}
  			else
  			{		
  				//uzivatel toto policko zaskrtl jako nove
  				$sql_query = "UPDATE ${dbPrefix}Seats SET status = 'L'
  					WHERE hall_ID = $hall_ID AND seat_number = $number AND status = 'A'
  					LIMIT 1";
  
  				$sqlres = mysql_query ( $sql_query, $id_spojeni );
  				if ( $sqlres == false )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>Převod na knihovnu se nezdařil $sql_query</td></tr>\n";
  					
  				}
  				else if ( mysql_affected_rows($id_spojeni) == 0 )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>Převod na knihovnu nezdařil (lístek již nebyl volný)</td></tr>\n";
  						
  				}
  				else
  				{
  				    $res .= "<tr style=\"color:green;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>Převod na knihovnu se zdařil</td></tr>\n";
  				}
  			}
  		} // fi ($_POST ["seat_$number"] == 1)
  
  		else //rusim podminku 
  		{
  			// toto policko neni zaskrtle - tuto rezervaci zrusit
  			//
  			if (  array_key_exists($number, $already_reserved) && $already_reserved[$number] == 1 )
  			{
  			$sql_query = "UPDATE ${dbPrefix}Seats SET status = 'A'
  					WHERE hall_ID = $hall_ID AND seat_number = $number  AND status = 'L'
  					LIMIT 1";
  					
  				$sqlres = mysql_query ( $sql_query, $id_spojeni );
  				if ( $sqlres == false )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení převodu na knihovnu se nezdařilo $sql_query</td></tr>\n";
  					
  					log_write("DB_ERROR [8] - $sql_query");
  				}
  				else if ( mysql_affected_rows($id_spojeni) == 0 )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení převodu na knihovnu se nezdařilo (vstupenka již nebyla rezervována)</td></tr>\n";
  				    
  				}
  				else
  				{
  				    $res .= "<tr style=\"color:green;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení převodu na knihovnu proběhlo v pořádku</td></tr>\n";
  				   
  				
  				
  				}//fi else $sqlres 
  			} //fi $already_reserved[$number] == 1	
  		
  		}	//fi $remove_other == "yes" 
  		
  	}	//endwhile
  	$res.= "</table>\n";
  	
  	
  	

  	$res .= "<input type=\"button\" value=\"Pokračovat\" onclick=\"onNavigate('form-menu', 'admin_saleplaces_pre');\" class='btn btn-success'/>";
  	$res .= "</div>\n";
  	$res .= "<div style=\"width:100%;text-align:center;\">";
  
  
  	$res .= "</div>  <!-- #data -->\n";
  	
  	
  	
  	return array($res, "", 0, "");
    
}

function admin_action_saleplaces_pre($id_spojeni, $dbPrefix, $userType) {
    if ($userType != 'A') {
    	    return array("", null, 1, "Nejsi admin!");
    }

    $res = "<h3>Upravit rozdělení na prodejná a v knihovnách</h3>";
    
    $res .= "<p>(Pro \"rezervu\" použít VIP sekci.)</p>";
    
    $res .= ' <table class="table table-striped table-hover"><thead><tr><th>Místo</th><th>Míst dostupných volně</th><th>Míst dostupných v knihovnách</th></tr></thead><tbody>';
    
    $sql_query="
                SELECT 
        H.name AS hall_name, H.id AS hall_id,
        
        (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = H.id AND T.status = 'A') available,
        (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = H.id AND T.status = 'L') libraries
                FROM ".$dbPrefix."Hall H
        INNER JOIN ".$dbPrefix."Hall_type HT ON H.hall_type_ID = HT.id
        ORDER BY HT.order, H.order";
        
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    if ( $vysledek == FALSE ) {
        return array("", null, 1, "Chyba. $sql_query");

    }
    else {
        while ( $arr = mysql_fetch_array($vysledek)) { 
            $id = $arr['hall_id'];
            $name = $arr['hall_name'];
            $ava = $arr['available'];
            $libraries = $arr['libraries'];
            
            
             $onclick_event = 
                    "document.forms['form-menu'].par1.value=$id;"
                    ."document.forms['form-menu'].act.value='admin_saleplaces_map';"
                    ."document.forms['form-menu'].submit();"
                    ."return false;";
            
            $res .= "<tr onclick=\"$onclick_event\" style=\"cursor: pointer;cursor: hand\"><td><a href=\"\">".$name.'</a></td><td>'.$ava.'</td><td>'.$libraries.'</td>';
            
        }
    }
    $res .= '</tbody></table>';
    
    return array($res, "", 0, "");
       
}

?>
