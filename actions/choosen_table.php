<?php

//HELL ON EARTH
//ABANDON ALL SANITY YOU WHO ENTER THIS PLACE


function action_choosen_table($mode, $customer_id,$user_id, $id_spojeni, $dbPrefix, $session_id, $user_type, $vip){


            $searchstatus = $vip ? 'V':'R';

            if ($vip && ($user_type != 'A')) {
                return array("", null, 1, "Jen admin smí spravovat VIP.");
            }
    

            global $MODE_CHANGE, $MODE_VIEW, $MODE_SELL;
            global $opening_dt;
			$res_filter = "";
			$place_filter = "";

			
			$sql_query = 
				"SELECT 
				  SEAT.seat_number,   
				  HALL.id AS hall_ID, HALL.name AS hall_name, HALLTYPE.price, TAABLE.label as table_label,
          unix_timestamp(SEAT.reserv_to_dt) AS reserv_to
				FROM ${dbPrefix}Seats SEAT  
				LEFT JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				LEFT JOIN ${dbPrefix}Hall_type HALLTYPE ON HALLTYPE.id = HALL.hall_type_ID
				LEFT JOIN ${dbPrefix}Tables TAABLE ON TAABLE.table_number = SEAT.table_number AND SEAT.hall_ID=TAABLE.hall_ID
				WHERE SEAT.customer_ID = $customer_id AND (status='$searchstatus')
				ORDER BY HALLTYPE.`order`, HALL.`order`, SEAT.seat_number
				";
				
            $vysledek = mysql_query ($sql_query, $id_spojeni );
			if ( $vysledek == false )
			{
			    return array("", null, 1, "! Chyba čtení databáze. ".$sql_query);
			}
			$res = "";
			
			
			$tickets_count = 0;
			$tickets_price = 0;			
		
			// v modu $MODE_SELL se navic 
			//   - ve formulari predava cislo zakaznika
			//   - zobrazi tlacitko "Prodavam" - je-li tedy co prodavat
			//
			if ($mode == $MODE_SELL && !$vip) 
			{
				$res.= "<input type=\"hidden\" name=\"par2\" value=\"$customer_id\" />\n";

				$res.= "<div class=\"sell-buttons\">\n";
				$res.= "<input class=\"btn btn-danger btn-large\" type=\"button\" value=\"Zpět\" onclick=\"fc=document.forms['form-menu'];fc.act.value='admin_customers';fc.par1.value=$customer_id;fc.submit();\" />";

				if (mysql_num_rows($vysledek) > 0)
				{
					$res.= "    <input class=\"btn btn-large btn-info\" type=\"button\" value=\"Prodat\" onclick=\"fc=document.forms['form-menu'];fc.act.value='admin_sale';fc.par1.value=$customer_id;fc.submit();\" />";
				}

				$res.= "</div>";
			}
			$price = 0;
			$res .= "<div class=\"res-tickets\">\n";
			if ($customer_id == $user_id) {
			    $res .= "<div class=\"title\"><b>Vaše vstupenky: </b>";
			} else {
			    $userquery = "SELECT U.login, U.firstname, U.lastname FROM ${dbPrefix}User U WHERE U.id='$customer_id'";
			    $uservysledek = mysql_query ($userquery, $id_spojeni );
			    if ( $uservysledek == false )
			    {
			        return array("", null, 1, "! Chyba čtení databáze. ".$userquery);
			    }
			    $userarr = mysql_fetch_array($uservysledek);
			    if (beginsWith($userarr['login'], 'vip')){
			        $usrname = $userarr['firstname'];
			    } elseif (beginsWith($userarr['login'], 'volny')) {
			        $usrname = "volny prodej";
			    } else {
			        $usrname = $userarr['firstname']." ".$userarr['lastname'];
			    }
			    $res .= "<div class=\"title\">Vstupenky uživatele <b>". $usrname."</b>: ";
			}
			if ($mode != $MODE_VIEW) $res .= "(chcete-li zobrazit či změnit rezervaci, klikněte na řádek)";
			$res .= "</div>";
			$res .= "<table class=\"table table-striped table-hover\">\n";
			$res .= "<tr>"
				."<th>Židle</th>"
				."<th>Stůl</th>"
				."<th>Sál</th>"
				."<th>Cena</th>"
				//."<th>Prodejní místo</th>"
				."<th>Platnost rezervace</th>"
				."</tr>\n";
			
			while ($arr = mysql_fetch_array ($vysledek ))
			{
				$number = $arr ['seat_number'];		//seat number
				$price  = $arr ['price'];		//ticket price
				$hall_name  = $arr ['hall_name'];		//place

				$seat_label = $number;
				
				$table_label = $arr ['table_label'];    
				$hall_id = $arr['hall_ID'];

				$reserv_to_txt = StrFTime ("%d.%m.%Y, %H:%M", $arr['reserv_to'])." (".timerUntil( $arr['reserv_to']).")";

				$actionOnClick = $vip?'display_vip_reservation':'display_reservation';
				// zobraz vstupenku	
				//
				$onClick = 
					 "fc=forms['form-menu']; "
                    ."fc.act.value = '$actionOnClick'; "
                    ."fc.par1.value = $hall_id; "
                    .($mode == $MODE_SELL ? "fc.par2.value = $customer_id; " : "")
					."fc.submit(); "
					."return false; ";

				// je-li prihlasen zakaznik pro zmeny rezervace nebo prodejce, je tu moznost editace vstupenek v sale
				//
				if ($mode == $MODE_CHANGE || $mode == $MODE_SELL)
				{
					$event_td = "<td><a href=\"/\" onclick=\"$onClick\" alt=\"Změnit\">$hall_name</a></td>";
					$event_tr = "<tr onclick=\"$onClick\" style=\"cursor: pointer;cursor: hand\">";
				}
				else
				{
					$event_td = "<td>$hall_name</td>";
					$event_tr = "<tr>";
				}

				$res .= $event_tr 
					."<td>$seat_label</td>"
					."<td>$table_label</td>"
					.$event_td
					."<td>$price</td>"
					//."<td>$place</td>"
					."<td>$reserv_to_txt</td>"
					."</tr>\n";
		
				$tickets_count++;
				$tickets_price += $price;
			}
			$res .= "</table>\n";
			
			if ($tickets_count!=0) {

               /* $res .= "<div class='well'><table><tr><td style='border-right:1px solid black; '><table style='font-weight:bold; width:190px'>\n";
                $res .= "<tr><td>Počet vstupenek:</td><td>$tickets_count</td></tr>\n";
                $res .= "<tr><td>Celková cena:</td><td style='padding-right:1em;margin-right:1em'>$tickets_price Kč</td></tr>\n";
                $res .= "</table></td><td style='padding-left:1em'><div>Lístky si kupte buď ve vybrané knihovně (Karlov, Karlín, ???), nebo zaplaťte převodem. <br><br>Pro platbu převodem pošlete částku <b>$tickets_price Kč</b> na účet <b>670100-2205778042/6210</b> s variabilním symbolem <b>330".$customer_id."</b>. <br><br>Do knihovny je nutné donést <b>celou částku $tickets_price Kč</b>.</div></td></tr></table></div>\n";*/
                $res .= text_uplaceni($tickets_count, $tickets_price, $customer_id);
			}
			
			$onClickNew = 
						 "fc=document.forms['form-menu']; "
						."fc.act.value = 'resend_confirmation'; "
						.($mode == $MODE_SELL ? "fc.par2.value = $customer_id; " : "")
						."fc.submit(); "
						."return false; ";
						
            
            /*if ($customer_id == $user_id) {
                $res .= "<br><br><p>
  <button class='btn btn-large btn-primary' type='button' onClick=\"$onClickNew\" >Znovu poslat e-mail o rezervaci</button>
</p>";
            }*/

			// v modu $MODE_CHANGE se navic zobrazi seznam salu, ve kterych uzivatel nema rezervace
			//
			if ($mode == $MODE_CHANGE || $mode == $MODE_SELL)
			{	
				$place_filter = "";
				/*if ($mode == $MODE_SELL)
				{
					// prodejce vidí jen rezervace svého prodejního místa
					//
					$place_filter = "
						  INNER JOIN Ticket T ON R.event_ID = T.event_ID AND R.seat_number = T.seat_number
						  INNER JOIN Session SES ON SES.id = $session_id 
						  INNER JOIN User U ON SES.user_ID = U.id AND T.salesplace_ID = U.salesplace_ID
						";
				}
				relikt
				*/
				//aktualni casove razitko
      	        //
      	        $now = Time();
      	        $now_sql = date("Y-m-d H:i:s", $now);
      	
      	        // najit vsechny udalosti, ktere maji casove razitko
      	        // vetsi nez aktualni (setridit podle razitka)
              	// a uz je mozne je rezervovat
              	//
              	$where = "TRUE";
              	if ($user_type != "S" && $user_type != "A" &&$user_type != "P") 
              	{
              	   $where .= " AND HT.reserv_from_dt <= '$now_sql' ";
              	   $where .= " AND HT.reserv_to_dt > '$now_sql' ";
                }
              	$orderby = "HT.order";
          
				$sql_query = 
          				
          				"SELECT 
                    HALL.id AS hall_ID, HALL.name hall_name, HT.name hall_type_name,
                    (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = HALL.id AND T.status = 'A') AS available,
                     (SELECT count(*) FROM ".$dbPrefix."Seats T WHERE T.hall_ID = HALL.id AND T.status = 'L') AS avlib
          				FROM ${dbPrefix}Hall HALL
          				  INNER JOIN ${dbPrefix}Hall_type HT ON HALL.hall_type_ID = HT.id 
          				WHERE
                    $where
                     ORDER BY HT.`order`, HALL.`order`"
          				
          				;
          				//die($sql_query);
          		 
				$vysledek = mysql_query ($sql_query, $id_spojeni );
				if ( $vysledek == false )
				{
				    return array("", null, 1, "? Chyba čtení databáze. ".$sql_query);
					
				}
	            if (mysql_num_rows($vysledek) > 0)
				{
				    //$res .= "<div class=\"other-events\" style=\"font-size:100%\">";
					
					 $res .= "<b>Pro rezervaci v jiných sálech vyberte níže:</b>\n";
					 $res .= "<table class='table'>";
					while($arr = mysql_fetch_array($vysledek))
					{
					    $available = $arr['available'];
					    $avlib = $arr['avlib'];
						$hall_id = $arr['hall_ID'];
						$hall_name = $arr['hall_name'];
						$hall_type_name = $arr['hall_type_name'];
						$event_date = $opening_dt;
						$event_date_str = StrFTime ("%d.%m.%Y", $event_date);
				        $actionOnClick = $vip?'display_vip_reservation':'display_reservation';
				
						$onClick = 
							 "fc=document.forms['form-menu']; "
							."fc.act.value = '$actionOnClick'; "
							."fc.par1.value = $hall_id; "
							.($mode == $MODE_SELL ? "fc.par2.value = $customer_id; " : "")
							."fc.submit(); "
							."return false; ";
				
						$res .= "<tr><td>$hall_type_name</td> <td><a href=\"/\" onClick=\"$onClick\" alt=\"Změnit\">$hall_name</a> (volných: ".$available." + $avlib u prodejců)</td><tr>";
					}
					$res .= "</table> <!-- .other-events -->\n";

				}

			}
			$res .= "</div>  <!-- .res-tickets -->\n";
				
			
			
			return array($res, null, 0, "");


}
?>
