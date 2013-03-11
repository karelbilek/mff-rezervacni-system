<?php
                                     //par1     //par2 //par3==no                                         
function action_reserve($seat_array, $hall_ID, $user_to_change,  $user_id, $user_type,$id_spojeni, $dbPrefix, $vip) {
    global $OK, $DB_ERROR, $CUST_DOESNT_EXIST, $MAIL_ERROR, $MAIL_NO_ADDRESS;
    global $TICKET_COUNT_LIMIT;
    global $CANCEL_RESERVATION_AFTER;
    global $opening_dt;
    global $RESERV_OK;
    $choosen = array();
    

    $statusOr = "";
    if ($user_type=="A" || $user_type=="S") {
        $statusOr="OR status='L'";
    }
   
 
    if ($vip && $user_type!='A') {
        return array("", null, 1, 
          "pouze admini smi upravovat VIP");
    }
    if ($vip||$user_type=='P') {
        $limit = 1000000;
    } else {
        $limit = $TICKET_COUNT_LIMIT;
    }
    
    //sanity check
    if ( ! $user_id || $user_id == -1   )
        {
            return array("", null, 1, 
          "Invalid parameter valueA ".$user_id);
          
        }
        
        if ( ! $user_to_change || $user_to_change == -1   )
        {
            return array("", null, 1, 
          "Invalid parameter valueB ".$user_to_change);
          
        }
        
    if ( $hall_ID == "" || ! is_numeric($hall_ID))
        {
            return array("", null, 1, 
          "Invalid parameter valueC");
          
        }
   
   $res = "";
   
           $now = Time();
   $sql_query = "
      SELECT
        H.name name, HT.reserv_days, HT.name hall_type,
        unix_timestamp(HT.cancel_to_dt) AS cancel_to, 
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
  	$name = $arr['name'];
  	$hall_type = $arr['hall_type'];
  	$date = StrFTime ("%d.%m.%Y", $opening_dt);
  	$cancel_to = $arr['cancel_to'];
  	//$reserv_days = $arr['reserv_days'];
  	$ht_reserv_to_dt = $arr['ht_reserv_to_dt'];
  	$ht_reserv_from_dt = $arr['ht_reserv_from_dt'];
    $tc = reservationTimeCheck($user_type, $ht_reserv_from_dt, $ht_reserv_to_dt);
  	if ($tc!=$RESERV_OK) {
  	    return array("", "welcome", 1, 
            "Je zakázáno měnit rezervace.");
  	}
  	

  	
  	$res .= "<b>Změny rezervací v sále $name</b>\n";
  	$res .= "</div>\n";
  	
  	$pocet = 0;
  	$seat_prefix = 'seat_';
  	$pref_len = strlen($seat_prefix);
  	
  	foreach($seat_array as $key => $value)
  	{
  		if ($value == 1)
  		{	
  			$n = substr($key, $pref_len);
  			$choosen[$n] = 1;
  			$pocet++;
  		}
  	}
  	//zjistit sedadla uzivatelem rezervovana
  	
  	$reservstat = $vip?'V':'R';
  	
  	$sql_query =
  		"SELECT hall_ID, seat_number FROM ${dbPrefix}Seats 
  		WHERE customer_ID = $user_to_change AND status = '$reservstat'; ";
  	$vysledek = mysql_query ( $sql_query, $id_spojeni );
  	if ( $vysledek == false )
  	{
  	    return array("", null, 1, 
          "Error reading DB. $sql_query");
  	}
  
  	$other_count = 0;
  	$already_reserved = array();
  	while ( $arr = mysql_fetch_array ( $vysledek ))
  	{
  		if ($arr['hall_ID'] != $hall_ID)
  		{
  			$other_count++;
  		}
  		else
  		{
  			$n = $arr['seat_number'];
  			$already_reserved[$n] = 1;
  
  			// pokud se vstupenky jen pridavaji (nova rezervace a zadaji se existujici udaje)
  			// a je-li v db vstupenka teto mistnosti, ktera nebyla zakliknuta, musi se pocitat
  			// do poctu vstupenek teto mistnosti
  			//
  			//if ($choosen[$n] != 1) 
  			//{
  			//	$pocet++;
  			//}
  		}
  	}
  	
  	if ($pocet + $other_count > $limit)
  	{
  		return array("", null, 1,"Ve všech akcích můžete rezervovat celkem nejvýše $limit vstupenek");
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
    

  	
  	$res .= "<div style=\"background-color:white;text-align:center; padding:20px;\">";
  	$res .= "<table class=\"table\">\n";
  	$res .= ' <thead><tr><th>Vstupenka</th><th>Stůl</th><th>Stav</th></tr></thead>';
  	while ( $row = mysql_fetch_array ($vysledek) )
  	{
  		$number = $row['seat_number'];
        $nazev = $number." <i>(stůl ".$row['table_label'].")</i>";
  		if ( $seat_array ["seat_$number"] == 1 )
  		{
  			if ( array_key_exists($number, $already_reserved) && $already_reserved[$number] == 1 /*&& $remove_other == "yes" */)
  			{
  				// sedadlo je zaskrtle, patri vsak jiz uzivateli
  				//
  				$res .= "<tr style=\"color:black;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zůstává rezervovaná.</td></tr>\n";
  			}
  			else
  			{		
  			    
  			    $deadline = GetDeadline($CANCEL_RESERVATION_AFTER);
  			        
  			    if ($user_type=='P') {
  			        $deadline = GetDeadline(25);
  			    }    
  			    
  			    if ($vip) {
  			        //vip rezervace nepropadavaji, ale RADSI
  			        $deadline=GetDeadline(300);
  			    }
  			        
                $now_sql = date("YmdHis", $now);
  				$deadline_sql = date("YmdHis", $deadline);
                $cancel_to_sql = date("YmdHis", $cancel_to);
  				//uzivatel toto policko zaskrtl jako nove
  				$sql_query = "
            UPDATE ${dbPrefix}Seats SET status = '$reservstat',customer_ID = '$user_to_change', 
            reserv_dt='$now_sql', reserv_to_dt='$deadline_sql', cancel_to_dt= '$cancel_to_sql',
            reserved_by = '$user_id'
  					WHERE hall_ID = $hall_ID AND seat_number = $number AND (status = 'A' $statusOr)
  					LIMIT 1";
  
  				$sqlres = mysql_query ( $sql_query, $id_spojeni );
  				if ( $sqlres == false )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>rezervace se nezdařila $sql_query</td></tr>\n";
  				    
  					
  					log_write("DB_ERROR [6] - $sql_query");
  				}
  				else if ( mysql_affected_rows($id_spojeni) == 0 )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>rezervace se nezdařila (lístek již nebyl volný)</td></tr>\n";
  						
  				}
  				else
  				{
  					// status byl zmenen - pridat zaznam do tabuky Reserved
  					//
  					
					$res .= "<tr style=\"color:green;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>rezervace proběhla úspěšně</td></tr>\n";
						
  				}
  			}
  		} // fi ($_POST ["seat_$number"] == 1)
  
  		else //rusim podminku 
  		{
  			// toto policko neni zaskrtle - tuto rezervaci zrusit
  			//
  			if (  array_key_exists($number, $already_reserved) && $already_reserved[$number] == 1 )
  			{
  			$sql_query = "
            UPDATE ${dbPrefix}Seats SET status = 'A'
  					WHERE hall_ID = $hall_ID AND seat_number = $number  AND status = '$reservstat'
  					LIMIT 1";
  					
  				$sqlres = mysql_query ( $sql_query, $id_spojeni );
  				if ( $sqlres == false )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení rezervace se nezdařilo $sql_query</td></tr>\n";
  					
  					log_write("DB_ERROR [8] - $sql_query");
  				}
  				else if ( mysql_affected_rows($id_spojeni) == 0 )
  				{
  				    $res .= "<tr style=\"color:red;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení rezervace se nezdařilo (vstupenka již nebyla rezervována)</td></tr>\n";
  				    
  				}
  				else
  				{
  					
  					    $res .= "<tr style=\"color:green;\"><td>".$row['seat_number']."</td><td>".$row['table_label']."</td> <td>zrušení rezervace proběhlo v pořádku</td></tr>\n";
  				   
  				
  				
  				}//fi else $sqlres 
  			} //fi $already_reserved[$number] == 1	
  		
  		}	//fi $remove_other == "yes" 
  		/**/
  	}	//endwhile
  	$res.= "</table>\n";
  	
  	######MAIL
  	
  	$hall_type_c = str_replace(chr(0x96), '-', $hall_type);
	$subject = "Rezervace vstupenek - Matfyzacky ples 2013";

    $body = createConfirmation($id_spojeni, $dbPrefix, $user_to_change);
	//include "inc.confirm_body.php";  // vraci se $body, $total_count

