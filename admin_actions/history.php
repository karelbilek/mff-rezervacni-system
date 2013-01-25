<?php

function admin_action_history($id_spojeni, $dbPrefix, $user_type, $hall_type_id, $hall_id, $seller_id) {
    if ($user_type!='A' && $user_type!='S') {
        return array("", null, 1, "Nedostatečná oprávnění $userType.");
    }
    
    global $opening_dt;
        $res = "";
    
    if ($user_type == 'S' || $user_type == 'A') {
        $flt_halltype_id = -1;
        if ($hall_type_id != "" && is_numeric($hall_type_id)) {
            $flt_halltype_id = $hall_type_id; 
        }
        
        $flt_hall_id = -1;
        if ($hall_id != "" && is_numeric($hall_id))
        {
             $flt_hall_id = $hall_id; 
        }
        
        $flt_user_id = -1;
        if ( $seller_id != "" && is_numeric( $seller_id))
        {
             $flt_user_id =  $seller_id; 
        }
        $list_category="";
    
        $dotazdown=mysql_query("select id, name from ${dbPrefix}Hall_type order by `order`", $id_spojeni); 
        if ( $dotazdown == FALSE ) {
            return array("", null, 1, "SQL error: select id, name from ${dbPrefix}Hall_type order by `order`");
        }
        
        
        $pocetdown=mysql_num_rows($dotazdown);
    
        if ($pocetdown==0) {
            $list_category.="<option value=\"0\">Není žádná kategorie...</option>\n";
        } else {
            $list_category.="<option value=\"-1\">Všechny kategorie</option>";
        }
    
        for ($pom=0; $pom < $pocetdown; $pom++)
        {
            $pole_data=mysql_fetch_assoc($dotazdown);
            $list_category .= "<option "; 

            if ($flt_halltype_id == $pole_data["id"]) { 
                $list_category .= "SELECTED";     
            }
            
            $list_category .= " value=\"".$pole_data["id"]."\">".$pole_data["name"]."</option>\n";
         }//endfor
         
         
        $list_halls = "";
        $sql_query = " 
            SELECT H.id, H.name, H.hall_type_ID type
  		        FROM ${dbPrefix}Hall H";
  		
  		$dotazdown=mysql_query($sql_query, $id_spojeni);
  		if ( $dotazdown == FALSE ) {
            return array("", null, 1, "SQL error: $sql_query");
        }
  
        $pocetdown=mysql_num_rows($dotazdown);
        
        // $pocethalls bude využito v javascriptu
        $pocethalls = $pocetdown; 
      
        if ($pocetdown == 0)
        {
          $list_halls .= "<option value=\"0\">Žádná událost</option>\n";
        }
        else
        {
          $list_halls .= "<option value=\"-1\">Všechny události</option>";
        }
        
        while( $pole_data=mysql_fetch_assoc($dotazdown) )
        { 
          $pole_data["time"]=$opening_dt;
          $hall_time = strftime("%d.%m.%Y", $pole_data["time"]);
          $list_halls .= "<option "; 
          
          if ($flt_hall_id == $pole_data["id"])
          { 
            $list_halls .= "SELECTED";     
          }
          
          if ($flt_halltype_id != -1 && $flt_halltype_id != $pole_data["type"])
          {
            $list_halls .= " style=\"display:none\"";
          }
          
          $list_halls .= 
            " id=\"hall_".$pole_data["id"]."\""
            ." class=\"halltype_".$pole_data["type"]."\""
            ." value=\"".$pole_data["id"]."\"";
      
          $list_halls.=">".$pole_data["name"]." (".$hall_time.") </option>\n";
        }//endwhile
        
        $list_users = "";
        $sql_query = " 
          SELECT id, login
      		FROM ${dbPrefix}User U
          WHERE U.status = 'S' OR  U.status = 'A' 
          ORDER BY lastname";
        $dotazdown=mysql_query($sql_query,$id_spojeni);
        if ( $dotazdown == FALSE ) {
            return array("", null, 1, "SQL error: $sql_query");
        }
        
        $pocetdown=mysql_num_rows($dotazdown);
        
        if ($pocetdown == 0)
        {
          $list_users.="<option value=\"0\">Žádný prodejce</option>\n";
        }
        else
        {
          $list_users.="<option value=\"-1\">Všichni prodejci</option>";
        }
        
        for ($pom=0; $pom < $pocetdown; $pom++)
        {
          $pole_data = mysql_fetch_assoc($dotazdown);
          $list_users .= "<option ";
          
          if ($flt_user_id == $pole_data["id"])
          { 
            $list_users .= "SELECTED";     
          }
          
           $list_users.=" value=\"".$pole_data["id"]."\"";
      
          $list_users.=">".$pole_data["login"]."</option>\n";
        }
        
        $res .= <<<EOT
<h3 class="center">Historie prodeje</h3>
    
    <table style="border-width:1px;border-style:solid;margin:auto;padding:10px;padding-bottom:2px;width:320px;"> 
    <tr style='font: normal normal bold large bold;'>
      
    <td>Kategorie</td><td>Událost</td><td>Prodejce</td>
    </tr>
    <tr style='font: normal normal bold large bold;'>
    <td>	
    
      <select id="history_category" name="history_category" onchange="select_halls(this.value, $pocethalls )">
      $list_category
      </select>
      
      </td>
      
      
    <td>	
    
      <select id="history_halls" name="history_halls">
         $list_halls
      </select>
       
    </td>
    
     <td>
    	
      <select id="history_users" name="history_users">
        $list_users
      </select>
      
    </td>
    </tr>
    </table>    
    
    <h3 class="center"><input type="button" value="Zobrazit" 
      onclick="onNavigate('form-menu', 'history', select_value('history_category'), select_value('history_halls'), select_value('history_users'));"></h3>
EOT;

        $halltype_filter = "";
        if ($flt_halltype_id != -1)
        {
          $halltype_filter = " AND HALL.hall_type_ID = $flt_halltype_id";
        }
     
        $hall_filter = "";
        if ($flt_hall_id != -1)
        {
          $hall_filter = " AND HALL.id = $flt_hall_id";
        }
     
        $user_filter = "";
        if ($flt_user_id != -1)
        {
          $user_filter = " AND SELLER.id = $flt_user_id";
        }   
        
	    $total_price = 0;
	    $total_count = 0;

	    $sql_query = "
			SELECT 
      unix_timestamp(max(SEAT.sold_dt)) sold_dt, count(*) Pocet, 
      SELLER.login, SEAT.table_number, HALL.name hall_name, HALLTYPE.price, 
      CONCAT(CUSTOMER.firstname, ' ', CUSTOMER.lastname) customer_name, CUSTOMER.address
			FROM ${dbPrefix}User SELLER 
				  INNER JOIN ${dbPrefix}Seats SEAT ON SELLER.id = SEAT.sold_by_ID
				  INNER JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				  INNER JOIN ${dbPrefix}Hall_type HALLTYPE ON HALL.hall_type_ID = HALLTYPE.id
				  INNER JOIN ${dbPrefix}User CUSTOMER ON SEAT.customer_ID = CUSTOMER.id
				WHERE SEAT.status='S' $halltype_filter $hall_filter $user_filter
				GROUP BY SELLER.login, SEAT.table_number, HALL.name, CONCAT(CUSTOMER.firstname, ' ', CUSTOMER.lastname), CUSTOMER.address, HALLTYPE.price
				ORDER BY SEAT.sold_dt, CUSTOMER.address, HALL.`order`, SEAT.table_number;
			";
	    $result = mysql_query ($sql_query, $id_spojeni);
	    
		if ($result == FALSE)
		{
		    return array("", null, 1, "SQL error: $sql_query");
			
		}
		
		$res .= "<table class=\"table table-striped table-condensed\">\n";
  		$res .= "<thead><tr><th>Prodejce</th><th>Datum</th><th>Ks</th><th>Stůl</th><th>Sál</th><th>Cena</th><th>Jméno</th><th>Email</th></tr></thead><tbody>\n";
  		while ( $arr = mysql_fetch_array( $result ))
  		{
  			$res .=  "<tr>";
  			$time = StrFTime ("%d.%m.%Y&nbsp;&nbsp;&nbsp;&nbsp;%H:%M", $arr["sold_dt"]);
  			$res .=  "<td>".$arr["login"]."</td>";
  			$res .=  "<td>${time}</td>";
  			$res .=  "<td>".$arr["Pocet"]."</td>";
  			$res .=  "<td>".$arr["table_number"]."</td>";
  			$res .=  "<td>".$arr["hall_name"]."</td>";
  			$res .=  "<td>".$arr["price"] * $arr["Pocet"]."</td>";
  			$res .=  "<td>".$arr["customer_name"]."</td>";
  			$res .=  "<td>".$arr["address"]."</td>";
  			$res .=  "</tr>\n";
  
  			$total_count += $arr["Pocet"];
  			$total_price += $arr["Pocet"] * $arr["price"];
  		}
  		
  		
  		$res .=  "</tbody></table>\n";	
  	
  		$res .=  "<br/><br/>";
  	
  		$res .=  "<table class=\"table table-bordered\">\n";
  		$res .=  "<tr><th colspan=\"2\">Celkem</th></tr>\n";
  		$res .=  "<tr><th>Počet</th><th>Cena</th></tr>";
  		$res .=  "<tr><td>$total_count</td><td>$total_price</td><tr/>\n";
  		$res .=  "</table>\n";	
        
         
    }//fi $user_type == 'S'
    
    

     return array($res, null, 0, "");
}

?>
