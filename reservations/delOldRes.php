<?php
//SMAZANI STARYCH REZERVACI 



	function delOldRes ($id_spojeni, $dbPrefix)
	{
        global $propadejDoKnihoven;

		
		
		$query = "SELECT * FROM ".$dbPrefix."Seats WHERE reserv_to_dt < FROM_UNIXTIME(".time() .") AND STATUS='R'";
		
		$vysledek = mysql_query ($query, $id_spojeni);
		if ($vysledek == FALSE)
			die ("Error reading database. $query\n");
		
		
		while ($row = mysql_fetch_array ($vysledek))
		{
			//klice zaznamu
			$id = $row ["hall_ID"];
			$seat = $row ["seat_number"];
			$customer_ID = $row["customer_ID"];
			$reserve_dt = $row["reserv_dt"];
			$reserve_to_dt = $row["reserv_to_dt"];
			
			if ($propadejDoKnihoven == 1) {
			    $status = "L";
			} else {
			    $status = "A";
			}
			
			//zmenit status v tabulce Ticket na free	
			$m_query = "UPDATE ${dbPrefix}Seats SET status='${status}' WHERE seat_number=$seat AND hall_ID=$id AND status='R';";
			$v = mysql_query ( $m_query, $id_spojeni );
			if ( $v == FALSE )
				die ("Error writing database. $query");
				
				
			$m_query = "INSERT INTO ${dbPrefix}backup_Expired (
						
						`seat_number` ,
						`hall_ID` ,
						`customer_ID` ,
						`reserv_dt` ,
						`reserv_to_dt` ,
						`expired_dt`
					)
					VALUES (
						 $seat, $id, $customer_ID, '$reserve_dt', '$reserve_to_dt', FROM_UNIXTIME(".time() .")
					);";

			
			$v = mysql_query ( $m_query, $id_spojeni );
			if ( $v == FALSE )
				die ("Error writing database. $m_query");
			
			
		
		}/**/
		
	}	

?>