//	if ($total_count > 0)
	if (true)
	{
		$result = sendConfirmation($id_spojeni, $user_to_change, $subject, $body, $dbPrefix);
		if ($result == $OK)
		{
			$log = "";
		} 
		else if ($result == $DB_ERROR)
		{
			$log = "MAIL DB_ERROR (customer_id = $customer_id)";
		} 
		else if ($result == $CUST_DOESNT_EXIST)
		{
			$log = "MAIL CUST_DOESNT_EXIST (customer_id = $customer_id)";
		} 
		else if ($result == $MAIL_ERROR)
		{
			$log = "MAIL_ERROR (customer_id = $customer_id)";
		} 
		else   // $result == $MAIL_NO_ADDRESS
		{
			$log = "";
		}

		if ($result == $MAIL_NO_ADDRESS)
		{
			// uzivatel nema adresu, nedelej nic
			//
			log_write("MAIL_NO_ADDRESS");
		}
		else if ($log == "")
		{		
			$res .= "<div class='alert alert-success'>
  Na Váš e-mail byl odeslán aktuální stav Vašich rezervací
</div>";
			log_write('MAIL OK');
		}
		else
		{	
			$res .= "Došlo k chybě $result při odesílání přehledu Vašich rezervací.";
			log_write($log." - ".$body);
		}

		// a jeste kopie pro administratora, svuj email ma v zakaznikovi id=1
		//   - uz bez kontroly uspesneho odeslani
		//
		$body = "(cid=$user_to_change)\n".$body;
		
		sendConfirmation($id_spojeni, 1, "[matfyzak rezervace] ".$subject, $body, $dbPrefix);
  	}
  	
  	$nextThing = $vip?'vip_choosen_table':'reservations';
  	$res .= "<input type=\"button\" value=\"Pokračovat\" onclick=\"onNavigate('form-menu', '$nextThing', $user_to_change);\" class='btn btn-success'/>";
  	$res .= "</div>\n";
  	$res .= "<div style=\"width:100%;text-align:center;\">";
  
  
  	$res .= "</div>  <!-- #data -->\n";
  	
  	
  	     return array($res, null, 0, "");

}

?>
