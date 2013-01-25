<?php

//action je admin_sale
function admin_action_sale($customer_id, $id_spojeni, $dbPrefix, $user_id, $userType) {
     
     if ($userType!='A' && $userType!='S') {
        return array("", null, 1, "Nedostatečná oprávnění $userType.");
    }
     
     $outres = "";
    if ($customer_id == "" || !IsNumeric($customer_id) ) {
        
        $error = 1;
        log_write("INTERNAL ERROR - Wrong parameter customer_id = $customer_id", "admin_sale" );
        return array("", null, 1, "Wrong parameter customer_id=".$customer_id);
        
    }
    
    $sql_query = "SELECT login FROM ${dbPrefix}User WHERE id = $customer_id; ";
	$result = mysql_query($sql_query, $id_spojeni);
	if ($result == FALSE) {
	    return array("", null, 1, "DB error.".$sql_query);
	}
	
	if (mysql_num_rows($result) == 0) {
	    return array("", null, 1, "No customer. ".$sql_query);
	}
	$arr = mysql_fetch_array($result);
    $login = $arr['login'];
	
	
	// nacti z databaze seznam vstupenek zakaznika
	
	
	$sql_query = 
		"SELECT 
			SEAT.seat_number, TAABLE.label AS table_label, SEAT.reserv_dt, SEAT.reserv_to_dt,
			HALL.name AS hall_name, HALLTYPE.price, HALL.id AS hall_ID, 
      unix_timestamp(SEAT.reserv_to_dt) AS reserv_to,
      rtrim(cast(SEAT.seat_number as char(10))) AS seat_label
	 FROM ${dbPrefix}Seats SEAT  
				LEFT JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				LEFT JOIN ${dbPrefix}Hall_type HALLTYPE ON HALLTYPE.id = HALL.hall_type_ID
				LEFT JOIN ${dbPrefix}Tables TAABLE ON TAABLE.table_number = SEAT.table_number AND SEAT.hall_ID=TAABLE.hall_ID
				WHERE SEAT.customer_ID = $customer_id AND status='R'
				ORDER BY HALLTYPE.`order`, HALL.`order`, SEAT.seat_number
		";
		
		
	$result = mysql_query ($sql_query, $id_spojeni);
    
    
    $outres .= "<div class=\"sold-result\">\n";
    
    if ($result == FALSE) {
	    return array("", null, 1, "DB error.".$sql_query);
	}
	
	if (mysql_num_rows($result) == 0)
	{
		$outres .= "V rezervaci nejsou žádné vstupenky, které by mohly být prodány";
		log_write("NOTHING_TO_SELL");
	}
	else
	{

		// ber jednu vstupenku po druhe a prodavej
		//

		while ($arr = mysql_fetch_array($result))
		{

			$seat_number = $arr["seat_number"];
			$hall_ID = $arr["hall_ID"];
			$reserv_dt = $arr['reserv_dt'];
			$reserv_to_dt = $arr['reserv_to_dt'];
		    $ident = $seat_number."_".$hall_ID;
		    
			// ----- cas prodeje
			//
			$now = Time ();
            $now_sql = date("YmdHis", $now);

			// ----- oznac vstupenku jako prodanou
			//
			$sql_query = "
				UPDATE ${dbPrefix}Seats SET status = 'S', sold_by_ID = $user_id, sold_dt = '$now_sql'
				WHERE seat_number = $seat_number AND hall_ID = $hall_ID AND status <> 'S'
				LIMIT 1";
			$result_ = mysql_query ( $sql_query, $id_spojeni );
			if ( $result_ == FALSE )
			{
				log_write("DB_ERROR (res_id=$ident) $sql_query");
				$sell_result[$ident] = "došlo k chybě zápisu do databáze $sql_query";

				continue;
			}
			if (mysql_affected_rows($id_spojeni) == 0)
			{
				// vstupenku se nepodarilo zmenit - mozna je uz prodana (??)
				//
				log_write("UPDATE_FAILED (res_id=$ident) $sql_query");
				$sell_result[$ident] = "vstupenku nelze prodat $sql_query";

				continue;
			}

			// ----- vlozeni backup zaznamu
			//
			$sql_query = "
				INSERT INTO ${dbPrefix}backup_Sold(seat_number, hall_ID, customer_ID, reserv_dt, reserv_to_dt, sold_dt, sold_by_ID) VALUES
				($seat_number, $hall_ID, $customer_id, '$reserv_dt', '$reserv_to_dt', $now_sql, $user_id)
				";
			$result_ = mysql_query ( $sql_query, $id_spojeni );
			if ( $result_ == FALSE )
			{
				log_write("UPDATE_FAILED (res_id=$ident) $sql_query");
				$sell_result[$ident] = "vstupenku nelze prodat $sql_query";
				continue;
			}
			if (mysql_affected_rows($id_spojeni) == 0)
			{
				// vstupenku se nepodarilo vlozit do prodanych
				//   - nehlasit, jen zapsat do logu (bude se stale zobrazovat v rezervaci, ale neni to fatalni)
				
				//TODO - mailem
				log_write("!!! CAN_NOT_MOVE_TO_SOLD - insert(res_id=$ident) $sql_query");
				$sell_result[$ident] = "vstupenka ve zvlastnim stavu $sql_query";
				continue;
			}


			// ----- vstupenka byla uspesne prodana
			//		
			$sell_result[$ident] = "OK";
			log_write("OK - sold (res_id=$ident)");
		}

		$outres.= "<table><tr><th>Místo</th><th>Stůl</th><th>Sál</th><th>Prodáno</th></tr>\n";

		// vypis vysledek prodeje - znovu projdi recordset 
		//
		mysql_data_seek($result, 0);
		while ($arr = mysql_fetch_array($result))
		{

			$seat_number = $arr["seat_number"];
			$hall_ID = $arr["hall_ID"];
			$ident = $seat_number."_".$hall_ID;
					
			
			$seat_label = $arr["seat_label"];
			$table_label = $arr["table_label"];
			$hall_name = $arr["hall_name"];
	
			$tr_tag = "<tr>";
			if ($sell_result[$ident] != "OK")
			{
				$tr_tag = "<tr class=\"error\">";
			}

			$outres.= $tr_tag."<td>$seat_label</td><td>$table_label</td><td>$hall_name</td><td>".$sell_result[$ident]."</td></tr>\n";
		}
		$outres.= "</table>\n";	
	}
	return array($outres, null, 0, "");
}

?>
