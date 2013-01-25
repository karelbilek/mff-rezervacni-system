<?php

function action_sold($user_id, $id_spojeni, $dbPrefix) {

    $sql_query = 
				"SELECT 
				  SEAT.seat_number,   
				  HALL.id AS hall_ID, HALL.name AS hall_name, HALLTYPE.price, TAABLE.label as table_label,
          unix_timestamp(SEAT.sold_dt) AS sold_dt, SELLER.login,
            SEAT.barcode, SEAT.barcode, SEAT.pseudorandom
				FROM ${dbPrefix}Seats SEAT  
				LEFT JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				LEFT JOIN ${dbPrefix}Hall_type HALLTYPE ON HALLTYPE.id = HALL.hall_type_ID
				LEFT JOIN ${dbPrefix}Tables TAABLE ON TAABLE.table_number = SEAT.table_number AND SEAT.hall_ID=TAABLE.hall_ID

				LEFT JOIN ${dbPrefix}User SELLER ON SEAT.sold_by_ID = SELLER.id
				WHERE SEAT.customer_ID = $user_id AND SEAT.status='S'
				ORDER BY HALLTYPE.`order`, HALL.`order`, SEAT.seat_number
				";
	$vysledek = mysql_query ($sql_query, $id_spojeni );
	if ( $vysledek == false )
	{
	    return array("", null, 1, "Chyba čtení databáze. ".$sql_query);
	}
	$res = "";
	
	
	
	$res .= "<h2>Koupené vstupenky</h2>";
	if (mysql_num_rows($vysledek)==0) {
	    $res .= "Nemáte koupené žádné vstupenky.";
	    return array($res, null, 0, "");
	} 
	
	
	$res .= '<table class="table table-striped">';
	$res .= '<thead><tr><th>Sál</th><th>Stůl</th><th>Cena</th><th>Prodejce</th><th>Download</th></tr></thead><tbody>';
	while($arr = mysql_fetch_array($vysledek)) {
	    $hallname = $arr['hall_name'];
	    $table = $arr['table_label'];
	    $seat_nu = $arr['seat_number'];
	    $when_sold = StrFTime ("%d.%m.%Y", $arr['sold_dt']);
	    $who_sold = $arr['login'];
	    $price = $arr['price'];
	    
	    if ($who_sold=="online") {
	        $download = '<a class="btn btn-info btn-mini" type="button" href="mbank/pdf_gen/generated/'.$arr['barcode'].$arr['pseudorandom'].'.pdf" download="ticket_'.$arr['barcode'].'">Stáhnout lístek</a>';
	    } else {
	        $download = '(možno jen u online)';
	    }
	    
	    $res .= "<tr><td>$hallname</td><td>$table</td><td>$price</td><td>$who_sold</td><td>$download</td></tr>";
	    
	}
	$res .= '</table>';
	return array($res, null, 0, "");
	

}

?>
