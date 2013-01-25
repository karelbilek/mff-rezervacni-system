<?php

function admin_action_customers($id_spojeni, $dbPrefix, $userType) {

    
    if ($userType!='A' && $userType!='S') {
        return array("", null, 1, "Nedostatečná oprávnění $userType.");
    }

  $res = "";
  $onclick = "fc=document.forms['form-menu']; fc.act.value='reservations'; fc.par1.value=-1; fc.submit();"; 
  //$res .= "<h3 class=\"center\">Volný prodej (bez rezervace)</h3>\n";
  $res .= "<input type=\"button\" class = 'btn btn-large btn-primary' onclick=\"$onclick\" value=\"Volný prodej (bez rezervace)\">\n";


  $res .= "<h3 class=\"center\">Platné rezervace</h3>\n";

  $sql_query="
    SELECT U.firstname, U.lastname, U.address,  U.status, U.login, U.id ".
    "FROM ${dbPrefix}User U WHERE EXISTS ( SELECT * FROM ${dbPrefix}Seats S WHERE S.customer_ID=U.id AND S.status='R' ) AND U.status <> 'V' ".
    /*FROM User U 
      INNER JOIN Reservation R ON U.id = R.customer_ID 
      INNER JOIN Ticket T ON R.event_ID = T.event_ID AND R.seat_number = T.seat_number
      INNER JOIN User SU ON T.salesplace_ID = SU.salesplace_ID 
    WHERE 
      U.user_type IN ('C', 'S') AND U.status IN ('A', 'B') 
      AND SU.id = $user_id AND SU.user_type = 'S' 
    GROUP BY 1, 2, 3, 4, 5, 6, 7*/
    " ORDER BY U.lastname; ";
    		
		
	$vysledek = mysql_query ( $sql_query, $id_spojeni );
	if ( $vysledek == FALSE ) 
	{
	    return array("", null, 1, "Chyba čtení databáze. ".$sql_query);
	    
	}
	
	$res .= "<div id=\"customer-list\">\n";
	
	// vypsat uzivatele na vystup
	//
	$first = 1;
   
  // echo "<table style=\"border-width:1px;border-style:solid;margin:auto;padding:10px;padding-bottom:2px;width:320px;\">";
  $res .=  "<table class=\"table table-striped\">\n"; 
  $res .=  "<tr>";
  $res .=  "<th>Login</th>";        
  $res .=  "<th>Křestní</th>";        
  $res .=  "<th>Příjmení</th>";
  $res .=  "<th>e-mail</th>";
  // echo "<th>Prùkaz</th>";
  // echo "<th>Aktivní?</th>";
  $res .=  "<th>Akce</th>";
  // echo "<th>Smazat</th>";
  $res .=  "</tr>";
    
  while ( $radek=mysql_fetch_array($vysledek))
  {
    
		if ( $first == 1 )
		{
			$first=0;
    }

    $res .=  "<tr>";
 		$res .=  "<td>".$radek['login']."</td>";
    $res .=  "<td>".$radek['firstname']."</td>";        
		$res .=  "<td>".$radek['lastname']."</td>";        
		$res .=  "<td>".$radek['address']."</td>";
    // echo "<td>".$radek[3]."</td>";

    /*
    echo "<td>";
    if ($radek[4]=='A') 
    {
      echo "aktivni";
    } 
    elseif ($radek[4]=='B')
    {
      echo "zablokován"; 
    } 
    echo "</td>";
    */
    
    $res .=  "<td>";
    $onclick = "fc=document.forms['form-menu']; fc.act.value='reservations'; fc.par1.value=".$radek['id']."; fc.submit();";
    $res .=  "<input type=\"button\" onclick=\"$onclick\" value=\"Prodej\" class='btn btn-info btn-small'/>";   
    $res .=  "</td>";

    // echo "<td>";
    // echo "<a href=''>smazat</a>";
    // echo "</td>";
    
    $res .=  "</tr>";
  }

	$res .=  "</table>";
	 
	if ( $first == 1 )
	{
		//nejsou k dispozici zadne akce daneho druhu
		//
		$res .=  "V databázi nejsou žádní uživatelé\n";
	}
	
	$res .=  "</div>\n";
	
	  	     return array($res, null, 0, "");
	
}

?>
