<?php




function action_counts_on_homepage($id_spojeni, $dbPrefix, $logged_in, $user_type) {

        global $RESERV_TOO_SOON, $RESERV_TOO_LATE,$RESERV_OK;

        
        
        $orderby = "HT.order, H.order";

        $sql_query="
                SELECT 
        H.name AS hall_name, HT.name AS hall_type, H.id AS hall_id,
        unix_timestamp(HT.reserv_from_dt) reserv_from_dt, unix_timestamp(HT.reserv_to_dt) reserv_to_dt,
        (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = H.id AND T.status = 'A') available,
        (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = H.id AND T.status = 'L') avlib
                FROM ".$dbPrefix."Hall H
        INNER JOIN ".$dbPrefix."Hall_type HT ON H.hall_type_ID = HT.id
        ORDER BY $orderby";
        
        $vysledek = mysql_query ( $sql_query, $id_spojeni );
        if ( $vysledek == FALSE ) {
                $msg= ("Error reading Events from database.");
                $error = 1;
        }
        else {

            $out = "<table class = 'table table-striped table-hover'>";
            $out .= ' <thead><tr><th>Typ lístků</th><th>Sál</th><th>K dispozici?</th></tr></thead><tbody>';
            
            $first = 1;
            $last_type = "";
            $comment = "";

            while ( $arr = mysql_fetch_array($vysledek)) { 
                $hall_type = $arr['hall_type'];

          
                /*if ($hall_type != $last_type) {
                    $last_type = $hall_type;
                    
                    if ( $first == 1 ) {
                        $first = 0;
                    } else {
                        $out .= "<hr/>\n";
                    }
                    
                    $out .= "<h3>".$arr['hall_type']."</h3>";
                }   */ 


                $available_count = $arr['available'];
                $avlib = $arr['avlib'];
                $reserv_from_dt = $arr['reserv_from_dt'];
                $reserv_to_dt = $arr['reserv_to_dt'];

                // je-li mozne vstupenky na akci rezervovat (nebo ma uzivatel 
                // nejako specialni vyjimku), zobraz pocet volnych
                // jinak vypis duvod proc nelze rezervovat
                $timeCheckRes=reservationTimeCheck($user_type, $reserv_from_dt, $reserv_to_dt);
                
                if ($timeCheckRes == $RESERV_TOO_LATE) {
                    // konec rezervaci plati jen pro zakazniky
                    $comment = "online rezervace ukončeny ($avlib u prodejců)";
                    $allow_reserv = false;
                } else if ($timeCheckRes == $RESERV_OK) {
                    $comment = "<span style=\"color: ".($available_count == 0 ? "red" : "green")."; \">"
                        ."volných $available_count</span> (+$avlib u prodejců)";
                    $allow_reserv = true;
                } else {
                    $comment = "rezervace od ".StrFTime ("%d.%m.%Y, %H:%M", $reserv_from_dt)." (".timerUntil($reserv_from_dt).")";
                    $allow_reserv = false;
                }                  

                if (!$logged_in) {
                    $allow_reserv = false;
                }
                
                
                if (!$allow_reserv) {
                          $out .='<tr>';
                          $out .= '<td>'.$arr['hall_type'].'</td>';
                          $out .= '<td>'.$arr['hall_name']."</td><td> $comment</td>\n";
                } else {
                 //onNavigate('form-menu', 'display_reservation', $hall_ID);
                          $onclick_event = 
                    "document.forms['form-menu'].par1.value=".$arr['hall_id'].";"
                    ."document.forms['form-menu'].act.value='display_reservation';"
                    ."document.forms['form-menu'].submit();"
                    ."return false;";
                    
                    $out .="<tr onclick=\"$onclick_event\" style=\"cursor: pointer;cursor: hand\">";
                          $out .= '<td>'.$arr['hall_type'].'</td>';
                
                          $out .= /*"$date_str – */
                                "<td><a href=\"\" "./*onclick=\"$onclick_event\". */"alt=\"Změnit rezervaci\">"
                                  .$arr['hall_name']."</a></td> <td>$comment</td>\n";
                }
                $out .='</tr>';
            }
        
            mysql_free_result($vysledek); 
            
            //if ( $first == 1 ) {
		    //nejsou k dispozici zadne saly
		    //    $out .= "<div style=\"color:red;margin:auto;\">Není možno nic rezervovat.</div>";
	        //}
	
	        $out.= "</tbody></table>";

      }      
        return array($out, "", 0, "");
        
        

}

?>
